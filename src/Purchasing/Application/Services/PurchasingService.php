<?php

namespace Purchasing\Application\Services;

use Purchasing\Infrastructure\Persistence\Supplier;
use Purchasing\Infrastructure\Persistence\PurchaseOrder;
use Purchasing\Infrastructure\Persistence\PurchaseOrderLine;
use Purchasing\Infrastructure\Persistence\PurchaseRequisition;
use Purchasing\Infrastructure\Persistence\GoodsReceipt;
use Purchasing\Infrastructure\Persistence\GoodsReceiptLine;
use Purchasing\Infrastructure\Persistence\SupplierInvoice;
use Inventory\Application\Services\StockMovementService;
use Accounting\Application\Services\JournalEntryService;
use Shared\Support\AuditLogger;
use Shared\Support\Outbox;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PurchasingService
 *
 * Implements the procurement workflow:
 *   Purchase Requisition → Approval → RFQ → Quotation → Purchase Order
 *   → Goods Receipt → Quality Inspection → Supplier Invoice → Payment
 *
 * Business rules:
 *   BR-005 Production cannot consume more material than is available or authorised.
 *   BR-010 Financial and inventory transactions must be atomic.
 */
class PurchasingService
{
    protected StockMovementService $stockService;
    protected JournalEntryService $journalService;

    public function __construct(StockMovementService $stockService, JournalEntryService $journalService)
    {
        $this->stockService = $stockService;
        $this->journalService = $journalService;
    }

    /**
     * Create a purchase requisition.
     */
    public function createRequisition(array $dto): PurchaseRequisition
    {
        return PurchaseRequisition::create([
            'tenant_id' => $dto['tenant_id'],
            'factory_id' => $dto['factory_id'] ?? null,
            'requisition_number' => $dto['requisition_number'],
            'requested_by' => $dto['requested_by'],
            'required_date' => $dto['required_date'],
            'status' => 'draft',
            'notes' => $dto['notes'] ?? null,
        ]);
    }

    /**
     * Approve a purchase requisition.
     */
    public function approveRequisition(PurchaseRequisition $requisition, int $approverId): PurchaseRequisition
    {
        if ($requisition->status !== 'draft') {
            throw new \LogicException('Only draft requisitions can be approved.');
        }

        $requisition->status = 'approved';
        $requisition->save();

        Log::info('Purchase requisition approved', [
            'requisition_id' => $requisition->id,
            'requisition_number' => $requisition->requisition_number,
            'approved_by' => $approverId,
        ]);

        return $requisition;
    }

    /**
     * Create a purchase order from an approved requisition.
     */
    public function createPurchaseOrder(array $dto): PurchaseOrder
    {
        return PurchaseOrder::create([
            'tenant_id' => $dto['tenant_id'],
            'factory_id' => $dto['factory_id'] ?? null,
            'supplier_id' => $dto['supplier_id'],
            'po_number' => $dto['po_number'],
            'order_date' => $dto['order_date'],
            'expected_delivery_date' => $dto['expected_delivery_date'] ?? null,
            'status' => PurchaseOrder::STATUS_APPROVED,
            'notes' => $dto['notes'] ?? null,
        ]);
    }

    /**
     * Add lines to a purchase order.
     */
    public function addOrderLines(string $orderId, array $lines): array
    {
        $order = PurchaseOrder::findOrFail($orderId);
        $created = [];

        foreach ($lines as $line) {
            $total = (float) $line['quantity'] * (float) ($line['unit_price'] ?? 0);
            $created[] = PurchaseOrderLine::create([
                'purchase_order_id' => $orderId,
                'tenant_id' => $order->tenant_id,
                'item_id' => $line['item_id'],
                'quantity' => (float) $line['quantity'],
                'unit_id' => $line['unit_id'] ?? null,
                'unit_price' => (float) ($line['unit_price'] ?? 0),
                'total_price' => $total,
                'received_quantity' => 0,
            ]);
        }

        return $created;
    }

    /**
     * Receive goods against a purchase order (partial receiving supported).
     * Creates stock movement (goods_receipt) and accounting posting.
     */
    public function receiveGoods(array $dto): GoodsReceipt
    {
        return DB::transaction(function () use ($dto) {
            $order = PurchaseOrder::with('lines')->findOrFail($dto['purchase_order_id']);

            $receipt = GoodsReceipt::create([
                'tenant_id' => $dto['tenant_id'],
                'factory_id' => $dto['factory_id'] ?? $order->factory_id,
                'warehouse_id' => $dto['warehouse_id'],
                'purchase_order_id' => $order->id,
                'grn_number' => $dto['grn_number'],
                'receipt_date' => $dto['receipt_date'],
                'status' => 'pending',
                'received_by' => $dto['received_by'],
            ]);

            $movementLines = [];

            foreach ($dto['lines'] as $line) {
                $poLine = PurchaseOrderLine::find($line['purchase_order_line_id']);
                $receivedQty = (float) $line['quantity'];

                GoodsReceiptLine::create([
                    'goods_receipt_id' => $receipt->id,
                    'tenant_id' => $dto['tenant_id'],
                    'purchase_order_line_id' => $poLine->id,
                    'item_id' => $poLine->item_id,
                    'quantity' => $receivedQty,
                    'unit_id' => $poLine->unit_id,
                    'batch_id' => $line['batch_id'] ?? null,
                ]);

                // Update received quantity on PO line
                $poLine->received_quantity = bcadd((string) $poLine->received_quantity, (string) $receivedQty, 4);
                $poLine->save();

                // Stock movement line
                $movementLines[] = [
                    'item_id' => $poLine->item_id,
                    'quantity' => $receivedQty,
                    'unit_id' => $poLine->unit_id,
                    'warehouse_id' => $dto['warehouse_id'],
                    'batch_id' => $line['batch_id'] ?? null,
                    'unit_cost' => (float) $poLine->unit_price,
                ];

                // Check if PO is fully received
                $this->checkPOCompletion($order);
            }

            // Post stock movement (atomic)
            $this->stockService->postMovement([
                'tenant_id' => $dto['tenant_id'],
                'factory_id' => $dto['factory_id'] ?? $order->factory_id,
                'warehouse_id' => $dto['warehouse_id'],
                'movement_type' => 'goods_receipt',
                'reference' => 'GRN-' . $receipt->grn_number,
                'source_document_type' => 'goods_receipt',
                'source_document_id' => $receipt->id,
                'transaction_date' => $dto['receipt_date'],
                'posting_date' => $dto['receipt_date'],
                'user_id' => $dto['received_by'],
                'reason' => 'Goods received against PO ' . $order->po_number,
                'lines' => $movementLines,
            ]);

            $receipt->status = 'completed';
            $receipt->save();

            Log::info('Goods received', [
                'receipt_id' => $receipt->id,
                'grn_number' => $receipt->grn_number,
                'po_number' => $order->po_number,
                'received_by' => $dto['received_by'],
            ]);

            // Transactional outbox + audit for the committed receipt.
            $tenantId = (int) $dto['tenant_id'];
            $userId = (int) ($dto['received_by'] ?? 0);
            Outbox::record(
                'GoodsReceiptPosted',
                'goods_receipt',
                (int) $receipt->id,
                [
                    'tenant_id' => $tenantId,
                    'grn_number' => $receipt->grn_number,
                    'purchase_order_id' => $order->id,
                ],
                $tenantId
            );
            AuditLogger::record(
                'goods_receipt_posted',
                'goods_receipt',
                (int) $receipt->id,
                [],
                ['grn_number' => $receipt->grn_number, 'status' => 'completed'],
                $tenantId,
                $userId
            );

            return $receipt->load(['lines.item', 'purchaseOrder.supplier']);
        }, 3);
    }

    /**
     * Create a supplier invoice and post accounting (Dr Inventory, Cr AP).
     */
    public function createSupplierInvoice(array $dto): SupplierInvoice
    {
        return DB::transaction(function () use ($dto) {
            $invoice = SupplierInvoice::create([
                'tenant_id' => $dto['tenant_id'],
                'supplier_id' => $dto['supplier_id'],
                'invoice_number' => $dto['invoice_number'],
                'invoice_date' => $dto['invoice_date'],
                'due_date' => $dto['due_date'] ?? null,
                'total_amount' => (float) $dto['total_amount'],
                'tax_amount' => (float) ($dto['tax_amount'] ?? 0),
                'status' => 'draft',
            ]);

            // Post accounting journal: Dr Raw Material Inventory, Cr Supplier Payable
            $this->journalService->postJournal([
                'tenant_id' => $dto['tenant_id'],
                'accounting_book_id' => $dto['accounting_book_id'] ?? 1,
                'fiscal_period_id' => $dto['fiscal_period_id'] ?? null,
                'document_date' => $dto['invoice_date'],
                'posting_date' => $dto['invoice_date'],
                'currency' => $dto['currency'] ?? 'PKR',
                'reference' => 'INV-' . $dto['invoice_number'],
                'description' => 'Supplier invoice from supplier ' . $dto['supplier_id'],
                'created_by' => $dto['created_by'],
                'approved_by' => $dto['approved_by'] ?? null,
                'lines' => [
                    [
                        'account_id' => $dto['inventory_account_id'], // Raw Material Inventory
                        'debit_amount' => (float) $dto['total_amount'],
                        'credit_amount' => 0,
                        'currency' => $dto['currency'] ?? 'PKR',
                        'exchange_rate' => 1,
                        'description' => 'Inventory received from supplier',
                    ],
                    [
                        'account_id' => $dto['accounts_payable_account_id'], // Supplier Payable
                        'debit_amount' => 0,
                        'credit_amount' => (float) $dto['total_amount'],
                        'currency' => $dto['currency'] ?? 'PKR',
                        'exchange_rate' => 1,
                        'description' => 'Amount payable to supplier',
                    ],
                ],
            ]);

            $invoice->status = 'posted';
            $invoice->save();

            Log::info('Supplier invoice posted', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'total_amount' => $dto['total_amount'],
            ]);

            return $invoice;
        }, 3);
    }

    /**
     * Check if a purchase order is fully received and update status.
     */
    private function checkPOCompletion(PurchaseOrder $order): void
    {
        $order->refresh();
        $lines = $order->lines;

        if ($lines->isEmpty()) {
            return;
        }

        $allReceived = $lines->every(function ($line) {
            return bccomp((string) $line->received_quantity, (string) $line->quantity, 4) >= 0;
        });

        $anyReceived = $lines->contains(function ($line) {
            return bccomp((string) $line->received_quantity, '0', 4) > 0;
        });

        if ($allReceived) {
            $order->status = PurchaseOrder::STATUS_RECEIVED;
        } elseif ($anyReceived) {
            $order->status = PurchaseOrder::STATUS_PARTIALLY_RECEIVED;
        }

        $order->save();
    }

    /**
     * List suppliers with filtering.
     */
    public function listSuppliers(array $filters = []): array
    {
        $query = Supplier::where('tenant_id', $filters['tenant_id'] ?? 0)
            ->where('is_active', true);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->get();
    }
}
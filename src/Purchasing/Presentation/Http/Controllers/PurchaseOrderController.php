<?php

namespace Purchasing\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Purchasing\Application\Services\PurchasingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * PurchaseOrderController - purchase order management and goods receipt.
 */
class PurchaseOrderController extends Controller
{
    protected PurchasingService $purchasingService;

    public function __construct(PurchasingService $purchasingService)
    {
        $this->purchasingService = $purchasingService;
    }

    public function index(Request $request): JsonResponse
    {
        $query = \Purchasing\Infrastructure\Persistence\PurchaseOrder::with(['supplier', 'lines'])
            ->where('tenant_id', $request->user()->tenant_id ?? $request->input('tenant_id'));

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->orderBy('created_at', 'desc')->paginate($request->input('per_page', 15)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'factory_id' => 'nullable|exists:factories,id',
            'po_number' => 'required|string|max:100|unique:purchase_orders,po_number',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $po = $this->purchasingService->createPurchaseOrder(array_merge(
            $validated,
            ['tenant_id' => $request->user()->tenant_id ?? $request->input('tenant_id')]
        ));

        return response()->json([
            'status' => 'success',
            'data' => $po->load('supplier'),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $po = \Purchasing\Infrastructure\Persistence\PurchaseOrder::with(['supplier', 'lines'])->findOrFail($id);
        return response()->json(['status' => 'success', 'data' => $po]);
    }

    /**
     * Add lines to a purchase order.
     */
    public function addLines(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.quantity' => 'required|numeric|gt:0',
            'lines.*.unit_id' => 'nullable|exists:units,id',
            'lines.*.unit_price' => 'required|numeric|gte:0',
        ]);

        $lines = $this->purchasingService->addOrderLines($id, $validated['lines']);

        return response()->json([
            'status' => 'success',
            'data' => $lines,
        ], 201);
    }

    /**
     * Receive goods against a purchase order (GRN).
     */
    public function receive(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'grn_number' => 'required|string|max:100|unique:goods_receipts,grn_number',
            'receipt_date' => 'required|date',
            'received_by' => 'required|exists:users,id',
            'lines' => 'required|array|min:1',
            'lines.*.purchase_order_line_id' => 'required|exists:purchase_order_lines,id',
            'lines.*.quantity' => 'required|numeric|gt:0',
            'lines.*.batch_id' => 'nullable|exists:stock_batches,id',
        ]);

        try {
            $receipt = $this->purchasingService->receiveGoods(array_merge(
                $validated,
                ['purchase_order_id' => $id,
                 'tenant_id' => $request->user()->tenant_id ?? $request->input('tenant_id')]
            ));

            return response()->json([
                'status' => 'success',
                'data' => $receipt,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'RECEIPT_FAILED', 'message' => $e->getMessage()],
            ], 400);
        }
    }

    /**
     * Create a supplier invoice.
     */
    public function createInvoice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'required|string|max:100|unique:supplier_invoices,invoice_number',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'total_amount' => 'required|numeric|gt:0',
            'tax_amount' => 'nullable|numeric|gte:0',
            'accounting_book_id' => 'nullable|exists:accounting_books,id',
            'fiscal_period_id' => 'nullable|exists:accounting_periods,id',
            'currency' => 'nullable|string|size:3',
            'created_by' => 'required|exists:users,id',
            'approved_by' => 'nullable|exists:users,id',
            'inventory_account_id' => 'required|exists:chart_of_accounts,id',
            'accounts_payable_account_id' => 'required|exists:chart_of_accounts,id',
        ]);

        try {
            $invoice = $this->purchasingService->createSupplierInvoice($validated);

            return response()->json([
                'status' => 'success',
                'data' => $invoice->load('supplier'),
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'INVOICE_FAILED', 'message' => $e->getMessage()],
            ], 400);
        }
    }
}
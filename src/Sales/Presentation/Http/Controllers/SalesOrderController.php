<?php

namespace Sales\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Sales\Application\Services\SalesService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * SalesOrderController - sales order management.
 */
class SalesOrderController extends Controller
{
    protected SalesService $salesService;

    public function __construct(SalesService $salesService)
    {
        $this->salesService = $salesService;
    }

    public function index(Request $request): JsonResponse
    {
        $query = \Sales\Infrastructure\Persistence\SalesOrder::with(['customer', 'lines'])
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
            'customer_id' => 'required|exists:customers,id',
            'so_number' => 'required|string|max:100|unique:sales_orders,so_number',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $order = $this->salesService->createOrder(array_merge(
            $validated,
            ['tenant_id' => $request->user()->tenant_id ?? $request->input('tenant_id')]
        ));

        return response()->json([
            'status' => 'success',
            'data' => $order->load('customer'),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $order = \Sales\Infrastructure\Persistence\SalesOrder::with(['customer', 'lines', 'reservations'])->findOrFail($id);
        return response()->json(['status' => 'success', 'data' => $order]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $order = \Sales\Infrastructure\Persistence\SalesOrder::findOrFail($id);
        $validated = $request->validate([
            'status' => 'sometimes|string',
            'delivery_date' => 'sometimes|nullable|date',
            'notes' => 'sometimes|nullable|string',
        ]);
        $order->update($validated);
        return response()->json(['status' => 'success', 'data' => $order]);
    }

    /**
     * Confirm order and reserve stock (make-to-stock).
     */
    public function confirm(Request $request, string $id): JsonResponse
    {
        $order = \Sales\Infrastructure\Persistence\SalesOrder::findOrFail($id);

        try {
            $order = $this->salesService->confirmOrder(
                $order,
                $request->user()->id ?? $request->input('user_id')
            );

            return response()->json([
                'status' => 'success',
                'data' => $order,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'CONFIRM_FAILED', 'message' => $e->getMessage()],
            ], 400);
        }
    }

    /**
     * Add lines to a sales order.
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

        $lines = $this->salesService->addOrderLines($id, $validated['lines']);

        return response()->json([
            'status' => 'success',
            'data' => $lines,
        ], 201);
    }

    /**
     * Check available-to-promise quantity.
     */
    public function atp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'item_id' => 'required|exists:items,id',
            'item_variant_id' => 'nullable|exists:item_variants,id',
        ]);

        $atp = $this->salesService->getATPQuantity(
            $request->user()->tenant_id ?? $request->input('tenant_id'),
            $validated['warehouse_id'],
            $validated['item_id'],
            $validated['item_variant_id'] ?? null
        );

        return response()->json([
            'status' => 'success',
            'data' => ['atp_quantity' => $atp],
        ]);
    }

    /**
     * Create a customer invoice.
     */
    public function createInvoice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_number' => 'required|string|max:100|unique:customer_invoices,invoice_number',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'total_amount' => 'required|numeric|gt:0',
            'tax_amount' => 'nullable|numeric|gte:0',
            'accounting_book_id' => 'nullable|exists:accounting_books,id',
            'currency' => 'nullable|string|size:3',
            'created_by' => 'required|exists:users,id',
            'accounts_receivable_account_id' => 'required|exists:chart_of_accounts,id',
            'sales_revenue_account_id' => 'required|exists:chart_of_accounts,id',
        ]);

        try {
            $invoice = $this->salesService->createCustomerInvoice($validated);

            return response()->json([
                'status' => 'success',
                'data' => $invoice->load('customer'),
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => ['code' => 'INVOICE_FAILED', 'message' => $e->getMessage()],
            ], 400);
        }
    }
}
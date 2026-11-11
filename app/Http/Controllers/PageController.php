<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Workspace pages for the operations console.
 *
 * The demo dataset lives in DatabaseSeeder; these handlers shape it into the
 * paginated payloads the Inertia pages expect.
 */
class PageController extends Controller
{
    private function tenantId(): int
    {
        if ($tenantId = \Shared\Support\TenantContext::currentTenantId()) {
            return $tenantId;
        }

        return (int) DB::table('tenants')
            ->where('status', 'active')
            ->orderBy('id')
            ->value('id');
    }

    /**
     * @return array{data: array<int, array<string, mixed>>, meta: array<string, int>}
     */
    private function paginate(Builder $query, int $perPage = 12): array
    {
        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'data' => array_map(static fn ($row) => (array) $row, $paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem() ?? 0,
                'to' => $paginator->lastItem() ?? 0,
            ],
        ];
    }

    private function search(Builder $query, ?string $term, array $columns): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $nested) use ($term, $columns) {
            foreach ($columns as $column) {
                $nested->orWhere($column, 'ilike', "%{$term}%");
            }
        });
    }

    private function day(mixed $value): ?string
    {
        return $value ? Carbon::parse($value)->format('d M Y') : null;
    }

    private function money(float|int|string|null $value): string
    {
        return 'Rs. ' . number_format((float) $value);
    }

    public function dashboard(): Response
    {
        $tenantId = $this->tenantId();
        $user = \Illuminate\Support\Facades\Auth::user();
        $userRole = $user?->role ?? 'viewer';
        $userRoleKey = strtolower(str_replace(' ', '_', $userRole));

        $productionToday = (float) DB::table('production_orders')
            ->where('tenant_id', $tenantId)
            ->whereDate('actual_end_date', today())
            ->whereIn('status', ['completed', 'closed'])
            ->sum('good_quantity');

        $rawMaterialValue = (float) DB::table('stock_balances as sb')
            ->join('items as i', 'i.id', '=', 'sb.item_id')
            ->join('item_categories as c', 'c.id', '=', 'i.category_id')
            ->where('sb.tenant_id', $tenantId)
            ->whereIn('c.code', ['FAB', 'TRM', 'PKG'])
            ->selectRaw('COALESCE(SUM(sb.on_hand * i.default_purchase_price), 0) as value')
            ->value('value');

        $ordersDelayed = (int) DB::table('production_orders')
            ->where('tenant_id', $tenantId)
            ->whereNotNull('planned_end_date')
            ->where('planned_end_date', '<', now())
            ->whereNotIn('status', ['completed', 'closed', 'cancelled'])
            ->count();

        $finishedGoods = (float) DB::table('stock_balances as sb')
            ->join('items as i', 'i.id', '=', 'sb.item_id')
            ->join('item_categories as c', 'c.id', '=', 'i.category_id')
            ->where('sb.tenant_id', $tenantId)
            ->where('c.code', 'FG')
            ->sum('sb.on_hand');

        $inspected = (int) DB::table('quality_inspections')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['passed', 'failed', 'partially_accepted', 'rejected'])
            ->count();

        $rejected = (int) DB::table('quality_inspections')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['failed', 'rejected'])
            ->count();

        $unitCost = (float) DB::table('production_cost_variances as v')
            ->join('production_orders as po', 'po.id', '=', 'v.production_order_id')
            ->where('v.tenant_id', $tenantId)
            ->where('po.planned_quantity', '>', 0)
            ->selectRaw('AVG(v.actual_cost / po.planned_quantity) as value')
            ->value('value');

        $activity = DB::table('stock_movements as sm')
            ->leftJoin('stock_movement_lines as sml', 'sml.movement_id', '=', 'sm.id')
            ->leftJoin('items as i', 'i.id', '=', 'sml.item_id')
            ->where('sm.tenant_id', $tenantId)
            ->orderByDesc('sm.transaction_date')
            ->limit(6)
            ->get([
                'sm.reference',
                'sm.movement_type',
                'sm.transaction_date',
                'i.name as item_name',
                'sml.quantity',
            ])
            ->map(function ($row) {
                $type = str_replace('_', ' ', $row->movement_type);

                return [
                    'title' => $row->reference,
                    'detail' => ($row->item_name ?? 'Stock item') . ' · ' . number_format((float) $row->quantity) . ' units · ' . $type,
                    'time' => Carbon::parse($row->transaction_date)->diffForHumans(),
                ];
            })
            ->all();

        // Role-specific data
        $roleData = [];

        if (in_array($userRoleKey, ['super_admin', 'admin'])) {
            $roleData['tenant_count'] = (int) DB::table('tenants')->count();
            $roleData['user_count'] = (int) DB::table('users')->where('tenant_id', $tenantId)->count();
            $roleData['active_tenants'] = (int) DB::table('tenants')->where('status', 'active')->count();
        }

        if (in_array($userRoleKey, ['super_admin', 'admin', 'manager'])) {
            $roleData['total_production_orders'] = (int) DB::table('production_orders')->where('tenant_id', $tenantId)->count();
            $roleData['pending_orders'] = (int) DB::table('production_orders')->where('tenant_id', $tenantId)->whereIn('status', ['draft', 'pending_approval', 'approved'])->count();
            $roleData['total_revenue'] = (float) DB::table('customer_invoices')->where('tenant_id', $tenantId)->where('status', 'paid')->sum('total_amount');
            $roleData['total_payables'] = (float) DB::table('supplier_invoices')->where('tenant_id', $tenantId)->where('status', 'unpaid')->sum('total_amount');
        }

        if (in_array($userRoleKey, ['super_admin', 'admin', 'manager', 'operator'])) {
            $roleData['work_orders_today'] = (int) DB::table('work_orders')->where('tenant_id', $tenantId)->whereDate('started_at', today())->count();
            $roleData['quality_pending'] = (int) DB::table('quality_inspections')->where('tenant_id', $tenantId)->where('status', 'pending')->count();
            $roleData['low_stock_items'] = (int) DB::table('stock_balances as sb')
                ->join('items as i', 'i.id', '=', 'sb.item_id')
                ->where('sb.tenant_id', $tenantId)
                ->whereColumn('sb.on_hand', '<', 'i.reorder_level')
                ->count();
        }

        $alerts = [];
        try { $alerts['pending_approvals'] = (int) DB::table('approval_requests')->where('tenant_id', $tenantId)->where('status', 'pending')->count(); } catch (\Throwable $e) { $alerts['pending_approvals'] = 0; }
        try { $alerts['unread_notifications'] = (int) DB::table('notifications')->where('tenant_id', $tenantId)->whereNull('read_at')->count(); } catch (\Throwable $e) { $alerts['unread_notifications'] = 0; }
        try { $alerts['open_purchase_orders'] = (int) DB::table('purchase_orders')->where('tenant_id', $tenantId)->whereNotIn('status', ['received', 'closed', 'cancelled'])->count(); } catch (\Throwable $e) { $alerts['open_purchase_orders'] = 0; }
        try { $alerts['overdue_sales_invoices'] = (int) DB::table('customer_invoices')->where('tenant_id', $tenantId)->where('status', '!=', 'paid')->whereNotNull('due_date')->where('due_date', '<', now()->toDateString())->count(); } catch (\Throwable $e) { $alerts['overdue_sales_invoices'] = 0; }

        $quick_links = [
            ['label' => 'Create Sales Quote', 'href' => '/sales/quotes/create'],
            ['label' => 'New Purchase Order', 'href' => '/purchasing/create'],
            ['label' => 'Run MRP', 'href' => '/mrp/run'],
            ['label' => 'View Approvals', 'href' => '/workflow/approvals'],
            ['label' => 'Audit Trail', 'href' => '/compliance/audit-trail'],
            ['label' => 'Notifications', 'href' => '/notifications'],
        ];

        return Inertia::render('Dashboard', [
            'user_role' => $userRole,
            'user_role_key' => $userRoleKey,
            'stats' => [
                'production_today' => $productionToday,
                'raw_material_value' => $rawMaterialValue,
                'orders_delayed' => $ordersDelayed,
                'finished_goods' => $finishedGoods,
                'quality_rejection_rate' => $inspected > 0 ? round(($rejected / $inspected) * 100, 1) : 0.0,
                'actual_unit_cost' => round($unitCost, 2),
            ],
            'role_stats' => $roleData,
            'alerts' => $alerts,
            'quick_links' => $quick_links,
            'activity' => $activity,
        ]);
    }

    public function inventory(Request $request): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('stock_movements as sm')
            ->leftJoin('stock_movement_lines as sml', 'sml.movement_id', '=', 'sm.id')
            ->leftJoin('items as i', 'i.id', '=', 'sml.item_id')
            ->leftJoin('warehouses as w', 'w.id', '=', 'sm.warehouse_id')
            ->where('sm.tenant_id', $tenantId)
            ->select([
                'sm.id',
                'sm.reference',
                'sm.movement_type',
                'i.name as item_name',
                'w.name as warehouse_name',
                'sml.quantity',
                'sm.transaction_date',
                'sm.posted_at',
            ])
            ->orderByDesc('sm.transaction_date')
            ->orderByDesc('sm.id');

        $payload = $this->paginate(
            $this->search($query, $request->query('q'), ['sm.reference', 'i.name', 'w.name'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['quantity'] = (float) $row['quantity'];
            $row['movement_type'] = ucfirst(str_replace('_', ' ', $row['movement_type']));
            $row['transaction_date'] = $this->day($row['transaction_date']);
            $row['status'] = $row['posted_at'] ? 'posted' : 'draft';
            unset($row['posted_at']);

            return $row;
        }, $payload['data']);

        $summary = DB::table('stock_balances as sb')
            ->join('items as i', 'i.id', '=', 'sb.item_id')
            ->where('sb.tenant_id', $tenantId)
            ->selectRaw('COALESCE(SUM(sb.on_hand), 0) as on_hand')
            ->selectRaw('COALESCE(SUM(sb.on_hand * COALESCE(NULLIF(i.default_purchase_price, 0), i.standard_production_cost, 0)), 0) as value')
            ->selectRaw('COALESCE(SUM(CASE WHEN sb.on_hand < i.reorder_level THEN 1 ELSE 0 END), 0) as low_stock')
            ->first();

        return Inertia::render('Inventory', [
            'movements' => $payload,
            'inventory_summary' => [
                'on_hand' => number_format((float) $summary->on_hand),
                'value' => $this->money($summary->value),
                'low_stock' => (int) $summary->low_stock,
            ],
        ]);
    }

    public function production(Request $request): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('production_orders as po')
            ->join('bom_headers as bh', 'bh.id', '=', 'po.bom_header_id')
            ->leftJoin('items as i', 'i.id', '=', 'bh.item_id')
            ->where('po.tenant_id', $tenantId)
            ->select([
                'po.id',
                'po.order_number',
                'i.name as item_name',
                'po.planned_quantity',
                'po.status',
                'po.planned_start_date',
            ])
            ->orderByDesc('po.planned_start_date')
            ->orderByDesc('po.id');

        $payload = $this->paginate(
            $this->search($query, $request->query('q'), ['po.order_number', 'i.name'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['planned_quantity'] = (float) $row['planned_quantity'];
            $row['planned_start_date'] = $this->day($row['planned_start_date']);

            return $row;
        }, $payload['data']);

        $summary = DB::table('production_orders')
            ->where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COUNT(*) FILTER (WHERE status IN ('in_production', 'quality_inspection')) as active")
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'completed') as completed")
            ->first();

        $late = DB::table('production_orders')
            ->where('tenant_id', $tenantId)
            ->whereNotNull('planned_end_date')
            ->where('planned_end_date', '<', now())
            ->whereNotIn('status', ['completed', 'closed', 'cancelled'])
            ->count();

        return Inertia::render('Production', [
            'production_orders' => $payload,
            'production_summary' => [
                'total' => (int) $summary->total,
                'active' => (int) $summary->active,
                'completed' => (int) $summary->completed,
                'late' => $late,
            ],
        ]);
    }

    public function quality(Request $request): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('quality_inspections as qi')
            ->leftJoin('production_orders as po', function ($join) {
                $join->on('po.id', '=', 'qi.reference_id')
                    ->where('qi.reference_type', '=', 'production_order');
            })
            ->leftJoin('bom_headers as bh', 'bh.id', '=', 'po.bom_header_id')
            ->leftJoin('items as i', 'i.id', '=', 'bh.item_id')
            ->where('qi.tenant_id', $tenantId)
            ->select([
                'qi.id',
                'qi.inspection_type',
                'qi.status',
                'qi.inspected_at',
                'i.name as item_name',
                DB::raw("COALESCE(po.order_number, 'INSP-' || qi.id) as reference"),
            ])
            ->orderByDesc('qi.inspected_at')
            ->orderByDesc('qi.id');

        $payload = $this->paginate(
            $this->search($query, $request->query('q'), ['po.order_number', 'i.name'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['inspection_type'] = ucfirst(str_replace('_', ' ', $row['inspection_type']));
            $row['inspected_at'] = $this->day($row['inspected_at']);

            return $row;
        }, $payload['data']);

        $summary = DB::table('quality_inspections')
            ->where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'passed') as passed")
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'pending') as pending")
            ->selectRaw("COUNT(*) FILTER (WHERE status IN ('failed', 'rejected')) as failed")
            ->first();

        return Inertia::render('Quality', [
            'inspections' => $payload,
            'quality_summary' => [
                'total' => (int) $summary->total,
                'passed' => (int) $summary->passed,
                'pending' => (int) $summary->pending,
                'failed' => (int) $summary->failed,
            ],
        ]);
    }

    public function costing(Request $request): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('production_cost_variances as v')
            ->join('production_orders as po', 'po.id', '=', 'v.production_order_id')
            ->leftJoin('bom_headers as bh', 'bh.id', '=', 'po.bom_header_id')
            ->leftJoin('items as i', 'i.id', '=', 'bh.item_id')
            ->where('v.tenant_id', $tenantId)
            ->select([
                'v.id',
                'po.order_number as production_order_id',
                'i.name as item_name',
                'v.standard_cost',
                'v.actual_cost',
                'v.variance_amount',
                'v.variance_percentage',
            ])
            ->orderByDesc('v.calculated_at');

        $payload = $this->paginate(
            $this->search($query, $request->query('q'), ['po.order_number', 'i.name'])
        );

        $payload['data'] = array_map(fn (array $row) => [
            'id' => $row['id'],
            'production_order_id' => $row['production_order_id'],
            'item_name' => $row['item_name'],
            'standard_cost' => $this->money($row['standard_cost']),
            'actual_cost' => $this->money($row['actual_cost']),
            'variance_amount' => ($row['variance_amount'] < 0 ? '−' : '+') . $this->money(abs((float) $row['variance_amount'])),
            'variance_percentage' => number_format((float) $row['variance_percentage'], 2) . '%',
        ], $payload['data']);

        $summary = DB::table('production_costs')
            ->where('tenant_id', $tenantId)
            ->selectRaw('COALESCE(SUM(material_cost), 0) as material')
            ->selectRaw('COALESCE(SUM(labour_cost), 0) as labour')
            ->selectRaw('COALESCE(SUM(machine_cost) + SUM(overhead_cost), 0) as machine')
            ->selectRaw('CASE WHEN SUM(quantity) > 0 THEN SUM(total_cost) / SUM(quantity) ELSE 0 END as unit_cost')
            ->first();

        return Inertia::render('Costing', [
            'variances' => $payload,
            'cost_summary' => [
                'material' => $this->money($summary->material),
                'labour' => $this->money($summary->labour),
                'machine' => $this->money($summary->machine),
                'unit_cost' => $this->money($summary->unit_cost),
            ],
        ]);
    }

    public function purchasing(Request $request): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('suppliers')
            ->where('tenant_id', $tenantId)
            ->select(['id', 'name', 'code', 'contact_person', 'email', 'is_active'])
            ->orderBy('name');

        $payload = $this->paginate(
            $this->search($query, $request->query('q'), ['name', 'code', 'email', 'contact_person'])
        );

        $payload['data'] = array_map(fn (array $row) => [
            ...$row,
            'status' => $row['is_active'] ? 'active' : 'inactive',
        ], $payload['data']);

        $summary = DB::table('suppliers')
            ->where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(*) FILTER (WHERE is_active) as active')
            ->first();

        $payable = (float) DB::table('supplier_bills')
            ->where('tenant_id', $tenantId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->sum('total_amount');

        return Inertia::render('Purchasing', [
            'suppliers' => $payload,
            'purchasing_summary' => [
                'total' => (int) $summary->total,
                'active' => (int) $summary->active,
                'payable' => $this->money($payable),
            ],
        ]);
    }

    public function sales(Request $request): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('customers')
            ->where('tenant_id', $tenantId)
            ->select(['id', 'name', 'code', 'contact_person', 'email', 'credit_limit'])
            ->orderBy('name');

        $payload = $this->paginate(
            $this->search($query, $request->query('q'), ['name', 'code', 'email', 'contact_person'])
        );

        $payload['data'] = array_map(fn (array $row) => [
            ...$row,
            'credit_limit' => $this->money($row['credit_limit']),
        ], $payload['data']);

        $summary = DB::table('customers')
            ->where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(*) FILTER (WHERE is_active) as active')
            ->first();

        $openOrders = (int) DB::table('sales_orders')
            ->where('tenant_id', $tenantId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $receivable = (float) DB::table('customer_invoices')
            ->where('tenant_id', $tenantId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->sum('total_amount');

        return Inertia::render('Sales', [
            'customers' => $payload,
            'sales_summary' => [
                'total' => (int) $summary->total,
                'open_orders' => $openOrders,
                'receivable' => $this->money($receivable),
            ],
        ]);
    }

    public function dispatch(Request $request): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('dispatches as d')
            ->leftJoin('sales_orders as so', 'so.id', '=', 'd.sales_order_id')
            ->where('d.tenant_id', $tenantId)
            ->select([
                'd.id',
                'd.dispatch_number',
                'd.driver_name',
                'd.dispatch_date',
                'd.status',
                DB::raw("COALESCE(so.so_number, '—') as sales_order_id"),
            ])
            ->orderByDesc('d.dispatch_date')
            ->orderByDesc('d.id');

        $payload = $this->paginate(
            $this->search($query, $request->query('q'), ['d.dispatch_number', 'd.driver_name', 'so.so_number'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['dispatch_date'] = $this->day($row['dispatch_date']);

            return $row;
        }, $payload['data']);

        $summary = DB::table('dispatches')
            ->where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'in_transit') as in_transit")
            ->selectRaw("COUNT(*) FILTER (WHERE status IN ('pending', 'approved')) as waiting")
            ->first();

        return Inertia::render('Dispatch', [
            'dispatches' => $payload,
            'dispatch_summary' => [
                'total' => (int) $summary->total,
                'in_transit' => (int) $summary->in_transit,
                'waiting' => (int) $summary->waiting,
            ],
        ]);
    }

    public function accounting(Request $request): Response
    {
        $tenantId = $this->tenantId();

        $status = (string) $request->query('status', '');
        $from = $request->query('from');
        $to = $request->query('to');

        $query = DB::table('journal_entries')
            ->where('tenant_id', $tenantId)
            ->select(['id', 'reference', 'document_date', 'status', 'description'])
            ->selectSub(function ($sub) {
                $sub->from('journal_lines')
                    ->selectRaw('COALESCE(SUM(debit_amount_base), 0)')
                    ->whereColumn('journal_lines.entry_id', 'journal_entries.id');
            }, 'debit_total')
            ->selectSub(function ($sub) {
                $sub->from('journal_lines')
                    ->selectRaw('COALESCE(SUM(credit_amount_base), 0)')
                    ->whereColumn('journal_lines.entry_id', 'journal_entries.id');
            }, 'credit_total')
            ->orderByDesc('document_date')
            ->orderByDesc('id');

        if (in_array($status, ['draft', 'submitted', 'posted', 'reversed'], true)) {
            $query->where('status', $status);
        }
        if ($from) $query->whereDate('document_date', '>=', $from);
        if ($to) $query->whereDate('document_date', '<=', $to);

        $payload = $this->paginate(
            $this->search($query, $request->query('q'), ['reference', 'description'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['document_date'] = $this->day($row['document_date']);
            $row['debit_total'] = (float) ($row['debit_total'] ?? 0);
            $row['credit_total'] = (float) ($row['credit_total'] ?? 0);

            return $row;
        }, $payload['data']);

        $trialBalance = (float) DB::table('journal_lines as jl')
            ->join('journal_entries as je', 'je.id', '=', 'jl.entry_id')
            ->where('je.tenant_id', $tenantId)
            ->where('je.status', 'posted')
            ->sum('jl.debit_amount_base');

        $receivable = (float) DB::table('customer_invoices')
            ->where('tenant_id', $tenantId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->sum('total_amount');

        $payable = (float) DB::table('supplier_bills')
            ->where('tenant_id', $tenantId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->sum('total_amount');

        $statusCounts = DB::table('journal_entries')
            ->where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'draft') as draft")
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'submitted') as submitted")
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'posted') as posted")
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'reversed') as reversed")
            ->first();

        return Inertia::render('Accounting', [
            'journal_entries' => $payload,
            'filters' => ['q' => (string) $request->query('q', ''), 'status' => $status, 'from' => $from ?? '', 'to' => $to ?? ''],
            'journal_entry_counts' => [
                'total' => (int) ($statusCounts->total ?? 0),
                'draft' => (int) ($statusCounts->draft ?? 0),
                'submitted' => (int) ($statusCounts->submitted ?? 0),
                'posted' => (int) ($statusCounts->posted ?? 0),
                'reversed' => (int) ($statusCounts->reversed ?? 0),
            ],
            'finance_summary' => [
                'trial_balance' => $this->money($trialBalance),
                'receivable' => $this->money($receivable),
                'payable' => $this->money($payable),
            ],
        ]);
    }

    public function chartOfAccounts(Request $request): Response
    {
        $tenantId = $this->tenantId();
        $q = trim((string) $request->query('q', ''));
        $type = $request->query('type');
        $status = $request->query('status');
        $usage = $request->query('usage'); // used|unused
        $posting = $request->query('posting'); // direct|blocked
        $flag = $request->query('flag'); // cash|bank
        $hasChildren = $request->query('has_children'); // yes|no
        $groupId = $request->query('group');

        $childrenSub = DB::table('chart_of_accounts as c2')
            ->selectRaw('COUNT(*)')
            ->whereColumn('c2.parent_id', 'coa.id')
            ->where('c2.tenant_id', $tenantId);
        $journalSub = DB::table('journal_lines as jl')
            ->selectRaw('COUNT(*)')
            ->whereColumn('jl.account_id', 'coa.id')
            ->where('jl.tenant_id', $tenantId);
        $balanceSub = DB::table('journal_lines as jlb')
            ->join('journal_entries as je', 'je.id', '=', 'jlb.entry_id')
            ->selectRaw('COALESCE(SUM(jlb.debit_amount_base - jlb.credit_amount_base), 0)')
            ->whereColumn('jlb.account_id', 'coa.id')
            ->where('jlb.tenant_id', $tenantId)
            ->where('je.status', 'posted');

        $query = DB::table('chart_of_accounts as coa')
            ->leftJoin('chart_of_accounts as parent', 'parent.id', '=', 'coa.parent_id')
            ->leftJoin('account_groups as ag', 'ag.id', '=', 'coa.account_group_id')
            ->where('coa.tenant_id', $tenantId)
            ->select([
                'coa.id', 'coa.code', 'coa.name', 'coa.type',
                'coa.allow_direct_posting', 'coa.is_cash_account', 'coa.is_bank_account', 'coa.is_active',
                'coa.parent_id', 'coa.account_group_id',
                'parent.code as parent_code', 'parent.name as parent_name',
                'ag.name as group_name',
            ])
            ->selectSub($childrenSub, 'children_count')
            ->selectSub($journalSub, 'journal_lines_count')
            ->selectSub($balanceSub, 'posted_balance')
            ->orderBy('coa.code');

        if ($q !== '') {
            $query->where(function ($nested) use ($q) {
                $nested->where('coa.code', 'ilike', "%{$q}%")
                    ->orWhere('coa.name', 'ilike', "%{$q}%")
                    ->orWhere('parent.name', 'ilike', "%{$q}%")
                    ->orWhere('ag.name', 'ilike', "%{$q}%");
            });
        }
        if ($type && $type !== 'all') $query->where('coa.type', $type);
        if ($status === 'active') $query->where('coa.is_active', true);
        elseif ($status === 'inactive') $query->where('coa.is_active', false);

        if ($posting === 'direct') $query->where('coa.allow_direct_posting', true);
        elseif ($posting === 'blocked') $query->where('coa.allow_direct_posting', false);

        if ($flag === 'cash') $query->where('coa.is_cash_account', true);
        elseif ($flag === 'bank') $query->where('coa.is_bank_account', true);

        if ($groupId) $query->where('coa.account_group_id', $groupId);

        if ($usage === 'used') $query->whereRaw('(SELECT COUNT(*) FROM journal_lines jl2 WHERE jl2.account_id = coa.id AND jl2.tenant_id = ?) > 0', [$tenantId]);
        elseif ($usage === 'unused') $query->whereRaw('(SELECT COUNT(*) FROM journal_lines jl2 WHERE jl2.account_id = coa.id AND jl2.tenant_id = ?) = 0', [$tenantId]);

        if ($hasChildren === 'yes') $query->whereRaw('(SELECT COUNT(*) FROM chart_of_accounts c3 WHERE c3.parent_id = coa.id AND c3.tenant_id = ?) > 0', [$tenantId]);
        elseif ($hasChildren === 'no') $query->whereRaw('(SELECT COUNT(*) FROM chart_of_accounts c3 WHERE c3.parent_id = coa.id AND c3.tenant_id = ?) = 0', [$tenantId]);

        $normalize = function (array $row) {
            $row['allow_direct_posting'] = (bool) ($row['allow_direct_posting'] ?? false);
            $row['is_cash_account'] = (bool) ($row['is_cash_account'] ?? false);
            $row['is_bank_account'] = (bool) ($row['is_bank_account'] ?? false);
            $row['is_active'] = (bool) ($row['is_active'] ?? true);
            $row['children_count'] = (int) ($row['children_count'] ?? 0);
            $row['journal_lines_count'] = (int) ($row['journal_lines_count'] ?? 0);
            $row['posted_balance'] = (float) ($row['posted_balance'] ?? 0);
            $row['can_delete'] = $row['children_count'] === 0 && $row['journal_lines_count'] === 0;
            $row['parent_label'] = $row['parent_code'] ? ($row['parent_code'] . ' — ' . ($row['parent_name'] ?? '')) : null;
            return $row;
        };

        $view = $request->query('view') === 'tree' ? 'tree' : 'table';

        $tree = null;
        if ($view === 'tree') {
            $allRows = (clone $query)->limit(5000)->get()
                ->map(static fn ($r) => $normalize((array) $r))
                ->all();
            $tree = $this->buildAccountTree($allRows);
        }

        $payload = $this->paginate($query);
        $payload['data'] = array_map($normalize, $payload['data']);

        $byType = DB::table('chart_of_accounts')
            ->where('tenant_id', $tenantId)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        $typeCounts = [];
        foreach ($byType as $k => $v) $typeCounts[$k] = (int) $v;

        $summary = [
            'total' => (int) DB::table('chart_of_accounts')->where('tenant_id', $tenantId)->count(),
            'active' => (int) DB::table('chart_of_accounts')->where('tenant_id', $tenantId)->where('is_active', true)->count(),
            'inactive' => (int) DB::table('chart_of_accounts')->where('tenant_id', $tenantId)->where('is_active', false)->count(),
            'posting' => (int) DB::table('chart_of_accounts')->where('tenant_id', $tenantId)->where('allow_direct_posting', true)->count(),
        ];

        $groupOptions = DB::table('account_groups')
            ->where('tenant_id', $tenantId)
            ->select(['id', 'code', 'name'])
            ->orderBy('code')
            ->get();

        return Inertia::render('AccountingChartOfAccounts', [
            'accounts' => $payload,
            'type_counts' => $typeCounts,
            'summary' => $summary,
            'groups' => $groupOptions,
            'filters' => [
                'q' => $q,
                'type' => $type ?? 'all',
                'status' => $status ?? 'all',
                'usage' => $usage ?? 'all',
                'posting' => $posting ?? 'all',
                'flag' => $flag ?? 'all',
                'has_children' => $hasChildren ?? 'all',
                'group' => $groupId ?? 'all',
                'view' => $view,
            ],
            'tree' => $tree,
            'view' => $view,
        ]);
    }

    private function buildAccountTree(array $rows): array
    {
        $byId = [];
        foreach ($rows as $row) {
            $row['children'] = [];
            $byId[$row['id']] = $row;
        }

        $roots = [];
        foreach (array_keys($byId) as $id) {
            $node = &$byId[$id];
            $parentId = $node['parent_id'] ?? null;
            if ($parentId && isset($byId[$parentId]) && (string) $parentId !== (string) $id) {
                $byId[$parentId]['children'][] = &$node;
            } else {
                $roots[] = &$node;
            }
            unset($node);
        }

        return $roots;
    }

    public function trialBalancePage(Request $request): Response
    {
        $tenantId = $this->tenantId();
        $asOf = $request->query('as_of');

        $accounts = DB::table('chart_of_accounts as coa')
            ->where('coa.tenant_id', $tenantId)
            ->where('coa.allow_direct_posting', true)
            ->select(['coa.id', 'coa.code', 'coa.name', 'coa.type'])
            ->orderBy('coa.code')
            ->get();

        $rows = $accounts->map(function ($account) use ($tenantId, $asOf) {
            $debitQuery = DB::table('journal_lines as jl')
                ->join('journal_entries as je', 'je.id', '=', 'jl.entry_id')
                ->where('je.tenant_id', $tenantId)
                ->where('je.status', 'posted')
                ->where('jl.account_id', $account->id);
            if ($asOf) $debitQuery->whereDate('je.posting_date', '<=', $asOf);

            $creditQuery = DB::table('journal_lines as jl')
                ->join('journal_entries as je', 'je.id', '=', 'jl.entry_id')
                ->where('je.tenant_id', $tenantId)
                ->where('je.status', 'posted')
                ->where('jl.account_id', $account->id);
            if ($asOf) $creditQuery->whereDate('je.posting_date', '<=', $asOf);

            $debit = (float) $debitQuery->sum('jl.debit_amount_base');
            $credit = (float) $creditQuery->sum('jl.credit_amount_base');

            $balance = $debit - $credit;

            return [
                'account_id' => $account->id,
                'account_code' => $account->code,
                'account_name' => $account->name,
                'account_type' => $account->type,
                'debit_balance' => $balance > 0 ? $balance : 0,
                'credit_balance' => $balance < 0 ? abs($balance) : 0,
            ];
        });

        $totalDebit = $rows->sum('debit_balance');
        $totalCredit = $rows->sum('credit_balance');

        return Inertia::render('AccountingTrialBalance', [
            'accounts' => $rows,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
            'filters' => ['as_of' => $asOf ?? ''],
        ]);
    }

    public function profitAndLossPage(Request $request): Response
    {
        $tenantId = $this->tenantId();
        $from = $request->query('from');
        $to = $request->query('to');

        $revenueAccounts = DB::table('chart_of_accounts')
            ->where('tenant_id', $tenantId)
            ->where('type', 'revenue')
            ->select(['id', 'code', 'name'])
            ->get();

        $expenseAccounts = DB::table('chart_of_accounts')
            ->where('tenant_id', $tenantId)
            ->where('type', 'expense')
            ->select(['id', 'code', 'name'])
            ->get();

        $getAccountTurnover = function (array $account) use ($tenantId, $from, $to) {
            $debitQuery = DB::table('journal_lines as jl')
                ->join('journal_entries as je', 'je.id', '=', 'jl.entry_id')
                ->where('je.tenant_id', $tenantId)
                ->where('je.status', 'posted')
                ->where('jl.account_id', $account['id']);
            $creditQuery = clone $debitQuery;
            if ($from) {
                $debitQuery->whereDate('je.posting_date', '>=', $from);
                $creditQuery->whereDate('je.posting_date', '>=', $from);
            }
            if ($to) {
                $debitQuery->whereDate('je.posting_date', '<=', $to);
                $creditQuery->whereDate('je.posting_date', '<=', $to);
            }

            $debit = (float) $debitQuery->sum('jl.debit_amount_base');
            $credit = (float) $creditQuery->sum('jl.credit_amount_base');

            return [
                'account_id' => $account['id'],
                'code' => $account['code'],
                'name' => $account['name'],
                'turnover' => $credit - $debit,
            ];
        };

        $revenue = $revenueAccounts->map($getAccountTurnover);
        $expenses = $expenseAccounts->map($getAccountTurnover);

        $totalRevenue = $revenue->sum('turnover');
        $totalExpenses = $expenses->sum('turnover');
        $netProfit = $totalRevenue - $totalExpenses;

        return Inertia::render('AccountingProfitLoss', [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
            'filters' => ['from' => $from ?? '', 'to' => $to ?? ''],
        ]);
    }

    public function balanceSheetPage(Request $request): Response
    {
        $tenantId = $this->tenantId();
        $asOf = $request->query('as_of');

        $assetAccounts = DB::table('chart_of_accounts')
            ->where('tenant_id', $tenantId)
            ->where('type', 'asset')
            ->select(['id', 'code', 'name'])
            ->get();

        $liabilityAccounts = DB::table('chart_of_accounts')
            ->where('tenant_id', $tenantId)
            ->where('type', 'liability')
            ->select(['id', 'code', 'name'])
            ->get();

        $equityAccounts = DB::table('chart_of_accounts')
            ->where('tenant_id', $tenantId)
            ->where('type', 'equity')
            ->select(['id', 'code', 'name'])
            ->get();

        $getAccountBalance = function (array $account) use ($tenantId, $asOf) {
            $debitQuery = DB::table('journal_lines as jl')
                ->join('journal_entries as je', 'je.id', '=', 'jl.entry_id')
                ->where('je.tenant_id', $tenantId)
                ->where('je.status', 'posted')
                ->where('jl.account_id', $account['id']);
            $creditQuery = clone $debitQuery;
            if ($asOf) {
                $debitQuery->whereDate('je.posting_date', '<=', $asOf);
                $creditQuery->whereDate('je.posting_date', '<=', $asOf);
            }

            $debit = (float) $debitQuery->sum('jl.debit_amount_base');
            $credit = (float) $creditQuery->sum('jl.credit_amount_base');

            return [
                'account_id' => $account['id'],
                'code' => $account['code'],
                'name' => $account['name'],
                'balance' => $debit - $credit,
            ];
        };

        $assets = $assetAccounts->map($getAccountBalance);
        $liabilities = $liabilityAccounts->map($getAccountBalance);
        $equity = $equityAccounts->map($getAccountBalance);

        $totalAssets = $assets->sum('balance');
        $totalLiabilities = $liabilities->sum('balance');
        $totalEquity = $equity->sum('balance');

        return Inertia::render('AccountingBalanceSheet', [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'filters' => ['as_of' => $asOf ?? ''],
        ]);
    }

    /**
     * Cash flow statement derived from posted journal entries that touch
     * cash/bank accounts. Counterpart accounts are classified as operating,
     * investing, or financing based on their account type.
     */
    public function cashFlowPage(Request $request): Response
    {
        $tenantId = $this->tenantId();
        $from = $request->query('from');
        $to = $request->query('to');

        $cashAccountIds = DB::table('chart_of_accounts')
            ->where('tenant_id', $tenantId)
            ->where(function ($q) {
                $q->where('is_cash_account', true)->orWhere('is_bank_account', true);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $opening = 0.0;
        if (!empty($cashAccountIds) && $from) {
            $openingQuery = DB::table('journal_lines as jl')
                ->join('journal_entries as je', 'je.id', '=', 'jl.entry_id')
                ->where('je.tenant_id', $tenantId)
                ->where('je.status', 'posted')
                ->whereIn('jl.account_id', $cashAccountIds)
                ->whereDate('je.posting_date', '<', $from);
            try {
                $opening = (float) $openingQuery->selectRaw('COALESCE(SUM(jl.debit_amount_base - jl.credit_amount_base), 0) as bal')->value('bal');
            } catch (\Throwable $e) {
                $opening = 0.0;
            }
        }

        $closing = 0.0;
        if (!empty($cashAccountIds)) {
            $closingQuery = DB::table('journal_lines as jl')
                ->join('journal_entries as je', 'je.id', '=', 'jl.entry_id')
                ->where('je.tenant_id', $tenantId)
                ->where('je.status', 'posted')
                ->whereIn('jl.account_id', $cashAccountIds);
            if ($to) $closingQuery->whereDate('je.posting_date', '<=', $to);
            try {
                $closing = (float) $closingQuery->selectRaw('COALESCE(SUM(jl.debit_amount_base - jl.credit_amount_base), 0) as bal')->value('bal');
            } catch (\Throwable $e) {
                $closing = 0.0;
            }
        }

        $rows = collect();
        if (!empty($cashAccountIds)) {
            $query = DB::table('journal_lines as jl')
                ->join('journal_entries as je', 'je.id', '=', 'jl.entry_id')
                ->join('chart_of_accounts as coa', 'coa.id', '=', 'jl.account_id')
                ->where('je.tenant_id', $tenantId)
                ->where('je.status', 'posted')
                ->whereNotIn('jl.account_id', $cashAccountIds)
                ->whereExists(function ($sub) use ($tenantId, $cashAccountIds) {
                    $sub->selectRaw('1')
                        ->from('journal_lines as jlc')
                        ->whereColumn('jlc.entry_id', 'jl.entry_id')
                        ->where('jlc.tenant_id', $tenantId)
                        ->whereIn('jlc.account_id', $cashAccountIds);
                });

            if ($from) $query->whereDate('je.posting_date', '>=', $from);
            if ($to) $query->whereDate('je.posting_date', '<=', $to);

            try {
                $raw = $query->select([
                    'jl.account_id',
                    'coa.code as account_code',
                    'coa.name as account_name',
                    'coa.type as account_type',
                ])
                    ->selectRaw('COALESCE(SUM(jl.credit_amount_base - jl.debit_amount_base), 0) as net')
                    ->selectRaw('COALESCE(SUM(jl.credit_amount_base), 0) as credit_total')
                    ->selectRaw('COALESCE(SUM(jl.debit_amount_base), 0) as debit_total')
                    ->groupBy('jl.account_id', 'coa.code', 'coa.name', 'coa.type')
                    ->orderBy('coa.code')
                    ->get();

                $rows = $raw->map(function ($r) {
                    $type = (string) ($r->account_type ?? '');
                    $category = in_array($type, ['revenue', 'expense'], true)
                        ? 'operating'
                        : ($type === 'asset' ? 'investing' : (in_array($type, ['liability', 'equity'], true) ? 'financing' : 'operating'));

                    $net = (float) $r->net;
                    return [
                        'account_id' => $r->account_id,
                        'account_code' => $r->account_code,
                        'account_name' => $r->account_name,
                        'account_type' => $type,
                        'category' => $category,
                        'inflow' => $net > 0 ? $net : 0,
                        'outflow' => $net < 0 ? abs($net) : 0,
                        'net' => $net,
                        'gross_inflow' => (float) $r->credit_total,
                        'gross_outflow' => (float) $r->debit_total,
                    ];
                })->values();
            } catch (\Throwable $e) {
                $rows = collect();
            }
        }

        $categories = [];
        foreach (['operating' => 'Operating Activities', 'investing' => 'Investing Activities', 'financing' => 'Financing Activities'] as $key => $label) {
            $subset = $rows->where('category', $key)->values();
            $categories[] = [
                'key' => $key,
                'label' => $label,
                'accounts' => $subset,
                'inflow' => (float) $subset->sum('inflow'),
                'outflow' => (float) $subset->sum('outflow'),
                'net' => (float) $subset->sum('net'),
            ];
        }

        $netChange = (float) $rows->sum('net');

        return Inertia::render('Accounting/CashFlow', [
            'categories' => $categories,
            'summary' => [
                'opening_cash' => $opening,
                'net_change' => $netChange,
                'closing_cash' => $closing,
                'reconciled' => abs(($opening + $netChange) - $closing) < 0.01,
                'cash_accounts' => count($cashAccountIds),
                'line_count' => $rows->count(),
            ],
            'filters' => ['from' => $from ?? '', 'to' => $to ?? ''],
        ]);
    }

    public function exportCashFlow(Request $request)
    {
        $tenantId = $this->tenantId();
        $from = $request->query('from');
        $to = $request->query('to');

        $cashAccountIds = DB::table('chart_of_accounts')
            ->where('tenant_id', $tenantId)
            ->where(function ($q) {
                $q->where('is_cash_account', true)->orWhere('is_bank_account', true);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $rows = collect();
        if (!empty($cashAccountIds)) {
            $query = DB::table('journal_lines as jl')
                ->join('journal_entries as je', 'je.id', '=', 'jl.entry_id')
                ->join('chart_of_accounts as coa', 'coa.id', '=', 'jl.account_id')
                ->where('je.tenant_id', $tenantId)
                ->where('je.status', 'posted')
                ->whereNotIn('jl.account_id', $cashAccountIds)
                ->whereExists(function ($sub) use ($tenantId, $cashAccountIds) {
                    $sub->selectRaw('1')
                        ->from('journal_lines as jlc')
                        ->whereColumn('jlc.entry_id', 'jl.entry_id')
                        ->where('jlc.tenant_id', $tenantId)
                        ->whereIn('jlc.account_id', $cashAccountIds);
                });
            if ($from) $query->whereDate('je.posting_date', '>=', $from);
            if ($to) $query->whereDate('je.posting_date', '<=', $to);

            try {
                $rows = $query->select([
                    'coa.code as account_code', 'coa.name as account_name', 'coa.type as account_type',
                ])->selectRaw('COALESCE(SUM(jl.credit_amount_base - jl.debit_amount_base), 0) as net')
                    ->groupBy('coa.code', 'coa.name', 'coa.type')
                    ->orderBy('coa.code')
                    ->get();
            } catch (\Throwable $e) {
                $rows = collect();
            }
        }

        return response()->streamDownload(function () use ($rows, $from, $to) {
            $out = fopen('php://output', 'w');
            if ($from || $to) fputcsv($out, ['Period', ($from ?: 'Start') . ' to ' . ($to ?: 'Today')]);
            fputcsv($out, ['Category', 'Account Code', 'Account Name', 'Net Cash Flow']);
            foreach ($rows as $r) {
                $type = (string) $r->account_type;
                $category = in_array($type, ['revenue', 'expense'], true)
                    ? 'Operating'
                    : ($type === 'asset' ? 'Investing' : (in_array($type, ['liability', 'equity'], true) ? 'Financing' : 'Operating'));
                fputcsv($out, [$category, $r->account_code, $r->account_name, (float) $r->net]);
            }
            fclose($out);
        }, 'cash_flow_' . now()->format('Ymd_His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportTrialBalance(Request $request)
    {
        $tenantId = $this->tenantId();
        $asOf = $request->query('as_of');
        $accounts = DB::table('chart_of_accounts')
            ->where('tenant_id', $tenantId)
            ->where('allow_direct_posting', true)
            ->select(['id', 'code', 'name', 'type'])
            ->orderBy('code')
            ->get();

        $rows = [];
        foreach ($accounts as $account) {
            $debitQuery = DB::table('journal_lines as jl')
                ->join('journal_entries as je', 'je.id', '=', 'jl.entry_id')
                ->where('je.tenant_id', $tenantId)->where('je.status', 'posted')
                ->where('jl.account_id', $account->id);
            $creditQuery = clone $debitQuery;
            if ($asOf) {
                $debitQuery->whereDate('je.posting_date', '<=', $asOf);
                $creditQuery->whereDate('je.posting_date', '<=', $asOf);
            }
            $debit = (float) $debitQuery->sum('jl.debit_amount_base');
            $credit = (float) $creditQuery->sum('jl.credit_amount_base');
            $balance = $debit - $credit;
            $rows[] = [
                'code' => $account->code, 'name' => $account->name, 'type' => $account->type,
                'debit' => $balance > 0 ? $balance : 0, 'credit' => $balance < 0 ? abs($balance) : 0,
            ];
        }

        return response()->streamDownload(function () use ($rows, $asOf) {
            $out = fopen('php://output', 'w');
            if ($asOf) fputcsv($out, ['As of', $asOf]);
            fputcsv($out, ['Account Code', 'Account Name', 'Type', 'Debit Balance', 'Credit Balance']);
            foreach ($rows as $r) fputcsv($out, [$r['code'], $r['name'], $r['type'], $r['debit'], $r['credit']]);
            fputcsv($out, ['', '', 'Total', array_sum(array_column($rows, 'debit')), array_sum(array_column($rows, 'credit'))]);
            fclose($out);
        }, 'trial_balance_' . now()->format('Ymd_His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportProfitAndLoss(Request $request)
    {
        $tenantId = $this->tenantId();
        $from = $request->query('from');
        $to = $request->query('to');
        $accounts = DB::table('chart_of_accounts')
            ->where('tenant_id', $tenantId)
            ->whereIn('type', ['revenue', 'expense'])
            ->select(['id', 'code', 'name', 'type'])
            ->orderBy('code')
            ->get();

        $rows = [];
        foreach ($accounts as $account) {
            $debitQuery = DB::table('journal_lines as jl')
                ->join('journal_entries as je', 'je.id', '=', 'jl.entry_id')
                ->where('je.tenant_id', $tenantId)->where('je.status', 'posted')
                ->where('jl.account_id', $account->id);
            $creditQuery = clone $debitQuery;
            if ($from) {
                $debitQuery->whereDate('je.posting_date', '>=', $from);
                $creditQuery->whereDate('je.posting_date', '>=', $from);
            }
            if ($to) {
                $debitQuery->whereDate('je.posting_date', '<=', $to);
                $creditQuery->whereDate('je.posting_date', '<=', $to);
            }
            $debit = (float) $debitQuery->sum('jl.debit_amount_base');
            $credit = (float) $creditQuery->sum('jl.credit_amount_base');
            $rows[] = [
                'code' => $account->code, 'name' => $account->name, 'type' => $account->type,
                'turnover' => $account->type === 'revenue' ? ($credit - $debit) : ($debit - $credit),
            ];
        }

        return response()->streamDownload(function () use ($rows, $from, $to) {
            $out = fopen('php://output', 'w');
            if ($from || $to) fputcsv($out, ['Period', ($from ?: 'Start') . ' to ' . ($to ?: 'Today')]);
            fputcsv($out, ['Account Code', 'Account Name', 'Type', 'Turnover']);
            foreach ($rows as $r) fputcsv($out, [$r['code'], $r['name'], $r['type'], $r['turnover']]);
            $revenue = array_sum(array_map(static fn ($r) => $r['type'] === 'revenue' ? $r['turnover'] : 0, $rows));
            $expense = array_sum(array_map(static fn ($r) => $r['type'] === 'expense' ? $r['turnover'] : 0, $rows));
            fputcsv($out, ['', '', 'Total Revenue', $revenue]);
            fputcsv($out, ['', '', 'Total Expenses', $expense]);
            fputcsv($out, ['', '', 'Net Profit', $revenue - $expense]);
            fclose($out);
        }, 'profit_and_loss_' . now()->format('Ymd_His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportBalanceSheet(Request $request)
    {
        $tenantId = $this->tenantId();
        $asOf = $request->query('as_of');
        $accounts = DB::table('chart_of_accounts')
            ->where('tenant_id', $tenantId)
            ->whereIn('type', ['asset', 'liability', 'equity'])
            ->select(['id', 'code', 'name', 'type'])
            ->orderBy('code')
            ->get();

        $rows = [];
        foreach ($accounts as $account) {
            $debitQuery = DB::table('journal_lines as jl')
                ->join('journal_entries as je', 'je.id', '=', 'jl.entry_id')
                ->where('je.tenant_id', $tenantId)->where('je.status', 'posted')
                ->where('jl.account_id', $account->id);
            $creditQuery = clone $debitQuery;
            if ($asOf) {
                $debitQuery->whereDate('je.posting_date', '<=', $asOf);
                $creditQuery->whereDate('je.posting_date', '<=', $asOf);
            }
            $debit = (float) $debitQuery->sum('jl.debit_amount_base');
            $credit = (float) $creditQuery->sum('jl.credit_amount_base');
            $rows[] = ['code' => $account->code, 'name' => $account->name, 'type' => $account->type, 'balance' => $debit - $credit];
        }

        return response()->streamDownload(function () use ($rows, $asOf) {
            $out = fopen('php://output', 'w');
            if ($asOf) fputcsv($out, ['As of', $asOf]);
            fputcsv($out, ['Account Code', 'Account Name', 'Type', 'Balance']);
            foreach ($rows as $r) fputcsv($out, [$r['code'], $r['name'], $r['type'], $r['balance']]);
            foreach (['asset' => 'Total Assets', 'liability' => 'Total Liabilities', 'equity' => 'Total Equity'] as $type => $label) {
                $total = array_sum(array_map(static fn ($r) => $r['type'] === $type ? $r['balance'] : 0, $rows));
                fputcsv($out, ['', '', $label, $total]);
            }
            fclose($out);
        }, 'balance_sheet_' . now()->format('Ymd_His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function createJournalEntry(Request $request): Response
    {
        $tenantId = $this->tenantId();

        $accounts = DB::table('chart_of_accounts')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('allow_direct_posting', true)
            ->select(['id', 'code', 'name', 'type'])
            ->orderBy('code')
            ->get()
            ->map(fn ($a) => [
                'id' => (string) $a->id,
                'code' => $a->code,
                'name' => $a->name,
                'type' => $a->type,
            ])
            ->values();

        $books = DB::table('accounting_books')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->select(['id', 'name', 'code', 'base_currency'])
            ->orderBy('name')
            ->get();

        $periods = DB::table('accounting_periods')
            ->where('tenant_id', $tenantId)
            ->where('status', 'open')
            ->select(['id', 'name', 'start_date', 'end_date'])
            ->orderByDesc('start_date')
            ->get()
            ->map(fn ($p) => [
                'id' => (string) $p->id,
                'name' => $p->name,
                'start_date' => $p->start_date,
                'end_date' => $p->end_date,
            ])
            ->values();

        $suggestedReference = 'JE-' . now()->format('Ymd') . '-'
            . str_pad((string) ((int) DB::table('journal_entries')->where('reference', 'like', 'JE-' . now()->format('Ymd') . '-%')->count() + 1), 4, '0', STR_PAD_LEFT);

        return Inertia::render('Accounting/JournalEntryCreate', [
            'accounts' => $accounts,
            'books' => $books,
            'periods' => $periods,
            'suggested_reference' => $suggestedReference,
            'default_date' => now()->format('Y-m-d'),
        ]);
    }

    public function storeJournalEntry(Request $request)
    {
        $tenantId = $this->tenantId();

        $validated = $request->validate([
            'reference' => ['nullable', 'string', 'max:100'],
            'document_date' => ['required', 'date'],
            'posting_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:2000'],
            'currency' => ['nullable', 'string', 'size:3'],
            'accounting_book_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:draft,posted'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['nullable'],
            'lines.*.debit_amount' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit_amount' => ['nullable', 'numeric', 'min:0'],
            'lines.*.description' => ['nullable', 'string', 'max:500'],
        ]);

        $lines = collect($validated['lines'])
            ->map(function ($line) {
                return [
                    'account_id' => (int) ($line['account_id'] ?? 0),
                    'debit' => round((float) ($line['debit_amount'] ?? 0), 4),
                    'credit' => round((float) ($line['credit_amount'] ?? 0), 4),
                    'description' => trim((string) ($line['description'] ?? '')) ?: null,
                ];
            })
            ->filter(fn ($line) => $line['debit'] > 0 || $line['credit'] > 0)
            ->values();

        if ($lines->count() < 2) {
            return back()->withErrors(['lines' => 'Enter at least two lines with a debit or credit amount.'])->withInput();
        }

        foreach ($lines as $index => $line) {
            if ($line['debit'] > 0 && $line['credit'] > 0) {
                return back()->withErrors(['lines' => 'Line ' . ($index + 1) . ' cannot have both a debit and a credit amount.'])->withInput();
            }
            if ($line['account_id'] <= 0) {
                return back()->withErrors(['lines' => 'Line ' . ($index + 1) . ' must have an account selected.'])->withInput();
            }
        }

        $totalDebit = round((float) $lines->sum('debit'), 4);
        $totalCredit = round((float) $lines->sum('credit'), 4);

        if ($totalDebit <= 0) {
            return back()->withErrors(['lines' => 'The journal entry must have a debit total greater than zero.'])->withInput();
        }
        if (abs($totalDebit - $totalCredit) > 0.0001) {
            return back()->withErrors(['lines' => 'The journal entry is not balanced: total debit ' . number_format($totalDebit, 2) . ' does not equal total credit ' . number_format($totalCredit, 2) . '.'])->withInput();
        }

        $accountIds = $lines->pluck('account_id')->unique()->values();
        $validAccountIds = DB::table('chart_of_accounts')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('allow_direct_posting', true)
            ->whereIn('id', $accountIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (count($validAccountIds) !== $accountIds->count()) {
            return back()->withErrors(['lines' => 'One or more selected accounts are inactive or do not allow direct posting.'])->withInput();
        }

        $bookId = (int) ($validated['accounting_book_id'] ?? 0);
        $book = null;
        if ($bookId > 0) {
            $book = DB::table('accounting_books')->where('tenant_id', $tenantId)->where('id', $bookId)->first();
        }
        if (!$book) {
            $book = DB::table('accounting_books')->where('tenant_id', $tenantId)->where('is_active', true)->orderBy('id')->first();
        }
        if (!$book) {
            $newBookId = DB::table('accounting_books')->insertGetId([
                'tenant_id' => $tenantId,
                'name' => 'Default Accounting Book',
                'code' => 'DEF-' . $tenantId,
                'base_currency' => 'PKR',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $book = DB::table('accounting_books')->where('id', $newBookId)->first();
        }

        $currency = strtoupper((string) ($validated['currency'] ?? $book->base_currency ?? 'PKR'));
        if (strlen($currency) !== 3) $currency = 'PKR';

        $documentDate = Carbon::parse($validated['document_date']);
        $postingDate = !empty($validated['posting_date']) ? Carbon::parse($validated['posting_date']) : $documentDate;

        $periodId = null;
        try {
            $period = DB::table('accounting_periods')
                ->where('tenant_id', $tenantId)
                ->whereDate('start_date', '<=', $postingDate->toDateString())
                ->whereDate('end_date', '>=', $postingDate->toDateString())
                ->orderByDesc('start_date')
                ->first();
            if ($period) {
                if ($period->status === 'closed' && ($validated['status'] ?? 'posted') === 'posted') {
                    return back()->withErrors(['posting_date' => 'The accounting period covering this date is closed. Reopen it or choose another date.'])->withInput();
                }
                $periodId = $period->id;
            }
        } catch (\Throwable $e) {
            $periodId = null;
        }

        $status = in_array(($validated['status'] ?? 'posted'), ['draft', 'posted'], true) ? $validated['status'] : 'posted';

        $reference = trim((string) ($validated['reference'] ?? ''));
        if ($reference === '') {
            $prefix = 'JE-' . $documentDate->format('Ymd') . '-';
            $seq = (int) DB::table('journal_entries')->where('reference', 'like', $prefix . '%')->count() + 1;
            do {
                $reference = $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
                $seq++;
            } while (DB::table('journal_entries')->where('reference', $reference)->exists());
        } elseif (DB::table('journal_entries')->where('reference', $reference)->exists()) {
            return back()->withErrors(['reference' => 'This reference is already used by another journal entry.'])->withInput();
        }

        $userId = $request->user()?->id;
        if (!$userId) {
            $userId = (int) DB::table('users')->orderBy('id')->value('id');
        }
        if (!$userId) {
            return back()->withErrors(['lines' => 'No user is available to record the journal entry.'])->withInput();
        }

        try {
            $entryId = DB::transaction(function () use ($tenantId, $book, $currency, $documentDate, $postingDate, $periodId, $status, $reference, $validated, $userId, $lines) {
                $entryId = DB::table('journal_entries')->insertGetId([
                    'tenant_id' => $tenantId,
                    'accounting_book_id' => $book->id,
                    'fiscal_period_id' => $periodId,
                    'document_date' => $documentDate->toDateTimeString(),
                    'posting_date' => $postingDate->toDateTimeString(),
                    'currency' => $currency,
                    'exchange_rate_to_base' => 1,
                    'reference' => $reference,
                    'description' => $validated['description'] ?? null,
                    'status' => $status,
                    'created_by' => $userId,
                    'posted_at' => $status === 'posted' ? now() : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $now = now();
                $rows = $lines->map(fn ($line) => [
                    'entry_id' => $entryId,
                    'tenant_id' => $tenantId,
                    'account_id' => $line['account_id'],
                    'debit_amount' => $line['debit'],
                    'credit_amount' => $line['credit'],
                    'debit_amount_base' => $line['debit'],
                    'credit_amount_base' => $line['credit'],
                    'currency' => $currency,
                    'exchange_rate' => 1,
                    'description' => $line['description'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                DB::table('journal_lines')->insert($rows);

                return $entryId;
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['lines' => 'Unable to save the journal entry: ' . $e->getMessage()])->withInput();
        }

        return redirect()->route('accounting')
            ->with('success', 'Journal entry ' . $reference . ' ' . ($status === 'posted' ? 'posted' : 'saved as draft') . ' successfully.');
    }

    public function editJournalEntryPage(string $id): Response
    {
        $tenantId = $this->tenantId();

        $entry = DB::table('journal_entries')->where('tenant_id', $tenantId)->where('id', $id)->first();
        abort_if(!$entry, 404);
        if ($entry->status !== 'draft') {
            return redirect()->route('accounting.journal-entries.show', ['id' => $id])
                ->withErrors(['entry' => 'Only draft entries can be edited. Posted entries must be reversed instead.']);
        }

        $accounts = DB::table('chart_of_accounts')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('allow_direct_posting', true)
            ->select(['id', 'code', 'name', 'type'])
            ->orderBy('code')
            ->get()
            ->map(fn ($a) => ['id' => (string) $a->id, 'code' => $a->code, 'name' => $a->name, 'type' => $a->type])
            ->values();

        $books = DB::table('accounting_books')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->select(['id', 'name', 'code', 'base_currency'])
            ->orderBy('name')
            ->get()
            ->map(fn ($b) => ['id' => (string) $b->id, 'name' => $b->name, 'code' => $b->code, 'base_currency' => $b->base_currency])
            ->values();

        $periods = DB::table('accounting_periods')
            ->where('tenant_id', $tenantId)
            ->where('status', 'open')
            ->select(['id', 'name', 'start_date', 'end_date'])
            ->orderByDesc('start_date')
            ->get()
            ->map(fn ($p) => [
                'id' => (string) $p->id,
                'name' => $p->name,
                'start_date' => $p->start_date,
                'end_date' => $p->end_date,
            ])
            ->values();

        $lines = DB::table('journal_lines as jl')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'jl.account_id')
            ->where('jl.tenant_id', $tenantId)
            ->where('jl.entry_id', $id)
            ->select([
                'jl.account_id', 'coa.code as account_code', 'coa.name as account_name',
                'jl.debit_amount_base', 'jl.credit_amount_base', 'jl.description',
            ])
            ->orderBy('jl.id')
            ->get()
            ->map(fn ($l) => [
                'account_id' => (string) $l->account_id,
                'account_name' => $l->account_code . ' — ' . $l->account_name,
                'debit_amount' => (float) $l->debit_amount_base > 0 ? (string) (float) $l->debit_amount_base : '',
                'credit_amount' => (float) $l->credit_amount_base > 0 ? (string) (float) $l->credit_amount_base : '',
                'description' => $l->description,
            ])
            ->values();

        return Inertia::render('Accounting/JournalEntryCreate', [
            'accounts' => $accounts,
            'books' => $books,
            'periods' => $periods,
            'suggested_reference' => $entry->reference,
            'default_date' => Carbon::parse($entry->document_date)->format('Y-m-d'),
            'entry' => [
                'id' => $entry->id,
                'reference' => $entry->reference,
                'document_date' => Carbon::parse($entry->document_date)->format('Y-m-d'),
                'posting_date' => Carbon::parse($entry->posting_date)->format('Y-m-d'),
                'accounting_book_id' => (string) $entry->accounting_book_id,
                'currency' => $entry->currency,
                'status' => $entry->status,
                'description' => $entry->description,
                'lines' => $lines,
            ],
        ]);
    }

    public function updateJournalEntry(Request $request, string $id)
    {
        $tenantId = $this->tenantId();
        $entry = DB::table('journal_entries')->where('tenant_id', $tenantId)->where('id', $id)->first();
        abort_if(!$entry, 404);

        if ($entry->status !== 'draft') {
            return back()->withErrors(['entry' => 'Only draft entries can be edited. Reverse posted entries instead.']);
        }

        $validated = $request->validate([
            'reference' => ['nullable', 'string', 'max:100'],
            'document_date' => ['required', 'date'],
            'posting_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:2000'],
            'currency' => ['nullable', 'string', 'size:3'],
            'accounting_book_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:draft,posted'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['nullable'],
            'lines.*.debit_amount' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit_amount' => ['nullable', 'numeric', 'min:0'],
            'lines.*.description' => ['nullable', 'string', 'max:500'],
        ]);

        $lines = collect($validated['lines'])
            ->map(fn ($line) => [
                'account_id' => (int) ($line['account_id'] ?? 0),
                'debit' => round((float) ($line['debit_amount'] ?? 0), 4),
                'credit' => round((float) ($line['credit_amount'] ?? 0), 4),
                'description' => trim((string) ($line['description'] ?? '')) ?: null,
            ])
            ->filter(fn ($line) => $line['debit'] > 0 || $line['credit'] > 0)
            ->values();

        if ($lines->count() < 2) {
            return back()->withErrors(['lines' => 'Enter at least two lines with a debit or credit amount.'])->withInput();
        }

        foreach ($lines as $index => $line) {
            if ($line['debit'] > 0 && $line['credit'] > 0) {
                return back()->withErrors(['lines' => 'Line ' . ($index + 1) . ' cannot have both a debit and a credit amount.'])->withInput();
            }
            if ($line['account_id'] <= 0) {
                return back()->withErrors(['lines' => 'Line ' . ($index + 1) . ' must have an account selected.'])->withInput();
            }
        }

        $totalDebit = round((float) $lines->sum('debit'), 4);
        $totalCredit = round((float) $lines->sum('credit'), 4);
        if ($totalDebit <= 0) {
            return back()->withErrors(['lines' => 'The journal entry must have a debit total greater than zero.'])->withInput();
        }
        if (abs($totalDebit - $totalCredit) > 0.0001) {
            return back()->withErrors(['lines' => 'The journal entry is not balanced: total debit ' . number_format($totalDebit, 2) . ' does not equal total credit ' . number_format($totalCredit, 2) . '.'])->withInput();
        }

        $accountIds = $lines->pluck('account_id')->unique()->values();
        $validAccountCount = DB::table('chart_of_accounts')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('allow_direct_posting', true)
            ->whereIn('id', $accountIds)
            ->count();
        if ($validAccountCount !== $accountIds->count()) {
            return back()->withErrors(['lines' => 'One or more selected accounts are inactive or do not allow direct posting.'])->withInput();
        }

        $documentDate = Carbon::parse($validated['document_date']);
        $postingDate = !empty($validated['posting_date']) ? Carbon::parse($validated['posting_date']) : $documentDate;
        $status = in_array(($validated['status'] ?? 'draft'), ['draft', 'posted'], true) ? ($validated['status'] ?? 'draft') : 'draft';

        $reference = trim((string) ($validated['reference'] ?? ''));
        if ($reference === '') $reference = $entry->reference;
        $duplicate = DB::table('journal_entries')->where('reference', $reference)->where('id', '!=', $id)->exists();
        if ($duplicate) {
            return back()->withErrors(['reference' => 'This reference is already used by another journal entry.'])->withInput();
        }

        $periodId = null;
        try {
            $period = DB::table('accounting_periods')
                ->where('tenant_id', $tenantId)
                ->whereDate('start_date', '<=', $postingDate->toDateString())
                ->whereDate('end_date', '>=', $postingDate->toDateString())
                ->orderByDesc('start_date')
                ->first();
            if ($period) {
                if ($period->status === 'closed' && $status === 'posted') {
                    return back()->withErrors(['posting_date' => 'The accounting period covering this date is closed. Reopen it or choose another date.'])->withInput();
                }
                $periodId = $period->id;
            }
        } catch (\Throwable $e) {
            $periodId = null;
        }

        $bookId = (int) ($validated['accounting_book_id'] ?? 0) ?: (int) $entry->accounting_book_id;
        $book = DB::table('accounting_books')->where('tenant_id', $tenantId)->where('id', $bookId)->first();
        if (!$book) $book = DB::table('accounting_books')->where('tenant_id', $tenantId)->orderBy('id')->first();

        $currency = strtoupper((string) ($validated['currency'] ?? $book->base_currency ?? 'PKR'));
        if (strlen($currency) !== 3) $currency = 'PKR';

        try {
            DB::transaction(function () use ($tenantId, $id, $entry, $validated, $documentDate, $postingDate, $periodId, $status, $reference, $currency, $book, $lines) {
                DB::table('journal_entries')->where('id', $id)->where('tenant_id', $tenantId)->update([
                    'accounting_book_id' => $book->id,
                    'fiscal_period_id' => $periodId,
                    'document_date' => $documentDate->toDateTimeString(),
                    'posting_date' => $postingDate->toDateTimeString(),
                    'currency' => $currency,
                    'reference' => $reference,
                    'description' => $validated['description'] ?? null,
                    'status' => $status,
                    'posted_at' => $status === 'posted' ? ($entry->posted_at ?: now()) : null,
                    'updated_at' => now(),
                ]);

                DB::table('journal_lines')->where('entry_id', $id)->where('tenant_id', $tenantId)->delete();

                $now = now();
                $rows = $lines->map(fn ($line) => [
                    'entry_id' => $id,
                    'tenant_id' => $tenantId,
                    'account_id' => $line['account_id'],
                    'debit_amount' => $line['debit'],
                    'credit_amount' => $line['credit'],
                    'debit_amount_base' => $line['debit'],
                    'credit_amount_base' => $line['credit'],
                    'currency' => $currency,
                    'exchange_rate' => 1,
                    'description' => $line['description'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();
                DB::table('journal_lines')->insert($rows);
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['lines' => 'Unable to update the journal entry: ' . $e->getMessage()])->withInput();
        }

        return redirect()->route('accounting.journal-entries.show', ['id' => $id])
            ->with('success', 'Journal entry ' . $reference . ' updated successfully.');
    }

    public function journalEntryShow(string $id): Response
    {
        $tenantId = $this->tenantId();

        $entry = DB::table('journal_entries as je')
            ->leftJoin('accounting_books as ab', 'ab.id', '=', 'je.accounting_book_id')
            ->leftJoin('accounting_periods as ap', 'ap.id', '=', 'je.fiscal_period_id')
            ->leftJoin('users as u', 'u.id', '=', 'je.created_by')
            ->leftJoin('users as au', 'au.id', '=', 'je.approved_by')
            ->leftJoin('journal_entries as rev', 'rev.id', '=', 'je.reversal_reference_id')
            ->where('je.tenant_id', $tenantId)
            ->where('je.id', $id)
            ->select([
                'je.id', 'je.reference', 'je.document_date', 'je.posting_date', 'je.status',
                'je.currency', 'je.exchange_rate_to_base', 'je.description', 'je.posted_at',
                'je.created_at', 'je.updated_at', 'je.reversal_reference_id',
                'ab.name as book_name', 'ab.code as book_code',
                'ap.name as period_name',
                'u.name as created_by_name', 'au.name as approved_by_name',
                'rev.reference as reversal_reference',
            ])
            ->first();

        abort_if(!$entry, 404);

        $lines = DB::table('journal_lines as jl')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'jl.account_id')
            ->where('jl.tenant_id', $tenantId)
            ->where('jl.entry_id', $id)
            ->select([
                'jl.id', 'jl.account_id', 'coa.code as account_code', 'coa.name as account_name', 'coa.type as account_type',
                'jl.description', 'jl.debit_amount', 'jl.credit_amount', 'jl.debit_amount_base', 'jl.credit_amount_base',
            ])
            ->orderBy('jl.id')
            ->get()
            ->map(fn ($l) => [
                'id' => $l->id,
                'account_id' => $l->account_id,
                'account_code' => $l->account_code,
                'account_name' => $l->account_name,
                'account_type' => $l->account_type,
                'description' => $l->description,
                'debit' => (float) $l->debit_amount_base,
                'credit' => (float) $l->credit_amount_base,
            ])
            ->values();

        $reverseEntryId = null;
        try {
            $reverseEntryId = DB::table('journal_entries')
                ->where('tenant_id', $tenantId)
                ->where('reversal_reference_id', $id)
                ->value('id');
        } catch (\Throwable $e) {
            $reverseEntryId = null;
        }

        return Inertia::render('Accounting/JournalEntryShow', [
            'entry' => [
                'id' => $entry->id,
                'reference' => $entry->reference,
                'document_date' => $entry->document_date ? Carbon::parse($entry->document_date)->format('Y-m-d') : null,
                'posting_date' => $entry->posting_date ? Carbon::parse($entry->posting_date)->format('Y-m-d') : null,
                'status' => $entry->status,
                'currency' => $entry->currency,
                'exchange_rate' => (float) $entry->exchange_rate_to_base,
                'description' => $entry->description,
                'book_name' => $entry->book_name,
                'book_code' => $entry->book_code,
                'period_name' => $entry->period_name,
                'created_by_name' => $entry->created_by_name,
                'approved_by_name' => $entry->approved_by_name,
                'posted_at' => $entry->posted_at ? Carbon::parse($entry->posted_at)->format('d M Y, H:i') : null,
                'created_at' => $entry->created_at ? Carbon::parse($entry->created_at)->format('d M Y, H:i') : null,
                'reversal_reference' => $entry->reversal_reference,
                'reversal_entry_id' => $reverseEntryId,
            ],
            'lines' => $lines,
            'totals' => [
                'debit' => (float) $lines->sum('debit'),
                'credit' => (float) $lines->sum('credit'),
                'line_count' => $lines->count(),
            ],
        ]);
    }

    public function postJournalEntry(Request $request, string $id)
    {
        $tenantId = $this->tenantId();
        $entry = DB::table('journal_entries')->where('tenant_id', $tenantId)->where('id', $id)->first();
        abort_if(!$entry, 404);

        if (!in_array($entry->status, ['draft', 'submitted'], true)) {
            return back()->withErrors(['entry' => 'Only draft or submitted entries can be posted.']);
        }

        $debit = (float) DB::table('journal_lines')->where('entry_id', $id)->sum('debit_amount_base');
        $credit = (float) DB::table('journal_lines')->where('entry_id', $id)->sum('credit_amount_base');
        if (abs($debit - $credit) > 0.0001 || $debit <= 0) {
            return back()->withErrors(['entry' => 'This entry is not balanced and cannot be posted.']);
        }

        $userId = $request->user()?->id ?: (int) DB::table('users')->orderBy('id')->value('id');

        if ($entry->fiscal_period_id) {
            $periodStatus = DB::table('accounting_periods')->where('id', $entry->fiscal_period_id)->value('status');
            if ($periodStatus === 'closed') {
                return back()->withErrors(['entry' => 'The accounting period for this entry is closed. Reopen it before posting.']);
            }
        }

        DB::table('journal_entries')->where('id', $id)->where('tenant_id', $tenantId)->update([
            'status' => 'posted',
            'posted_at' => now(),
            'approved_by' => $userId ?: null,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Journal entry ' . $entry->reference . ' posted successfully.');
    }

    public function reverseJournalEntry(Request $request, string $id)
    {
        $tenantId = $this->tenantId();
        $entry = DB::table('journal_entries')->where('tenant_id', $tenantId)->where('id', $id)->first();
        abort_if(!$entry, 404);

        if ($entry->status !== 'posted') {
            return back()->withErrors(['entry' => 'Only posted entries can be reversed.']);
        }

        $alreadyReversed = DB::table('journal_entries')
            ->where('tenant_id', $tenantId)
            ->where('reversal_reference_id', $id)
            ->exists();
        if ($alreadyReversed) {
            return back()->withErrors(['entry' => 'This entry has already been reversed.']);
        }

        $lines = DB::table('journal_lines')->where('tenant_id', $tenantId)->where('entry_id', $id)->orderBy('id')->get();
        if ($lines->isEmpty()) {
            return back()->withErrors(['entry' => 'This entry has no lines to reverse.']);
        }

        $userId = $request->user()?->id ?: (int) DB::table('users')->orderBy('id')->value('id');
        if (!$userId) {
            return back()->withErrors(['entry' => 'No user is available to record the reversal.']);
        }

        $reference = 'REV-' . $entry->reference;
        $suffix = 1;
        while (DB::table('journal_entries')->where('reference', $reference)->exists()) {
            $suffix++;
            $reference = 'REV-' . $entry->reference . '-' . $suffix;
        }

        $reverseId = DB::transaction(function () use ($tenantId, $entry, $reference, $lines, $userId) {
            $reversalPeriodId = null;
            try {
                $reversalPeriodId = DB::table('accounting_periods')
                    ->where('tenant_id', $tenantId)
                    ->where('status', 'open')
                    ->whereDate('start_date', '<=', now()->toDateString())
                    ->whereDate('end_date', '>=', now()->toDateString())
                    ->orderByDesc('start_date')
                    ->value('id');
            } catch (\Throwable $e) {
                $reversalPeriodId = null;
            }

            $reverseId = DB::table('journal_entries')->insertGetId([
                'tenant_id' => $tenantId,
                'accounting_book_id' => $entry->accounting_book_id,
                'fiscal_period_id' => $reversalPeriodId ?: $entry->fiscal_period_id,
                'document_date' => now()->toDateTimeString(),
                'posting_date' => now()->toDateTimeString(),
                'currency' => $entry->currency,
                'exchange_rate_to_base' => $entry->exchange_rate_to_base,
                'reference' => $reference,
                'description' => 'Reversal of ' . $entry->reference . ($entry->description ? ' — ' . $entry->description : ''),
                'status' => 'posted',
                'reversal_reference_id' => $entry->id,
                'created_by' => $userId,
                'posted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $now = now();
            $rows = $lines->map(fn ($l) => [
                'entry_id' => $reverseId,
                'tenant_id' => $tenantId,
                'account_id' => $l->account_id,
                'debit_amount' => $l->credit_amount,
                'credit_amount' => $l->debit_amount,
                'debit_amount_base' => $l->credit_amount_base,
                'credit_amount_base' => $l->debit_amount_base,
                'currency' => $l->currency,
                'exchange_rate' => $l->exchange_rate,
                'description' => $l->description,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();
            DB::table('journal_lines')->insert($rows);

            DB::table('journal_entries')->where('id', $entry->id)->update([
                'status' => 'reversed',
                'updated_at' => now(),
            ]);

            return $reverseId;
        });

        return redirect()->route('accounting.journal-entries.show', ['id' => $reverseId])
            ->with('success', 'Reversal entry ' . $reference . ' posted successfully.');
    }

    public function deleteJournalEntry(string $id)
    {
        $tenantId = $this->tenantId();
        $entry = DB::table('journal_entries')->where('tenant_id', $tenantId)->where('id', $id)->first();
        abort_if(!$entry, 404);

        if ($entry->status !== 'draft') {
            return back()->withErrors(['entry' => 'Only draft entries can be deleted. Reverse posted entries instead.']);
        }

        $hasReversal = DB::table('journal_entries')
            ->where('tenant_id', $tenantId)
            ->where('reversal_reference_id', $id)
            ->exists();
        if ($hasReversal) {
            return back()->withErrors(['entry' => 'This draft has a linked reversal and cannot be deleted.']);
        }

        DB::transaction(function () use ($id, $tenantId) {
            DB::table('journal_lines')->where('entry_id', $id)->where('tenant_id', $tenantId)->delete();
            DB::table('journal_entries')->where('id', $id)->where('tenant_id', $tenantId)->delete();
        });

        return redirect()->route('accounting')->with('success', 'Draft journal entry deleted.');
    }

    public function exportJournalEntries(Request $request)
    {
        $tenantId = $this->tenantId();
        $status = (string) $request->query('status', '');
        $from = $request->query('from');
        $to = $request->query('to');

        $query = DB::table('journal_entries as je')
            ->leftJoin('accounting_books as ab', 'ab.id', '=', 'je.accounting_book_id')
            ->where('je.tenant_id', $tenantId)
            ->select([
                'je.reference', 'je.document_date', 'je.posting_date', 'je.status',
                'je.description', 'ab.name as book_name',
            ]);
        if (in_array($status, ['draft', 'submitted', 'posted', 'reversed'], true)) $query->where('je.status', $status);
        if ($from) $query->whereDate('je.document_date', '>=', $from);
        if ($to) $query->whereDate('je.document_date', '<=', $to);

        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('je.reference', 'ilike', '%' . $q . '%')
                    ->orWhere('je.description', 'ilike', '%' . $q . '%');
            });
        }

        $rows = $query->orderByDesc('je.document_date')->orderByDesc('je.id')->limit(20000)->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Reference', 'Document Date', 'Posting Date', 'Status', 'Book', 'Description']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->reference,
                    $r->document_date ? Carbon::parse($r->document_date)->format('Y-m-d') : '',
                    $r->posting_date ? Carbon::parse($r->posting_date)->format('Y-m-d') : '',
                    $r->status,
                    $r->book_name,
                    $r->description,
                ]);
            }
            fclose($out);
        }, 'journal_entries_' . now()->format('Ymd_His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    // ============ Accounting — Fiscal Periods ============

    public function accountingPeriods(Request $request): Response
    {
        $tenantId = $this->tenantId();

        $periods = DB::table('accounting_periods as ap')
            ->leftJoin('financial_years as fy', 'fy.id', '=', 'ap.financial_year_id')
            ->where('ap.tenant_id', $tenantId)
            ->select([
                'ap.id', 'ap.name', 'ap.start_date', 'ap.end_date', 'ap.status',
                'ap.financial_year_id', 'fy.name as financial_year_name',
            ])
            ->orderByDesc('ap.start_date')
            ->get()
            ->map(function ($p) use ($tenantId) {
                $base = DB::table('journal_entries')
                    ->where('tenant_id', $tenantId)
                    ->whereDate('posting_date', '>=', $p->start_date)
                    ->whereDate('posting_date', '<=', $p->end_date);

                $entryCount = (clone $base)->count();
                $draftCount = (clone $base)->whereIn('status', ['draft', 'submitted'])->count();
                $postedCount = (clone $base)->where('status', 'posted')->count();

                $totals = DB::table('journal_lines as jl')
                    ->join('journal_entries as je', 'je.id', '=', 'jl.entry_id')
                    ->where('je.tenant_id', $tenantId)
                    ->where('je.status', 'posted')
                    ->whereDate('je.posting_date', '>=', $p->start_date)
                    ->whereDate('je.posting_date', '<=', $p->end_date)
                    ->selectRaw('COALESCE(SUM(jl.debit_amount_base), 0) as debit')
                    ->selectRaw('COALESCE(SUM(jl.credit_amount_base), 0) as credit')
                    ->first();

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'financial_year_name' => $p->financial_year_name,
                    'start_date' => $p->start_date ? Carbon::parse($p->start_date)->format('Y-m-d') : null,
                    'end_date' => $p->end_date ? Carbon::parse($p->end_date)->format('Y-m-d') : null,
                    'status' => $p->status,
                    'entry_count' => $entryCount,
                    'draft_count' => $draftCount,
                    'posted_count' => $postedCount,
                    'debit_total' => (float) ($totals->debit ?? 0),
                    'credit_total' => (float) ($totals->credit ?? 0),
                ];
            })
            ->values();

        $years = DB::table('financial_years')
            ->where('tenant_id', $tenantId)
            ->select(['id', 'name', 'start_date', 'end_date', 'is_closed'])
            ->orderByDesc('start_date')
            ->get();

        return Inertia::render('Accounting/AccountingPeriods', [
            'periods' => $periods,
            'financial_years' => $years,
            'summary' => [
                'total' => $periods->count(),
                'open' => $periods->where('status', 'open')->count(),
                'closed' => $periods->where('status', 'closed')->count(),
                'draft_entries' => $periods->sum('draft_count'),
            ],
        ]);
    }

    public function storeAccountingPeriod(Request $request)
    {
        $tenantId = $this->tenantId();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'financial_year_id' => ['nullable', 'integer'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $start = Carbon::parse($validated['start_date'])->startOfDay();
        $end = Carbon::parse($validated['end_date'])->startOfDay();

        $overlap = DB::table('accounting_periods')
            ->where('tenant_id', $tenantId)
            ->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $start->toDateString())
            ->exists();
        if ($overlap) {
            return back()->withErrors(['start_date' => 'This period overlaps an existing accounting period.'])->withInput();
        }

        $yearId = (int) ($validated['financial_year_id'] ?? 0);
        if ($yearId > 0) {
            $year = DB::table('financial_years')->where('tenant_id', $tenantId)->where('id', $yearId)->first();
            if (!$year) {
                return back()->withErrors(['financial_year_id' => 'Selected financial year was not found.'])->withInput();
            }
        } else {
            $year = DB::table('financial_years')
                ->where('tenant_id', $tenantId)
                ->whereDate('start_date', '<=', $start->toDateString())
                ->whereDate('end_date', '>=', $end->toDateString())
                ->orderByDesc('start_date')
                ->first();
            if (!$year) {
                $yearId = DB::table('financial_years')->insertGetId([
                    'tenant_id' => $tenantId,
                    'name' => 'FY ' . $start->format('Y'),
                    'start_date' => $start->copy()->startOfYear()->toDateString(),
                    'end_date' => $start->copy()->endOfYear()->toDateString(),
                    'is_closed' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $yearId = $year->id;
            }
        }

        DB::table('accounting_periods')->insert([
            'tenant_id' => $tenantId,
            'financial_year_id' => $yearId,
            'name' => $validated['name'],
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('accounting.periods')->with('success', 'Accounting period created successfully.');
    }

    public function toggleAccountingPeriod(Request $request, string $id)
    {
        $tenantId = $this->tenantId();
        $period = DB::table('accounting_periods')->where('tenant_id', $tenantId)->where('id', $id)->first();
        abort_if(!$period, 404);

        if ($period->status === 'open') {
            $unposted = DB::table('journal_entries')
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['draft', 'submitted'])
                ->whereDate('posting_date', '>=', $period->start_date)
                ->whereDate('posting_date', '<=', $period->end_date)
                ->count();

            if ($unposted > 0) {
                return back()->withErrors(['period' => "This period has {$unposted} unposted journal entr" . ($unposted === 1 ? 'y' : 'ies') . '. Post or delete them before closing the period.']);
            }
        }

        DB::table('accounting_periods')->where('id', $id)->where('tenant_id', $tenantId)->update([
            'status' => $period->status === 'open' ? 'closed' : 'open',
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Accounting period ' . ($period->status === 'open' ? 'closed' : 'reopened') . ' successfully.');
    }

    public function createAccountPage(Request $request): Response
    {
        $tenantId = $this->tenantId();

        $parents = DB::table('chart_of_accounts')
            ->where('tenant_id', $tenantId)
            ->select(['id', 'code', 'name', 'type'])
            ->orderBy('code')
            ->get();

        $groups = DB::table('account_groups')
            ->where('tenant_id', $tenantId)
            ->select(['id', 'code', 'name', 'type'])
            ->orderBy('code')
            ->get();

        $prefill = null;
        $parentId = $request->query('parent_id');
        if ($parentId) {
            $parent = DB::table('chart_of_accounts')
                ->where('tenant_id', $tenantId)
                ->where('id', $parentId)
                ->first();
            if ($parent) {
                $prefill = [
                    'code' => '',
                    'name' => '',
                    'type' => $parent->type ?? 'asset',
                    'account_group_id' => $parent->account_group_id,
                    'parent_id' => $parent->id,
                    'allow_direct_posting' => true,
                    'is_cash_account' => false,
                    'is_bank_account' => false,
                    'is_active' => true,
                ];
            }
        }

        return Inertia::render('Accounting/AccountCreate', [
            'parents' => $parents,
            'groups' => $groups,
            'prefill' => $prefill,
        ]);
    }

    // ============ Accounting — Chart of Accounts CSV Import ============

    public function importAccountsPage(): Response
    {
        $tenantId = $this->tenantId();

        $groups = DB::table('account_groups')
            ->where('tenant_id', $tenantId)
            ->select(['id', 'code', 'name'])
            ->orderBy('code')
            ->get();

        return Inertia::render('Accounting/AccountImport', [
            'groups' => $groups,
            'preview' => null,
            'summary' => null,
            'import_token' => null,
        ]);
    }

    public function importAccountTemplate()
    {
        $rows = [
            ['code', 'name', 'type', 'parent_code', 'group_code', 'allow_direct_posting', 'is_cash_account', 'is_bank_account', 'is_active'],
            ['1010', 'Cash on Hand', 'asset', '', '', 'yes', 'yes', 'no', 'yes'],
            ['1020', 'Bank — Current Account', 'asset', '', '', 'yes', 'no', 'yes', 'yes'],
            ['4010', 'Sales Revenue', 'revenue', '', '', 'yes', 'no', 'no', 'yes'],
            ['5010', 'Cost of Goods Sold', 'expense', '', '', 'yes', 'no', 'no', 'yes'],
        ];

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            foreach ($rows as $row) fputcsv($out, $row);
            fclose($out);
        }, 'chart_of_accounts_template.csv', ['Content-Type' => 'text/csv']);
    }

    private function importBool(?string $value, bool $default = false): bool
    {
        $v = strtolower(trim((string) $value));
        if ($v === '') return $default;
        return in_array($v, ['1', 'yes', 'true', 'y', 'active', 'on'], true);
    }

    public function previewAccountImport(Request $request)
    {
        $tenantId = $this->tenantId();

        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('file');
        if (!in_array(strtolower($file->getClientOriginalExtension()), ['csv', 'txt'], true)) {
            return back()->withErrors(['file' => 'Please upload a CSV file (.csv or .txt).']);
        }
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return back()->withErrors(['file' => 'Unable to read the uploaded file.']);
        }

        $header = null;
        $raw = [];
        $line = 0;
        while (($cols = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            $line++;
            if ($header === null) {
                $header = array_map(static fn ($h) => strtolower(trim((string) $h)), $cols);
                continue;
            }
            if (count(array_filter($cols, static fn ($c) => trim((string) $c) !== '')) === 0) continue;
            $assoc = [];
            foreach ($header as $i => $key) {
                if ($key === '') continue;
                $assoc[$key] = trim((string) ($cols[$i] ?? ''));
            }
            $raw[] = ['row' => $line, 'data' => $assoc];
        }
        fclose($handle);

        if ($header === null || !in_array('code', $header, true) || !in_array('name', $header, true)) {
            return back()->withErrors(['file' => 'Invalid CSV: header must include at least "code" and "name" columns.']);
        }

        $existingCodes = DB::table('chart_of_accounts')
            ->pluck('code')
            ->map(static fn ($c) => strtoupper(trim((string) $c)))
            ->flip()
            ->all();

        $fileCodes = [];
        foreach ($raw as $r) {
            $code = strtoupper(trim((string) ($r['data']['code'] ?? '')));
            if ($code !== '') $fileCodes[$code] = true;
        }

        $groupCodes = DB::table('account_groups')
            ->where('tenant_id', $tenantId)
            ->pluck('code')
            ->map(static fn ($c) => strtoupper(trim((string) $c)))
            ->flip()
            ->all();

        $seen = [];
        $preview = [];
        $validRows = [];
        foreach ($raw as $r) {
            $d = $r['data'];
            $errors = [];

            $code = trim((string) ($d['code'] ?? ''));
            $name = trim((string) ($d['name'] ?? ''));
            $type = strtolower(trim((string) ($d['type'] ?? '')));
            $parentCode = trim((string) ($d['parent_code'] ?? ''));
            $groupCode = trim((string) ($d['group_code'] ?? ''));

            if ($code === '') $errors[] = 'Code is required.';
            if ($name === '') $errors[] = 'Name is required.';
            if ($type === '') $errors[] = 'Type is required.';
            elseif (!in_array($type, ['asset', 'liability', 'equity', 'revenue', 'expense'], true)) $errors[] = 'Type must be asset, liability, equity, revenue, or expense.';

            $codeKey = strtoupper($code);
            if ($codeKey !== '') {
                if (isset($seen[$codeKey])) $errors[] = 'Duplicate code within the CSV.';
                $seen[$codeKey] = true;
                if (isset($existingCodes[$codeKey])) $errors[] = 'Code already exists in the chart of accounts.';
            }

            if ($parentCode !== '') {
                $parentKey = strtoupper($parentCode);
                if (!isset($existingCodes[$parentKey]) && !isset($fileCodes[$parentKey])) {
                    $errors[] = "Parent code '{$parentCode}' was not found in the CSV or existing accounts.";
                }
            }

            if ($groupCode !== '') {
                $groupKey = strtoupper($groupCode);
                if (!isset($groupCodes[$groupKey])) {
                    $errors[] = "Group code '{$groupCode}' was not found.";
                }
            }

            $row = [
                'row' => $r['row'],
                'code' => $code,
                'name' => $name,
                'type' => $type,
                'parent_code' => $parentCode,
                'group_code' => $groupCode,
                'allow_direct_posting' => $this->importBool($d['allow_direct_posting'] ?? null, true),
                'is_cash_account' => $this->importBool($d['is_cash_account'] ?? null, false),
                'is_bank_account' => $this->importBool($d['is_bank_account'] ?? null, false),
                'is_active' => $this->importBool($d['is_active'] ?? null, true),
                'valid' => count($errors) === 0,
                'errors' => $errors,
            ];

            $preview[] = $row;
            if ($row['valid']) $validRows[] = $row;
        }

        $token = bin2hex(random_bytes(16));
        $request->session()->put('coa_import_' . $token, [
            'tenant_id' => $tenantId,
            'created_at' => time(),
            'rows' => $validRows,
        ]);

        $summary = [
            'total' => count($preview),
            'valid' => count($validRows),
            'invalid' => count($preview) - count($validRows),
        ];

        return Inertia::render('Accounting/AccountImport', [
            'groups' => DB::table('account_groups')->where('tenant_id', $tenantId)->select(['id', 'code', 'name'])->orderBy('code')->get(),
            'preview' => $preview,
            'summary' => $summary,
            'import_token' => $token,
        ]);
    }

    public function commitAccountImport(Request $request)
    {
        $tenantId = $this->tenantId();
        $token = (string) $request->input('token', '');

        $payload = $token !== '' ? $request->session()->pull('coa_import_' . $token) : null;
        if (!$payload || ($payload['tenant_id'] ?? null) !== $tenantId) {
            return redirect()->route('accounting.chart-of-accounts.import')->withErrors(['file' => 'Import session expired or invalid. Please upload and preview the file again.']);
        }
        if ((time() - (int) ($payload['created_at'] ?? 0)) > 1800) {
            return redirect()->route('accounting.chart-of-accounts.import')->withErrors(['file' => 'Import session expired. Please upload and preview the file again.']);
        }

        $rows = $payload['rows'] ?? [];
        $inserted = 0;
        $skipped = 0;

        $pending = $rows;
        $passes = 0;
        while (!empty($pending) && $passes < 5) {
            $next = [];
            foreach ($pending as $row) {
                $parentId = null;
                if (!empty($row['parent_code'])) {
                    $parentId = DB::table('chart_of_accounts')
                        ->where('tenant_id', $tenantId)
                        ->where('code', $row['parent_code'])
                        ->value('id');
                    if (!$parentId) {
                        $next[] = $row;
                        continue;
                    }
                }

                if (DB::table('chart_of_accounts')->where('code', $row['code'])->exists()) {
                    $skipped++;
                    continue;
                }

                $groupId = null;
                if (!empty($row['group_code'])) {
                    $groupId = DB::table('account_groups')
                        ->where('tenant_id', $tenantId)
                        ->where('code', $row['group_code'])
                        ->value('id');
                }

                try {
                    DB::table('chart_of_accounts')->insert([
                        'tenant_id' => $tenantId,
                        'code' => $row['code'],
                        'name' => $row['name'],
                        'type' => $row['type'],
                        'account_group_id' => $groupId,
                        'parent_id' => $parentId,
                        'allow_direct_posting' => (bool) ($row['allow_direct_posting'] ?? true),
                        'is_cash_account' => (bool) ($row['is_cash_account'] ?? false),
                        'is_bank_account' => (bool) ($row['is_bank_account'] ?? false),
                        'is_active' => (bool) ($row['is_active'] ?? true),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $inserted++;
                } catch (\Throwable $e) {
                    $skipped++;
                }
            }

            if (count($next) === count($pending)) {
                // Unresolved parents: insert with no parent rather than losing the row.
                foreach ($next as $row) {
                    if (DB::table('chart_of_accounts')->where('code', $row['code'])->exists()) {
                        $skipped++;
                        continue;
                    }
                    try {
                        DB::table('chart_of_accounts')->insert([
                            'tenant_id' => $tenantId,
                            'code' => $row['code'],
                            'name' => $row['name'],
                            'type' => $row['type'],
                            'account_group_id' => null,
                            'parent_id' => null,
                            'allow_direct_posting' => (bool) ($row['allow_direct_posting'] ?? true),
                            'is_cash_account' => (bool) ($row['is_cash_account'] ?? false),
                            'is_bank_account' => (bool) ($row['is_bank_account'] ?? false),
                            'is_active' => (bool) ($row['is_active'] ?? true),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $inserted++;
                    } catch (\Throwable $e) {
                        $skipped++;
                    }
                }
                break;
            }

            $pending = $next;
            $passes++;
        }

        return redirect()->route('accounting.chart-of-accounts')
            ->with('success', "Imported {$inserted} account(s)." . ($skipped > 0 ? " Skipped {$skipped}." : ''));
    }

    // ============ Accounting — Account Groups ============

    public function accountGroups(Request $request): Response
    {
        $tenantId = $this->tenantId();
        $q = trim((string) $request->query('q', ''));
        $type = $request->query('type');

        $balanceSub = DB::table('chart_of_accounts as c')
            ->join('journal_lines as jl', 'jl.account_id', '=', 'c.id')
            ->join('journal_entries as je', 'je.id', '=', 'jl.entry_id')
            ->selectRaw('COALESCE(SUM(jl.debit_amount_base - jl.credit_amount_base), 0)')
            ->whereColumn('c.account_group_id', 'ag.id')
            ->where('c.tenant_id', $tenantId)
            ->where('jl.tenant_id', $tenantId)
            ->where('je.status', 'posted');

        $query = DB::table('account_groups as ag')
            ->where('ag.tenant_id', $tenantId)
            ->select([
                'ag.id', 'ag.code', 'ag.name', 'ag.type', 'ag.created_at',
                DB::raw('(SELECT COUNT(*) FROM chart_of_accounts c WHERE c.account_group_id = ag.id AND c.tenant_id = ag.tenant_id) as accounts_count'),
            ])
            ->selectSub($balanceSub, 'posted_balance')
            ->orderBy('ag.code');

        if ($q !== '') {
            $query->where(function ($n) use ($q) {
                $n->where('ag.code', 'ilike', "%{$q}%")->orWhere('ag.name', 'ilike', "%{$q}%");
            });
        }
        if ($type && $type !== 'all') $query->where('ag.type', $type);

        $payload = $this->paginate($query);
        $payload['data'] = array_map(function (array $row) {
            $row['accounts_count'] = (int) ($row['accounts_count'] ?? 0);
            $row['posted_balance'] = (float) ($row['posted_balance'] ?? 0);
            return $row;
        }, $payload['data']);

        $byType = DB::table('account_groups')->where('tenant_id', $tenantId)
            ->selectRaw('type, COUNT(*) as count')->groupBy('type')->pluck('count', 'type')->toArray();
        $typeCounts = [];
        foreach ($byType as $k => $v) $typeCounts[$k] = (int) $v;

        return Inertia::render('Accounting/AccountGroups', [
            'groups' => $payload,
            'type_counts' => $typeCounts,
            'summary' => [
                'total' => (int) DB::table('account_groups')->where('tenant_id', $tenantId)->count(),
            ],
            'filters' => ['q' => $q, 'type' => $type ?? 'all'],
        ]);
    }

    public function storeAccountGroup(Request $request)
    {
        $tenantId = $this->tenantId();
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', \Illuminate\Validation\Rule::unique('account_groups', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:asset,liability,equity,revenue,expense'],
        ]);

        DB::table('account_groups')->insert([
            'tenant_id' => $tenantId,
            'code' => $validated['code'],
            'name' => $validated['name'],
            'type' => $validated['type'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('accounting.account-groups')->with('success', 'Account group created successfully.');
    }

    public function updateAccountGroup(Request $request, string $id)
    {
        $tenantId = $this->tenantId();
        $existing = DB::table('account_groups')->where('tenant_id', $tenantId)->where('id', $id)->first();
        abort_if(!$existing, 404);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', \Illuminate\Validation\Rule::unique('account_groups', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:asset,liability,equity,revenue,expense'],
        ]);

        DB::table('account_groups')->where('tenant_id', $tenantId)->where('id', $id)->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'type' => $validated['type'],
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Account group updated successfully.');
    }

    public function deleteAccountGroup(Request $request, string $id)
    {
        $tenantId = $this->tenantId();
        $group = DB::table('account_groups')->where('tenant_id', $tenantId)->where('id', $id)->first();
        abort_if(!$group, 404);

        $accounts = (int) DB::table('chart_of_accounts')->where('tenant_id', $tenantId)->where('account_group_id', $id)->count();
        if ($accounts > 0) {
            $msg = 'Cannot delete group: it is assigned to ' . $accounts . ' account(s). Reassign them first.';
            return $request->expectsJson() ? response()->json(['success' => false, 'message' => $msg], 422) : back()->withErrors(['delete' => $msg]);
        }

        DB::table('account_groups')->where('tenant_id', $tenantId)->where('id', $id)->delete();
        return $request->expectsJson() ? response()->json(['success' => true]) : back()->with('success', 'Account group deleted.');
    }

    private function chartOfAccountValidationRules(Request $request, int $tenantId, $ignoreId = null): array
    {
        $codeRule = \Illuminate\Validation\Rule::unique('chart_of_accounts', 'code');
        if ($ignoreId) {
            $codeRule = $codeRule->ignore($ignoreId);
        }

        return [
            'code' => ['required', 'string', 'max:50', $codeRule],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:asset,liability,equity,revenue,expense'],
            'account_group_id' => ['nullable', 'integer'],
            'parent_id' => ['nullable', 'integer'],
            'allow_direct_posting' => ['nullable', 'boolean'],
            'is_cash_account' => ['nullable', 'boolean'],
            'is_bank_account' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function storeAccount(Request $request)
    {
        $tenantId = $this->tenantId();
        $validated = $request->validate($this->chartOfAccountValidationRules($request, $tenantId));

        $parentId = $validated['parent_id'] ?? null;
        if ($parentId) {
            $parent = DB::table('chart_of_accounts')->where('tenant_id', $tenantId)->where('id', $parentId)->first();
            if (!$parent) {
                return back()->withErrors(['parent_id' => 'Selected parent account is invalid.'])->withInput();
            }
            if (($parent->type ?? null) && $parent->type !== $validated['type']) {
                return back()->withErrors(['parent_id' => 'Parent account type must match the account type.'])->withInput();
            }
        }

        $groupId = $validated['account_group_id'] ?? null;
        if ($groupId) {
            $group = DB::table('account_groups')->where('tenant_id', $tenantId)->where('id', $groupId)->first();
            if (!$group) {
                return back()->withErrors(['account_group_id' => 'Selected account group is invalid.'])->withInput();
            }
        }

        try {
            DB::table('chart_of_accounts')->insert([
                'tenant_id' => $tenantId,
                'code' => $validated['code'],
                'name' => $validated['name'],
                'type' => $validated['type'],
                'account_group_id' => $groupId,
                'parent_id' => $parentId,
                'is_bank_account' => (bool) ($validated['is_bank_account'] ?? false),
                'is_cash_account' => (bool) ($validated['is_cash_account'] ?? false),
                'allow_direct_posting' => (bool) ($validated['allow_direct_posting'] ?? true),
                'is_active' => (bool) ($validated['is_active'] ?? true),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            return back()->withErrors(['code' => 'Unable to create account. Account code may already exist.'])->withInput();
        }

        return redirect()->route('accounting.chart-of-accounts')->with('success', 'Account created successfully.');
    }

    public function editAccountPage(string $id): Response
    {
        $tenantId = $this->tenantId();
        $account = DB::table('chart_of_accounts')->where('tenant_id', $tenantId)->where('id', $id)->first();
        abort_if(!$account, 404);

        $parents = DB::table('chart_of_accounts')
            ->where('tenant_id', $tenantId)
            ->where('id', '!=', $id)
            ->select(['id', 'code', 'name', 'type'])
            ->orderBy('code')
            ->get();

        $groups = DB::table('account_groups')
            ->where('tenant_id', $tenantId)
            ->select(['id', 'code', 'name', 'type'])
            ->orderBy('code')
            ->get();

        return Inertia::render('Accounting/AccountEdit', [
            'account' => [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'account_group_id' => $account->account_group_id,
                'parent_id' => $account->parent_id,
                'allow_direct_posting' => (bool) $account->allow_direct_posting,
                'is_cash_account' => (bool) $account->is_cash_account,
                'is_bank_account' => (bool) $account->is_bank_account,
                'is_active' => (bool) ($account->is_active ?? true),
            ],
            'parents' => $parents,
            'groups' => $groups,
        ]);
    }

    public function updateAccount(Request $request, string $id)
    {
        $tenantId = $this->tenantId();
        $existing = DB::table('chart_of_accounts')->where('tenant_id', $tenantId)->where('id', $id)->first();
        abort_if(!$existing, 404);

        $validated = $request->validate($this->chartOfAccountValidationRules($request, $tenantId, $id));

        $parentId = $validated['parent_id'] ?? null;
        if ($parentId) {
            if ((string) $parentId === (string) $id) {
                return back()->withErrors(['parent_id' => 'Account cannot be its own parent.'])->withInput();
            }
            $parent = DB::table('chart_of_accounts')->where('tenant_id', $tenantId)->where('id', $parentId)->first();
            if (!$parent) {
                return back()->withErrors(['parent_id' => 'Selected parent account is invalid.'])->withInput();
            }
            if (($parent->type ?? null) && $parent->type !== $validated['type']) {
                return back()->withErrors(['parent_id' => 'Parent account type must match the account type.'])->withInput();
            }
            // prevent cycles: selected parent must not be a descendant of this account
            $cursor = $parentId;
            $guard = 0;
            while ($cursor && $guard < 50) {
                if ((string) $cursor === (string) $id) {
                    return back()->withErrors(['parent_id' => 'Invalid parent: cannot set a descendant account as parent (cycle).'])->withInput();
                }
                $cursor = DB::table('chart_of_accounts')->where('tenant_id', $tenantId)->where('id', $cursor)->value('parent_id');
                $guard++;
            }
        }

        $groupId = $validated['account_group_id'] ?? null;
        if ($groupId) {
            $group = DB::table('account_groups')->where('tenant_id', $tenantId)->where('id', $groupId)->first();
            if (!$group) {
                return back()->withErrors(['account_group_id' => 'Selected account group is invalid.'])->withInput();
            }
        }

        try {
            DB::table('chart_of_accounts')
                ->where('tenant_id', $tenantId)
                ->where('id', $id)
                ->update([
                    'code' => $validated['code'],
                    'name' => $validated['name'],
                    'type' => $validated['type'],
                    'account_group_id' => $groupId,
                    'parent_id' => $parentId,
                    'is_bank_account' => (bool) ($validated['is_bank_account'] ?? false),
                    'is_cash_account' => (bool) ($validated['is_cash_account'] ?? false),
                    'allow_direct_posting' => (bool) ($validated['allow_direct_posting'] ?? true),
                    'is_active' => (bool) ($validated['is_active'] ?? $existing->is_active ?? true),
                    'updated_at' => now(),
                ]);
        } catch (\Throwable $e) {
            return back()->withErrors(['code' => 'Unable to update account. Account code may already exist.'])->withInput();
        }

        return redirect()->route('accounting.chart-of-accounts')->with('success', 'Account updated successfully.');
    }

    public function toggleAccount(Request $request, string $id)
    {
        $tenantId = $this->tenantId();
        $account = DB::table('chart_of_accounts')->where('tenant_id', $tenantId)->where('id', $id)->first();
        abort_if(!$account, 404);

        $new = !(bool) ($account->is_active ?? true);
        DB::table('chart_of_accounts')->where('tenant_id', $tenantId)->where('id', $id)->update([
            'is_active' => $new,
            'updated_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'is_active' => $new]);
        }

        return back()->with('success', $new ? 'Account activated.' : 'Account deactivated.');
    }

    public function bulkToggleAccounts(Request $request)
    {
        $tenantId = $this->tenantId();
        $action = $request->input('action');
        if (!in_array($action, ['activate', 'deactivate'], true)) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Invalid bulk action.'], 422)
                : back()->withErrors(['bulk' => 'Invalid bulk action.']);
        }

        $ids = array_filter((array) $request->input('ids', []));
        $q = trim((string) $request->input('q', ''));
        $type = $request->input('type');
        $status = $request->input('status');
        $usage = $request->input('usage');
        $posting = $request->input('posting');
        $flag = $request->input('flag');
        $hasChildren = $request->input('has_children');
        $groupId = $request->input('group');

        $query = DB::table('chart_of_accounts as coa')
            ->leftJoin('chart_of_accounts as parent', 'parent.id', '=', 'coa.parent_id')
            ->leftJoin('account_groups as ag', 'ag.id', '=', 'coa.account_group_id')
            ->where('coa.tenant_id', $tenantId);

        if (!empty($ids)) {
            $query->whereIn('coa.id', $ids);
        } else {
            if ($q !== '') {
                $query->where(function ($n) use ($q) {
                    $n->where('coa.code', 'ilike', "%{$q}%")
                        ->orWhere('coa.name', 'ilike', "%{$q}%")
                        ->orWhere('parent.name', 'ilike', "%{$q}%")
                        ->orWhere('ag.name', 'ilike', "%{$q}%");
                });
            }
            if ($type && $type !== 'all') $query->where('coa.type', $type);
            if ($status === 'active') $query->where('coa.is_active', true);
            elseif ($status === 'inactive') $query->where('coa.is_active', false);
            if ($posting === 'direct') $query->where('coa.allow_direct_posting', true);
            elseif ($posting === 'blocked') $query->where('coa.allow_direct_posting', false);
            if ($flag === 'cash') $query->where('coa.is_cash_account', true);
            elseif ($flag === 'bank') $query->where('coa.is_bank_account', true);
            if ($groupId) $query->where('coa.account_group_id', $groupId);
            if ($usage === 'used') $query->whereRaw('(SELECT COUNT(*) FROM journal_lines jl2 WHERE jl2.account_id = coa.id AND jl2.tenant_id = ?) > 0', [$tenantId]);
            elseif ($usage === 'unused') $query->whereRaw('(SELECT COUNT(*) FROM journal_lines jl2 WHERE jl2.account_id = coa.id AND jl2.tenant_id = ?) = 0', [$tenantId]);
            if ($hasChildren === 'yes') $query->whereRaw('(SELECT COUNT(*) FROM chart_of_accounts c3 WHERE c3.parent_id = coa.id AND c3.tenant_id = ?) > 0', [$tenantId]);
            elseif ($hasChildren === 'no') $query->whereRaw('(SELECT COUNT(*) FROM chart_of_accounts c3 WHERE c3.parent_id = coa.id AND c3.tenant_id = ?) = 0', [$tenantId]);
        }

        $targetIds = $query->pluck('coa.id')->all();
        $count = count($targetIds);

        if ($count > 0) {
            DB::table('chart_of_accounts')->where('tenant_id', $tenantId)->whereIn('id', $targetIds)->update([
                'is_active' => $action === 'activate',
                'updated_at' => now(),
            ]);
        }

        $label = $action === 'activate' ? 'activated' : 'deactivated';

        return $request->expectsJson()
            ? response()->json(['success' => true, 'count' => $count])
            : back()->with('success', "{$count} account(s) {$label}.");
    }

    public function exportChartOfAccounts(Request $request)
    {
        $tenantId = $this->tenantId();
        $childrenSub = DB::table('chart_of_accounts as c2')
            ->selectRaw('COUNT(*)')
            ->whereColumn('c2.parent_id', 'coa.id')
            ->where('c2.tenant_id', $tenantId);
        $journalSub = DB::table('journal_lines as jl')
            ->selectRaw('COUNT(*)')
            ->whereColumn('jl.account_id', 'coa.id')
            ->where('jl.tenant_id', $tenantId);
        $balanceSub = DB::table('journal_lines as jlb')
            ->join('journal_entries as je', 'je.id', '=', 'jlb.entry_id')
            ->selectRaw('COALESCE(SUM(jlb.debit_amount_base - jlb.credit_amount_base), 0)')
            ->whereColumn('jlb.account_id', 'coa.id')
            ->where('jlb.tenant_id', $tenantId)
            ->where('je.status', 'posted');

        $rows = DB::table('chart_of_accounts as coa')
            ->leftJoin('chart_of_accounts as p', 'p.id', '=', 'coa.parent_id')
            ->leftJoin('account_groups as ag', 'ag.id', '=', 'coa.account_group_id')
            ->where('coa.tenant_id', $tenantId)
            ->select([
                'coa.code', 'coa.name', 'coa.type', 'p.code as parent_code', 'ag.name as group_name',
                'coa.allow_direct_posting', 'coa.is_cash_account', 'coa.is_bank_account', 'coa.is_active',
            ])
            ->selectSub($childrenSub, 'children_count')
            ->selectSub($journalSub, 'journal_lines_count')
            ->selectSub($balanceSub, 'posted_balance')
            ->orderBy('coa.code')
            ->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Code', 'Name', 'Type', 'Parent Code', 'Group', 'Posted Balance', 'Children', 'Journal Lines', 'Direct Posting', 'Cash', 'Bank', 'Status']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->code, $r->name, $r->type, $r->parent_code, $r->group_name,
                    (float) ($r->posted_balance ?? 0),
                    (int) ($r->children_count ?? 0),
                    (int) ($r->journal_lines_count ?? 0),
                    !empty($r->allow_direct_posting) ? 'yes' : 'no',
                    !empty($r->is_cash_account) ? 'yes' : 'no',
                    !empty($r->is_bank_account) ? 'yes' : 'no',
                    !empty($r->is_active) ? 'active' : 'inactive',
                ]);
            }
            fclose($out);
        }, 'chart_of_accounts_' . now()->format('Ymd_His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function deleteAccount(Request $request, string $id)
    {
        $tenantId = $this->tenantId();
        $account = DB::table('chart_of_accounts')->where('tenant_id', $tenantId)->where('id', $id)->first();
        abort_if(!$account, 404);

        $children = (int) DB::table('chart_of_accounts')->where('tenant_id', $tenantId)->where('parent_id', $id)->count();
        if ($children > 0) {
            $msg = 'Cannot delete account: it has child accounts. Reassign or remove children first.';
            return $request->expectsJson() ? response()->json(['success' => false, 'message' => $msg], 422) : back()->withErrors(['delete' => $msg]);
        }

        try {
            $journalLines = (int) DB::table('journal_lines')->where('tenant_id', $tenantId)->where('account_id', $id)->count();
        } catch (\Throwable $e) {
            $journalLines = 0;
        }
        if ($journalLines > 0) {
            $msg = 'Cannot delete account: it has posted/linked journal lines. Deactivate it instead.';
            return $request->expectsJson() ? response()->json(['success' => false, 'message' => $msg], 422) : back()->withErrors(['delete' => $msg]);
        }

        DB::table('chart_of_accounts')->where('tenant_id', $tenantId)->where('id', $id)->delete();

        return $request->expectsJson()
            ? response()->json(['success' => true])
            : redirect()->route('accounting.chart-of-accounts')->with('success', 'Account deleted successfully.');
    }

    public function duplicateAccountPage(string $id): Response
    {
        $tenantId = $this->tenantId();
        $account = DB::table('chart_of_accounts')->where('tenant_id', $tenantId)->where('id', $id)->first();
        abort_if(!$account, 404);

        $parents = DB::table('chart_of_accounts')
            ->where('tenant_id', $tenantId)
            ->select(['id', 'code', 'name', 'type'])
            ->orderBy('code')
            ->get();
        $groups = DB::table('account_groups')
            ->where('tenant_id', $tenantId)
            ->select(['id', 'code', 'name', 'type'])
            ->orderBy('code')
            ->get();

        $suggested = trim((string) ($account->code ?? ''));
        $suggested = $suggested !== '' ? ($suggested . '-COPY') : '';

        return Inertia::render('Accounting/AccountCreate', [
            'parents' => $parents,
            'groups' => $groups,
            'prefill' => [
                'code' => $suggested,
                'name' => trim((string) ($account->name ?? '')) . ' (Copy)',
                'type' => $account->type ?? 'asset',
                'account_group_id' => $account->account_group_id,
                'parent_id' => $account->parent_id,
                'allow_direct_posting' => (bool) ($account->allow_direct_posting ?? true),
                'is_cash_account' => (bool) ($account->is_cash_account ?? false),
                'is_bank_account' => (bool) ($account->is_bank_account ?? false),
                'is_active' => (bool) ($account->is_active ?? true),
            ],
        ]);
    }

    public function showAccountPage(string $id): Response
    {
        $tenantId = $this->tenantId();
        $account = DB::table('chart_of_accounts as coa')
            ->leftJoin('chart_of_accounts as p', 'p.id', '=', 'coa.parent_id')
            ->leftJoin('account_groups as ag', 'ag.id', '=', 'coa.account_group_id')
            ->where('coa.tenant_id', $tenantId)
            ->where('coa.id', $id)
            ->select([
                'coa.id', 'coa.code', 'coa.name', 'coa.type', 'coa.parent_id', 'coa.account_group_id',
                'coa.allow_direct_posting', 'coa.is_cash_account', 'coa.is_bank_account', 'coa.is_active',
                'coa.created_at', 'coa.updated_at',
                'p.code as parent_code', 'p.name as parent_name',
                'ag.code as group_code', 'ag.name as group_name',
            ])->first();
        abort_if(!$account, 404);

        $children = DB::table('chart_of_accounts')
            ->where('tenant_id', $tenantId)
            ->where('parent_id', $id)
            ->select(['id', 'code', 'name', 'type', 'is_active'])
            ->orderBy('code')
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id, 'code' => $r->code, 'name' => $r->name, 'type' => $r->type,
                'is_active' => (bool) ($r->is_active ?? true),
            ])->values();

        try {
            $posted = DB::table('journal_lines as jl')
                ->join('journal_entries as je', 'je.id', '=', 'jl.entry_id')
                ->where('jl.tenant_id', $tenantId)
                ->where('jl.account_id', $id)
                ->where('je.status', 'posted');
            $debit = (float) (clone $posted)->sum('jl.debit_amount_base');
            $credit = (float) (clone $posted)->sum('jl.credit_amount_base');
            $postedCount = (int) (clone $posted)->count();
        } catch (\Throwable $e) {
            $debit = 0.0; $credit = 0.0; $postedCount = 0;
        }
        try {
            $totalLines = (int) DB::table('journal_lines')->where('tenant_id', $tenantId)->where('account_id', $id)->count();
        } catch (\Throwable $e) { $totalLines = 0; }

        $recentLines = [];
        try {
            $recentLines = DB::table('journal_lines as jl')
                ->leftJoin('journal_entries as je', 'je.id', '=', 'jl.entry_id')
                ->where('jl.tenant_id', $tenantId)
                ->where('jl.account_id', $id)
                ->select([
                    'jl.id', 'jl.debit_amount_base', 'jl.credit_amount_base', 'jl.description as line_description', 'jl.created_at',
                    'je.reference as entry_reference', 'je.posting_date', 'je.status as entry_status', 'je.description as entry_description',
                ])
                ->orderByDesc('je.posting_date')
                ->orderByDesc('jl.id')
                ->limit(15)
                ->get()
                ->map(fn ($r) => [
                    'id' => $r->id,
                    'entry_reference' => $r->entry_reference,
                    'posting_date' => $r->posting_date ? Carbon::parse($r->posting_date)->format('d M Y') : null,
                    'entry_status' => $r->entry_status,
                    'debit' => (float) ($r->debit_amount_base ?? 0),
                    'credit' => (float) ($r->credit_amount_base ?? 0),
                    'description' => $r->line_description ?: $r->entry_description,
                ])->values();
        } catch (\Throwable $e) { $recentLines = []; }

        $balance = $debit - $credit;

        $auditHistory = [];
        try {
            $auditHistory = DB::table('audit_logs as al')
                ->leftJoin('users as u', 'u.id', '=', 'al.user_id')
                ->where('al.tenant_id', $tenantId)
                ->where('al.subject_id', $id)
                ->where(function ($q) {
                    $q->where('al.subject_type', 'ilike', '%account%');
                })
                ->select(['al.id', 'al.action', 'al.old_values', 'al.new_values', 'al.created_at', 'u.name as user_name'])
                ->orderByDesc('al.created_at')
                ->limit(25)
                ->get()
                ->map(function ($r) {
                    $old = $r->old_values;
                    $new = $r->new_values;
                    if (is_string($old)) { $d = json_decode($old, true); if ($d !== null) $old = $d; }
                    if (is_string($new)) { $d = json_decode($new, true); if ($d !== null) $new = $d; }
                    return [
                        'id' => $r->id,
                        'action' => $r->action,
                        'user_name' => $r->user_name ?: 'System',
                        'created_at' => $r->created_at ? Carbon::parse($r->created_at)->format('d M Y, H:i') : null,
                        'old_values' => is_array($old) ? json_encode($old, JSON_UNESCAPED_UNICODE) : (string) ($old ?? ''),
                        'new_values' => is_array($new) ? json_encode($new, JSON_UNESCAPED_UNICODE) : (string) ($new ?? ''),
                    ];
                })->values();
        } catch (\Throwable $e) {
            try {
                $auditHistory = DB::table('audit_trail as at')
                    ->leftJoin('users as u', 'u.id', '=', 'at.user_id')
                    ->where('at.tenant_id', $tenantId)
                    ->where('at.record_id', $id)
                    ->where('at.table_name', 'chart_of_accounts')
                    ->select(['at.id', 'at.event_type as action', 'at.old_values', 'at.new_values', 'at.created_at', 'u.name as user_name'])
                    ->orderByDesc('at.created_at')
                    ->limit(25)
                    ->get()
                    ->map(fn ($r) => [
                        'id' => $r->id,
                        'action' => $r->action,
                        'user_name' => $r->user_name ?: 'System',
                        'created_at' => $r->created_at ? Carbon::parse($r->created_at)->format('d M Y, H:i') : null,
                        'old_values' => is_string($r->old_values) ? $r->old_values : json_encode($r->old_values),
                        'new_values' => is_string($r->new_values) ? $r->new_values : json_encode($r->new_values),
                    ])->values();
            } catch (\Throwable $e2) {
                $auditHistory = [];
            }
        }

        return Inertia::render('Accounting/AccountShow', [
            'account' => [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'parent_id' => $account->parent_id,
                'parent_label' => $account->parent_code ? ($account->parent_code . ' — ' . $account->parent_name) : null,
                'group_label' => $account->group_name ? (($account->group_code ? $account->group_code . ' — ' : '') . $account->group_name) : null,
                'allow_direct_posting' => (bool) $account->allow_direct_posting,
                'is_cash_account' => (bool) $account->is_cash_account,
                'is_bank_account' => (bool) $account->is_bank_account,
                'is_active' => (bool) ($account->is_active ?? true),
                'created_at' => $account->created_at ? Carbon::parse($account->created_at)->format('d M Y, H:i') : null,
                'updated_at' => $account->updated_at ? Carbon::parse($account->updated_at)->format('d M Y, H:i') : null,
            ],
            'children' => $children,
            'stats' => [
                'children_count' => $children->count(),
                'journal_lines_count' => $totalLines,
                'posted_lines_count' => $postedCount,
                'posted_debit' => $debit,
                'posted_credit' => $credit,
                'posted_balance' => $balance,
            ],
            'recent_lines' => $recentLines,
            'audit_history' => $auditHistory,
        ]);
    }

    public function accountLedger(Request $request, string $id): Response
    {
        $tenantId = $this->tenantId();
        $account = DB::table('chart_of_accounts')
            ->where('tenant_id', $tenantId)
            ->where('id', $id)
            ->first();
        abort_if(!$account, 404);

        $from = $request->query('from');
        $to = $request->query('to');
        $status = $request->query('status', 'posted');

        $base = DB::table('journal_lines as jl')
            ->join('journal_entries as je', 'je.id', '=', 'jl.entry_id')
            ->where('jl.tenant_id', $tenantId)
            ->where('jl.account_id', $id)
            ->where('je.tenant_id', $tenantId);
        if ($status && $status !== 'all') {
            $base->where('je.status', $status);
        }

        $openingQuery = (clone $base);
        if ($from) $openingQuery->whereDate('je.posting_date', '<', $from);
        try {
            $openingBalance = (float) $openingQuery->selectRaw('COALESCE(SUM(jl.debit_amount_base - jl.credit_amount_base), 0) as bal')->value('bal');
        } catch (\Throwable $e) {
            $openingBalance = 0.0;
        }

        $linesQuery = (clone $base);
        if ($from) $linesQuery->whereDate('je.posting_date', '>=', $from);
        if ($to) $linesQuery->whereDate('je.posting_date', '<=', $to);

        try {
            $raw = $linesQuery->select([
                'jl.id', 'jl.debit_amount_base', 'jl.credit_amount_base', 'jl.description as line_description',
                'je.reference as entry_reference', 'je.posting_date', 'je.status as entry_status', 'je.description as entry_description',
            ])
                ->orderBy('je.posting_date')
                ->orderBy('jl.id')
                ->limit(5000)
                ->get();
        } catch (\Throwable $e) {
            $raw = collect();
        }

        $running = $openingBalance;
        $totalDebit = 0.0;
        $totalCredit = 0.0;
        $lines = $raw->map(function ($r) use (&$running, &$totalDebit, &$totalCredit) {
            $debit = (float) ($r->debit_amount_base ?? 0);
            $credit = (float) ($r->credit_amount_base ?? 0);
            $totalDebit += $debit;
            $totalCredit += $credit;
            $running += $debit - $credit;

            return [
                'id' => $r->id,
                'posting_date' => $r->posting_date ? Carbon::parse($r->posting_date)->format('d M Y') : null,
                'entry_reference' => $r->entry_reference,
                'entry_status' => $r->entry_status,
                'description' => $r->line_description ?: $r->entry_description,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $running,
            ];
        })->values();

        $closingBalance = $openingBalance + $totalDebit - $totalCredit;

        return Inertia::render('Accounting/AccountLedger', [
            'account' => [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
            ],
            'lines' => $lines,
            'summary' => [
                'opening_balance' => $openingBalance,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'closing_balance' => $closingBalance,
                'line_count' => $lines->count(),
            ],
            'filters' => ['from' => $from, 'to' => $to, 'status' => $status ?? 'posted'],
        ]);
    }

    public function exportAccountLedger(Request $request, string $id)
    {
        $tenantId = $this->tenantId();
        $account = DB::table('chart_of_accounts')->where('tenant_id', $tenantId)->where('id', $id)->first();
        abort_if(!$account, 404);

        $from = $request->query('from');
        $to = $request->query('to');
        $status = $request->query('status', 'posted');

        $query = DB::table('journal_lines as jl')
            ->join('journal_entries as je', 'je.id', '=', 'jl.entry_id')
            ->where('jl.tenant_id', $tenantId)
            ->where('jl.account_id', $id)
            ->where('je.tenant_id', $tenantId);
        if ($status && $status !== 'all') $query->where('je.status', $status);
        if ($from) $query->whereDate('je.posting_date', '>=', $from);
        if ($to) $query->whereDate('je.posting_date', '<=', $to);

        $rows = $query->select([
            'je.posting_date', 'je.reference as entry_reference', 'je.status as entry_status',
            'jl.description as line_description', 'je.description as entry_description',
            'jl.debit_amount_base', 'jl.credit_amount_base',
        ])->orderBy('je.posting_date')->orderBy('jl.id')->get();

        return response()->streamDownload(function () use ($rows, $account) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Account', $account->code . ' — ' . $account->name]);
            fputcsv($out, ['Date', 'Entry Reference', 'Status', 'Description', 'Debit', 'Credit']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->posting_date ? Carbon::parse($r->posting_date)->format('Y-m-d') : '',
                    $r->entry_reference,
                    $r->entry_status,
                    $r->line_description ?: $r->entry_description,
                    (float) ($r->debit_amount_base ?? 0),
                    (float) ($r->credit_amount_base ?? 0),
                ]);
            }
            fclose($out);
        }, 'ledger_' . preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $account->code) . '_' . now()->format('Ymd_His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function createInventoryMovement(): Response
    {
        $items = DB::table('items')->select(['id', 'name', 'sku'])->orderBy('name')->get();
        $warehouses = DB::table('warehouses')->select(['id', 'name'])->orderBy('name')->get();

        return Inertia::render('Inventory/Create', [
            'items' => $items,
            'warehouses' => $warehouses,
        ]);
    }

    public function createProductionOrder(): Response
    {
        $boms = DB::table('bom_headers as bh')
            ->leftJoin('items as i', 'i.id', '=', 'bh.item_id')
            ->select(['bh.id', 'i.name as item_name', 'bh.version'])
            ->orderBy('i.name')
            ->get();

        return Inertia::render('Production/Create', [
            'boms' => $boms,
        ]);
    }

    public function createQualityInspection(): Response
    {
        $orders = DB::table('production_orders')
            ->select(['id', 'order_number', 'status'])
            ->whereIn('status', ['completed', 'in_production'])
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return Inertia::render('Quality/Create', [
            'orders' => $orders,
        ]);
    }

    public function createStandardCost(): Response
    {
        $items = DB::table('items')->select(['id', 'name', 'sku'])->orderBy('name')->get();

        return Inertia::render('Costing/Create', [
            'items' => $items,
        ]);
    }

    public function createPurchaseOrder(): Response
    {
        $suppliers = DB::table('suppliers')->select(['id', 'name', 'code'])->orderBy('name')->get();
        $items = DB::table('items')->select(['id', 'name', 'sku'])->orderBy('name')->get();

        return Inertia::render('Purchasing/Create', [
            'suppliers' => $suppliers,
            'items' => $items,
        ]);
    }

    public function createSalesOrder(): Response
    {
        $customers = DB::table('customers')->select(['id', 'name', 'code'])->orderBy('name')->get();
        $items = DB::table('items')->select(['id', 'name', 'sku'])->orderBy('name')->get();

        return Inertia::render('Sales/Create', [
            'customers' => $customers,
            'items' => $items,
        ]);
    }

    public function createDispatch(): Response
    {
        $orders = DB::table('sales_orders')
            ->select(['id', 'so_number', 'status'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return Inertia::render('Dispatch/Create', [
            'orders' => $orders,
        ]);
    }

    public function tenancy(): Response
    {
        $query = DB::table('tenants')
            ->select(['id', 'name', 'slug', 'email', 'plan', 'status'])
            ->orderBy('name');

        $summary = DB::table('tenants')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'active') as active")
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'suspended') as suspended")
            ->first();

        return Inertia::render('Tenancy', [
            'tenants' => $this->paginate($query),
            'tenancy_summary' => [
                'total' => (int) $summary->total,
                'active' => (int) $summary->active,
                'suspended' => (int) $summary->suspended,
            ],
        ]);
    }

    public function createTenant(): Response
    {
        return Inertia::render('Tenancy/Create');
    }

    public function settings(): Response
    {
        $tenant = DB::table('tenants')->where('id', $this->tenantId())->first();
        $stored = json_decode($tenant->settings ?? '{}', true) ?: [];

        return Inertia::render('Settings', [
            'company' => [
                'company_name' => $tenant->name,
                'tax_id' => $tenant->tax_id,
                'email' => $tenant->email,
                'phone' => $tenant->phone,
            ],
            'settings' => array_merge([
                'currency' => $tenant->currency,
                'timezone' => $tenant->timezone,
                'locale' => $tenant->locale,
                'fiscal_year_start' => '07-01',
                'default_warehouse' => 'Main Raw Store',
                'costing_method' => 'weighted_average',
                'quality_gate' => 'enabled',
                'shift_pattern' => '2 × 8 hours',
                'low_stock_alerts' => 'enabled',
                'delayed_order_alerts' => 'enabled',
                'quality_alerts' => 'enabled',
                'digest_recipients' => $tenant->email,
            ], $stored),
        ]);
    }

    // ============ HR Management ============

    public function hr(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('employees')
            ->where('tenant_id', $tenantId)
            ->select(['id', 'employee_code', 'first_name', 'last_name', 'email', 'phone', 'department', 'position', 'hire_date', 'is_active'])
            ->orderBy('employee_code');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['employee_code', 'first_name', 'last_name', 'email', 'position'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['name'] = $row['first_name'] . ' ' . $row['last_name'];
            $row['status'] = $row['is_active'] ? 'active' : 'inactive';
            unset($row['is_active'], $row['first_name'], $row['last_name']);

            return $row;
        }, $payload['data']);

        $summary = DB::table('employees')
            ->where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(*) FILTER (WHERE is_active) as active')
            ->first();

        return Inertia::render('HR', [
            'employees' => $payload,
            'hr_summary' => [
                'total' => (int) $summary->total,
                'active' => (int) $summary->active,
            ],
        ]);
    }

    public function createEmployee(): Response
    {
        $departments = DB::table('departments')->select(['id', 'name'])->orderBy('name')->get();

        return Inertia::render('HR/Create', [
            'departments' => $departments,
        ]);
    }

    // ============ Maintenance ============

    public function maintenance(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('machines')
            ->where('tenant_id', $tenantId)
            ->select(['id', 'machine_code', 'name', 'type', 'status', 'location', 'purchase_date', 'last_maintenance_date'])
            ->orderBy('machine_code');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['machine_code', 'name', 'type', 'location'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['status_label'] = ucfirst(str_replace('_', ' ', $row['status']));

            return $row;
        }, $payload['data']);

        $summary = DB::table('machines')
            ->where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'active') as active")
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'maintenance') as maintenance")
            ->first();

        return Inertia::render('Maintenance', [
            'machines' => $payload,
            'maintenance_summary' => [
                'total' => (int) $summary->total,
                'active' => (int) $summary->active,
                'maintenance' => (int) $summary->maintenance,
            ],
        ]);
    }

    public function createMachine(): Response
    {
        return Inertia::render('Maintenance/Create');
    }

    // ============ Products / Items ============

    public function products(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('items as i')
            ->leftJoin('item_categories as c', 'c.id', '=', 'i.category_id')
            ->where('i.tenant_id', $tenantId)
            ->select([
                'i.id',
                'i.sku',
                'i.name',
                'i.category_id',
                'c.name as category_name',
                'i.unit_of_measure',
                'i.default_purchase_price',
                'i.default_sales_price',
                'i.reorder_level',
                'i.is_active',
            ])
            ->orderBy('i.sku');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['i.sku', 'i.name', 'c.name'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['category'] = $row['category_name'] ?? '—';
            $row['status'] = $row['is_active'] ? 'active' : 'inactive';
            unset($row['is_active'], $row['category_name']);

            return $row;
        }, $payload['data']);

        $summary = DB::table('items')
            ->where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(*) FILTER (WHERE is_active) as active')
            ->first();

        return Inertia::render('Products', [
            'products' => $payload,
            'product_summary' => [
                'total' => (int) $summary->total,
                'active' => (int) $summary->active,
            ],
        ]);
    }

    public function createProduct(): Response
    {
        $categories = DB::table('item_categories')->select(['id', 'name'])->orderBy('name')->get();
        $units = DB::table('units')->select(['id', 'name', 'code'])->orderBy('name')->get();

        return Inertia::render('Products/Create', [
            'categories' => $categories,
            'units' => $units,
        ]);
    }

    // ============ Warehouses ============

    public function warehouses(Request $request): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('warehouses as w')
            ->leftJoin('factories as f', 'f.id', '=', 'w.factory_id')
            ->where('w.tenant_id', $tenantId)
            ->select([
                'w.id', 'w.code', 'w.name', 'w.type', 'w.address as location', 'w.is_active',
                'f.name as factory_name',
            ])
            ->selectSub(function ($q) {
                $q->from('warehouse_bins as wb')
                    ->whereColumn('wb.warehouse_id', 'w.id')
                    ->selectRaw('COUNT(*)');
            }, 'bin_count')
            ->orderBy('w.code');

        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where(function ($nested) use ($q) {
                $nested->where('w.code', 'ilike', "%{$q}%")
                    ->orWhere('w.name', 'ilike', "%{$q}%")
                    ->orWhere('w.type', 'ilike', "%{$q}%")
                    ->orWhere('w.address', 'ilike', "%{$q}%")
                    ->orWhere('f.name', 'ilike', "%{$q}%");
            });
        }

        $type = $request->query('type');
        if ($type) {
            $query->where('w.type', $type);
        }

        $status = $request->query('status');
        if ($status === 'active') {
            $query->where('w.is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('w.is_active', false);
        }

        $payload = $this->paginate($query);

        $payload['data'] = array_map(function (array $row) {
            $row['status'] = $row['is_active'] ? 'active' : 'inactive';
            $row['bin_count'] = (int) ($row['bin_count'] ?? 0);
            unset($row['is_active']);

            return $row;
        }, $payload['data']);

        $base = DB::table('warehouses')->where('tenant_id', $tenantId);
        $summary = (clone $base)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(*) FILTER (WHERE is_active) as active')
            ->selectRaw('COUNT(*) FILTER (WHERE NOT is_active) as inactive')
            ->first();

        $byType = (clone $base)
            ->select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($r) => ['type' => $r->type ?? 'general', 'count' => (int) $r->count])
            ->values()
            ->all();

        $totalBins = (int) DB::table('warehouse_bins as wb')
            ->join('warehouses as w', 'w.id', '=', 'wb.warehouse_id')
            ->where('w.tenant_id', $tenantId)
            ->count();

        return Inertia::render('Warehouses', [
            'warehouses' => $payload,
            'warehouse_summary' => [
                'total' => (int) ($summary->total ?? 0),
                'active' => (int) ($summary->active ?? 0),
                'inactive' => (int) ($summary->inactive ?? 0),
                'total_bins' => $totalBins,
                'by_type' => $byType,
            ],
            'filters' => [
                'q' => $q,
                'type' => $type,
                'status' => $status,
            ],
        ]);
    }

    public function createWarehouse(): Response
    {
        $factories = DB::table('factories')
            ->where('tenant_id', $this->tenantId())
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Warehouses/Create', [
            'factories' => $factories,
        ]);
    }

    public function storeWarehouse(Request $request)
    {
        $tenantId = $this->tenantId();

        $validated = $request->validate([
            'code' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'factory_id' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $factoryId = $validated['factory_id'] ?? (int) DB::table('factories')
            ->where('tenant_id', $tenantId)
            ->orderBy('id')
            ->value('id');

        if (!$factoryId) {
            $factoryId = (int) DB::table('factories')->orderBy('id')->value('id');
        }
        if (!$factoryId) {
            return back()->withErrors(['name' => 'No factory found. Please create a factory before adding warehouses.'])->withInput();
        }

        DB::table('warehouses')->insert([
            'tenant_id' => $tenantId,
            'factory_id' => $factoryId ?: null,
            'code' => $validated['code'] ?? null,
            'name' => $validated['name'],
            'type' => $validated['type'] ?? 'general',
            'address' => $validated['location'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('warehouses')->with('success', 'Warehouse created successfully.');
    }

    public function editWarehouse(string $id): Response
    {
        $tenantId = $this->tenantId();
        $warehouse = DB::table('warehouses')
            ->where('tenant_id', $tenantId)
            ->where('id', $id)
            ->first();

        abort_if(!$warehouse, 404);

        $factories = DB::table('factories')
            ->where('tenant_id', $tenantId)
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Warehouses/Edit', [
            'warehouse' => [
                'id' => $warehouse->id,
                'code' => $warehouse->code,
                'name' => $warehouse->name,
                'type' => $warehouse->type,
                'location' => $warehouse->address ?? '',
                'factory_id' => $warehouse->factory_id,
                'is_active' => (bool) $warehouse->is_active,
            ],
            'factories' => $factories,
        ]);
    }

    public function updateWarehouse(Request $request, string $id)
    {
        $tenantId = $this->tenantId();
        $existing = DB::table('warehouses')->where('tenant_id', $tenantId)->where('id', $id)->first();
        abort_if(!$existing, 404);

        $validated = $request->validate([
            'code' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'factory_id' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $factoryId = $validated['factory_id'] ?? $existing->factory_id;

        DB::table('warehouses')
            ->where('tenant_id', $tenantId)
            ->where('id', $id)
            ->update([
                'code' => $validated['code'] ?? null,
                'name' => $validated['name'],
                'type' => $validated['type'] ?? 'general',
                'address' => $validated['location'] ?? null,
                'factory_id' => $factoryId,
                'is_active' => (bool) ($validated['is_active'] ?? false),
                'updated_at' => now(),
            ]);

        return redirect()->route('warehouses')->with('success', 'Warehouse updated successfully.');
    }

    public function toggleWarehouse(Request $request, string $id)
    {
        $tenantId = $this->tenantId();
        $warehouse = DB::table('warehouses')->where('tenant_id', $tenantId)->where('id', $id)->first();
        abort_if(!$warehouse, 404);

        $new = !(bool) $warehouse->is_active;
        DB::table('warehouses')->where('id', $id)->update(['is_active' => $new, 'updated_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'is_active' => $new]);
        }

        return back()->with('success', $new ? 'Warehouse activated.' : 'Warehouse deactivated.');
    }

    public function exportWarehouses(Request $request)
    {
        $tenantId = $this->tenantId();
        $rows = DB::table('warehouses as w')
            ->leftJoin('factories as f', 'f.id', '=', 'w.factory_id')
            ->where('w.tenant_id', $tenantId)
            ->select(['w.code', 'w.name', 'w.type', 'w.address as location', 'w.is_active', 'f.name as factory_name'])
            ->orderBy('w.code')
            ->get();

        $filename = 'warehouses_' . now()->format('Ymd_His') . '.csv';
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Code', 'Name', 'Type', 'Location', 'Status', 'Factory']);
            foreach ($rows as $r) {
                fputcsv($out, [$r->code, $r->name, $r->type, $r->location, $r->is_active ? 'active' : 'inactive', $r->factory_name]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    // ============ Manufacturing — BOMs ============

    public function boms(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('bom_headers as bh')
            ->leftJoin('items as i', 'i.id', '=', 'bh.item_id')
            ->where('bh.tenant_id', $tenantId)
            ->select([
                'bh.id',
                'bh.code',
                'i.name as item_name',
                'bh.version',
                'bh.status',
                'bh.effective_from',
            ])
            ->orderBy('bh.code');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['bh.code', 'i.name'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['status_label'] = ucfirst(str_replace('_', ' ', $row['status']));

            return $row;
        }, $payload['data']);

        return Inertia::render('BOMs', [
            'boms' => $payload,
        ]);
    }

    public function createBOM(): Response
    {
        $items = DB::table('items')->select(['id', 'sku', 'name'])->where('is_active', true)->orderBy('sku')->get();

        return Inertia::render('BOMs/Create', [
            'items' => $items,
        ]);
    }

    // ============ Manufacturing — Routings ============

    public function routings(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('routings as r')
            ->leftJoin('items as i', 'i.id', '=', 'r.item_id')
            ->where('r.tenant_id', $tenantId)
            ->select([
                'r.id',
                'r.code',
                'i.name as item_name',
                'r.version',
                'r.status',
            ])
            ->orderBy('r.code');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['r.code', 'i.name'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['status_label'] = ucfirst(str_replace('_', ' ', $row['status']));

            return $row;
        }, $payload['data']);

        return Inertia::render('Routings', [
            'routings' => $payload,
        ]);
    }

    public function createRouting(): Response
    {
        $items = DB::table('items')->select(['id', 'sku', 'name'])->where('is_active', true)->orderBy('sku')->get();
        $workCentres = DB::table('work_centres')->select(['id', 'name'])->orderBy('name')->get();

        return Inertia::render('Routings/Create', [
            'items' => $items,
            'work_centres' => $workCentres,
        ]);
    }

    // ============ Reports ============

    public function reports(): Response
    {
        $tenantId = $this->tenantId();

        $reports = DB::table('reports')
            ->where('tenant_id', $tenantId)
            ->select(['id', 'name', 'type', 'status', 'generated_at', 'created_by'])
            ->orderByDesc('generated_at')
            ->get();

        return Inertia::render('Reports', [
            'reports' => $reports,
        ]);
    }

    // ============ Users ============

    public function users(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('users')
            ->where('tenant_id', $tenantId)
            ->select(['id', 'name', 'email', 'role', 'is_active', 'created_at'])
            ->orderBy('name');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['name', 'email', 'role'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['status'] = $row['is_active'] ? 'active' : 'inactive';
            unset($row['is_active']);

            return $row;
        }, $payload['data']);

        return Inertia::render('Users', [
            'users' => $payload,
        ]);
    }

    public function createUser(): Response
    {
        return Inertia::render('Users/Create');
    }

    // ============ Supply Chain — Goods Receipts ============

    public function goodsReceipts(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('goods_receipts as gr')
            ->leftJoin('purchase_orders as po', 'po.id', '=', 'gr.purchase_order_id')
            ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->where('gr.tenant_id', $tenantId)
            ->select([
                'gr.id',
                'gr.receipt_number',
                'po.po_number',
                's.name as supplier_name',
                'gr.receipt_date',
                'gr.status',
                'gr.received_by',
            ])
            ->orderByDesc('gr.receipt_date')
            ->orderByDesc('gr.id');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['gr.receipt_number', 'po.po_number', 's.name'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['receipt_date'] = $this->day($row['receipt_date']);
            $row['status_label'] = ucfirst(str_replace('_', ' ', $row['status']));

            return $row;
        }, $payload['data']);

        $summary = DB::table('goods_receipts')
            ->where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'received') as received")
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'pending') as pending")
            ->first();

        return Inertia::render('SupplyChain/GoodsReceipts', [
            'goods_receipts' => $payload,
            'gr_summary' => [
                'total' => (int) $summary->total,
                'received' => (int) $summary->received,
                'pending' => (int) $summary->pending,
            ],
        ]);
    }

    public function createGoodsReceipt(): Response
    {
        $pos = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->where('po.status', 'approved')
            ->select(['po.id', 'po.po_number', 's.name as supplier_name'])
            ->orderByDesc('po.id')
            ->limit(50)
            ->get();

        return Inertia::render('SupplyChain/CreateGoodsReceipt', [
            'purchase_orders' => $pos,
        ]);
    }

    // ============ Supply Chain — Purchase Requisitions ============

    public function purchaseRequisitions(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('purchase_requisitions as pr')
            ->leftJoin('employees as e', 'e.id', '=', 'pr.requested_by')
            ->where('pr.tenant_id', $tenantId)
            ->select([
                'pr.id',
                'pr.requisition_number',
                'pr.requested_by',
                'pr.required_date',
                'pr.status',
                'pr.total_amount',
            ])
            ->orderByDesc('pr.created_at')
            ->orderByDesc('pr.id');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['pr.requisition_number'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['required_date'] = $this->day($row['required_date']);
            $row['status_label'] = ucfirst(str_replace('_', ' ', $row['status']));

            return $row;
        }, $payload['data']);

        $summary = DB::table('purchase_requisitions')
            ->where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'approved') as approved")
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'pending') as pending")
            ->first();

        return Inertia::render('SupplyChain/PurchaseRequisitions', [
            'requisitions' => $payload,
            'pr_summary' => [
                'total' => (int) $summary->total,
                'approved' => (int) $summary->approved,
                'pending' => (int) $summary->pending,
            ],
        ]);
    }

    public function createPurchaseRequisition(): Response
    {
        $employees = DB::table('employees')
            ->where('is_active', true)
            ->select(['id', 'employee_code', 'first_name', 'last_name'])
            ->orderBy('employee_code')
            ->get();

        return Inertia::render('SupplyChain/CreatePurchaseRequisition', [
            'employees' => $employees,
        ]);
    }

    // ============ Advanced Inventory — Stock Transfers ============

    public function stockTransfers(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('stock_transfers as st')
            ->leftJoin('warehouses as from_w', 'from_w.id', '=', 'st.from_warehouse_id')
            ->leftJoin('warehouses as to_w', 'to_w.id', '=', 'st.to_warehouse_id')
            ->where('st.tenant_id', $tenantId)
            ->select([
                'st.id', 'st.transfer_number', 'st.status', 'st.transfer_date',
                'from_w.name as from_warehouse', 'to_w.name as to_warehouse',
            ])
            ->orderByDesc('st.transfer_date')
            ->orderByDesc('st.id');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['st.transfer_number', 'from_w.name', 'to_w.name'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['transfer_date'] = $this->day($row['transfer_date']);
            $row['status_label'] = ucfirst(str_replace('_', ' ', $row['status']));

            return $row;
        }, $payload['data']);

        $summary = DB::table('stock_transfers')
            ->where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'completed') as completed")
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'pending') as pending")
            ->first();

        return Inertia::render('Inventory/StockTransfers', [
            'transfers' => $payload,
            'transfer_summary' => [
                'total' => (int) $summary->total,
                'completed' => (int) $summary->completed,
                'pending' => (int) $summary->pending,
            ],
        ]);
    }

    public function createStockTransfer(): Response
    {
        $warehouses = DB::table('warehouses')
            ->where('is_active', true)
            ->select(['id', 'code', 'name'])
            ->orderBy('code')
            ->get();

        return Inertia::render('Inventory/CreateStockTransfer', [
            'warehouses' => $warehouses,
        ]);
    }

    // ============ Advanced Inventory — Batch / Lot Tracking ============

    public function batches(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('stock_batches as sb')
            ->leftJoin('items as i', 'i.id', '=', 'sb.item_id')
            ->where('sb.tenant_id', $tenantId)
            ->select([
                'sb.id', 'sb.batch_number', 'i.name as item_name', 'sb.quantity',
                'sb.expiry_date', 'sb.status', 'sb.manufacturing_date',
            ])
            ->orderByDesc('sb.manufacturing_date')
            ->orderByDesc('sb.id');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['sb.batch_number', 'i.name'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['expiry_date'] = $this->day($row['expiry_date']);
            $row['manufacturing_date'] = $this->day($row['manufacturing_date']);
            $row['status_label'] = ucfirst(str_replace('_', ' ', $row['status']));

            return $row;
        }, $payload['data']);

        return Inertia::render('Inventory/Batches', [
            'batches' => $payload,
        ]);
    }

    public function createBatch(): Response
    {
        $items = DB::table('items')
            ->where('is_active', true)
            ->select(['id', 'sku', 'name'])
            ->orderBy('sku')
            ->get();
        $warehouses = DB::table('warehouses')
            ->where('is_active', true)
            ->select(['id', 'code', 'name'])
            ->orderBy('code')
            ->get();

        return Inertia::render('Inventory/CreateBatch', [
            'items' => $items,
            'warehouses' => $warehouses,
        ]);
    }

    // ============ Advanced Inventory — Serial Numbers ============

    public function serialNumbers(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('stock_serials as ss')
            ->leftJoin('items as i', 'i.id', '=', 'ss.item_id')
            ->where('ss.tenant_id', $tenantId)
            ->select([
                'ss.id', 'ss.serial_number', 'i.name as item_name',
                'ss.status', 'ss.warranty_expiry', 'ss.current_location',
            ])
            ->orderByDesc('ss.id');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['ss.serial_number', 'i.name'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['warranty_expiry'] = $this->day($row['warranty_expiry']);
            $row['status_label'] = ucfirst(str_replace('_', ' ', $row['status']));

            return $row;
        }, $payload['data']);

        return Inertia::render('Inventory/SerialNumbers', [
            'serials' => $payload,
        ]);
    }

    public function createSerial(): Response
    {
        $items = DB::table('items')
            ->where('is_active', true)
            ->select(['id', 'sku', 'name'])
            ->orderBy('sku')
            ->get();

        return Inertia::render('Inventory/CreateSerial', [
            'items' => $items,
        ]);
    }

    // ============ Sales Advanced — Quotes ============

    public function salesQuotes(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('sales_quotes as sq')
            ->leftJoin('customers as c', 'c.id', '=', 'sq.customer_id')
            ->where('sq.tenant_id', $tenantId)
            ->select([
                'sq.id', 'sq.quote_number', 'c.name as customer_name',
                'sq.quote_date', 'sq.valid_until', 'sq.status', 'sq.total_amount',
            ])
            ->orderByDesc('sq.quote_date')
            ->orderByDesc('sq.id');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['sq.quote_number', 'c.name'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['quote_date'] = $this->day($row['quote_date']);
            $row['valid_until'] = $this->day($row['valid_until']);
            $row['status_label'] = ucfirst(str_replace('_', ' ', $row['status']));

            return $row;
        }, $payload['data']);

        $summary = DB::table('sales_quotes')
            ->where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'sent') as sent")
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'accepted') as accepted")
            ->first();

        return Inertia::render('Sales/Quotes', [
            'quotes' => $payload,
            'quote_summary' => [
                'total' => (int) $summary->total,
                'sent' => (int) $summary->sent,
                'accepted' => (int) $summary->accepted,
            ],
        ]);
    }

    public function createSalesQuote(): Response
    {
        $customers = DB::table('customers')
            ->where('is_active', true)
            ->select(['id', 'name', 'code'])
            ->orderBy('name')
            ->get();
        $items = DB::table('items')
            ->where('is_active', true)
            ->select(['id', 'sku', 'name', 'default_sales_price'])
            ->orderBy('sku')
            ->get();

        return Inertia::render('Sales/CreateQuote', [
            'customers' => $customers,
            'items' => $items,
        ]);
    }

    // ============ Sales Advanced — Customer Invoices ============

    public function customerInvoices(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('customer_invoices as ci')
            ->leftJoin('customers as c', 'c.id', '=', 'ci.customer_id')
            ->where('ci.tenant_id', $tenantId)
            ->select([
                'ci.id', 'ci.invoice_number', 'c.name as customer_name',
                'ci.invoice_date', 'ci.due_date', 'ci.status', 'ci.total_amount',
            ])
            ->orderByDesc('ci.invoice_date')
            ->orderByDesc('ci.id');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['ci.invoice_number', 'c.name'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['invoice_date'] = $this->day($row['invoice_date']);
            $row['due_date'] = $this->day($row['due_date']);
            $row['status_label'] = ucfirst(str_replace('_', ' ', $row['status']));

            return $row;
        }, $payload['data']);

        $summary = DB::table('customer_invoices')
            ->where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'paid') as paid")
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'unpaid') as unpaid")
            ->first();

        return Inertia::render('Sales/CustomerInvoices', [
            'invoices' => $payload,
            'invoice_summary' => [
                'total' => (int) $summary->total,
                'paid' => (int) $summary->paid,
                'unpaid' => (int) $summary->unpaid,
            ],
        ]);
    }

    public function createCustomerInvoice(): Response
    {
        $customers = DB::table('customers')
            ->where('is_active', true)
            ->select(['id', 'name', 'code'])
            ->orderBy('name')
            ->get();
        $items = DB::table('items')
            ->where('is_active', true)
            ->select(['id', 'sku', 'name', 'default_sales_price'])
            ->orderBy('sku')
            ->get();

        return Inertia::render('Sales/CreateCustomerInvoice', [
            'customers' => $customers,
            'items' => $items,
        ]);
    }

    // ============ Purchasing Advanced — Supplier Invoices ============

    public function supplierInvoices(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('supplier_invoices as si')
            ->leftJoin('suppliers as s', 's.id', '=', 'si.supplier_id')
            ->where('si.tenant_id', $tenantId)
            ->select([
                'si.id', 'si.invoice_number', 's.name as supplier_name',
                'si.invoice_date', 'si.due_date', 'si.status', 'si.total_amount',
            ])
            ->orderByDesc('si.invoice_date')
            ->orderByDesc('si.id');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['si.invoice_number', 's.name'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['invoice_date'] = $this->day($row['invoice_date']);
            $row['due_date'] = $this->day($row['due_date']);
            $row['status_label'] = ucfirst(str_replace('_', ' ', $row['status']));

            return $row;
        }, $payload['data']);

        $summary = DB::table('supplier_invoices')
            ->where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'paid') as paid")
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'unpaid') as unpaid")
            ->first();

        return Inertia::render('Purchasing/SupplierInvoices', [
            'invoices' => $payload,
            'supplier_invoice_summary' => [
                'total' => (int) $summary->total,
                'paid' => (int) $summary->paid,
                'unpaid' => (int) $summary->unpaid,
            ],
        ]);
    }

    public function createSupplierInvoice(): Response
    {
        $suppliers = DB::table('suppliers')
            ->where('is_active', true)
            ->select(['id', 'name', 'code'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Purchasing/CreateSupplierInvoice', [
            'suppliers' => $suppliers,
        ]);
    }

    // ============ Accounting Advanced — Bank Reconciliation ============

    public function bankReconciliation(Request $request): Response
    {
        $tenantId = $this->tenantId();
        $status = (string) $request->query('status', '');
        $from = $request->query('from');
        $to = $request->query('to');

        $query = DB::table('bank_reconciliations as br')
            ->leftJoin('chart_of_accounts as coa', 'coa.id', '=', 'br.account_id')
            ->where('br.tenant_id', $tenantId)
            ->select([
                'br.id', 'br.account_id', 'br.reference', 'coa.name as account_name',
                'br.statement_date', 'br.book_balance', 'br.bank_balance',
                'br.status', 'br.reconciled_date',
            ])
            ->orderByDesc('br.statement_date')
            ->orderByDesc('br.id');

        if (in_array($status, ['open', 'pending', 'reconciled'], true)) {
            $query->where('br.status', $status);
        }
        if ($from) $query->whereDate('br.statement_date', '>=', $from);
        if ($to) $query->whereDate('br.statement_date', '<=', $to);

        $payload = $this->paginate(
            $this->search($query, $request->query('q'), ['br.reference', 'coa.name'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['statement_date'] = $this->day($row['statement_date']);
            $row['reconciled_date'] = $this->day($row['reconciled_date']);
            $row['status_label'] = ucfirst(str_replace('_', ' ', (string) $row['status']));
            $row['difference'] = (float) ($row['bank_balance'] ?? 0) - (float) ($row['book_balance'] ?? 0);

            return $row;
        }, $payload['data']);

        $summary = DB::table('bank_reconciliations')
            ->where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'reconciled') as reconciled")
            ->selectRaw("COUNT(*) FILTER (WHERE status != 'reconciled') as pending")
            ->selectRaw('COALESCE(SUM(bank_balance), 0) as bank_total')
            ->selectRaw('COALESCE(SUM(book_balance), 0) as book_total')
            ->first();

        return Inertia::render('Accounting/BankReconciliation', [
            'reconciliations' => $payload,
            'filters' => ['status' => $status, 'from' => $from ?? '', 'to' => $to ?? ''],
            'reconciliation_summary' => [
                'total' => (int) ($summary->total ?? 0),
                'reconciled' => (int) ($summary->reconciled ?? 0),
                'pending' => (int) ($summary->pending ?? 0),
                'bank_total' => $this->money($summary->bank_total ?? 0),
                'book_total' => $this->money($summary->book_total ?? 0),
            ],
        ]);
    }

    public function createBankReconciliation(): Response
    {
        $tenantId = $this->tenantId();

        $accounts = DB::table('chart_of_accounts')
            ->where('tenant_id', $tenantId)
            ->where('is_bank_account', true)
            ->where('is_active', true)
            ->select(['id', 'code', 'name'])
            ->orderBy('code')
            ->get()
            ->map(function ($account) use ($tenantId) {
                $debit = (float) DB::table('journal_lines as jl')
                    ->join('journal_entries as je', 'je.id', '=', 'jl.entry_id')
                    ->where('je.tenant_id', $tenantId)
                    ->where('je.status', 'posted')
                    ->where('jl.account_id', $account->id)
                    ->sum('jl.debit_amount_base');
                $credit = (float) DB::table('journal_lines as jl')
                    ->join('journal_entries as je', 'je.id', '=', 'jl.entry_id')
                    ->where('je.tenant_id', $tenantId)
                    ->where('je.status', 'posted')
                    ->where('jl.account_id', $account->id)
                    ->sum('jl.credit_amount_base');

                return [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'book_balance' => round($debit - $credit, 2),
                ];
            })
            ->values();

        $suggestedReference = 'BR-' . now()->format('Ymd') . '-'
            . str_pad((string) ((int) DB::table('bank_reconciliations')->where('reference', 'like', 'BR-' . now()->format('Ymd') . '-%')->count() + 1), 4, '0', STR_PAD_LEFT);

        return Inertia::render('Accounting/CreateBankReconciliation', [
            'accounts' => $accounts,
            'suggested_reference' => $suggestedReference,
            'default_date' => now()->format('Y-m-d'),
        ]);
    }

    public function storeBankReconciliation(Request $request)
    {
        $tenantId = $this->tenantId();

        $validated = $request->validate([
            'reference' => ['required', 'string', 'max:100'],
            'account_id' => ['required', 'integer'],
            'statement_date' => ['required', 'date'],
            'book_balance' => ['required', 'numeric'],
            'bank_balance' => ['required', 'numeric'],
            'status' => ['nullable', 'in:open,pending,reconciled'],
        ]);

        $account = DB::table('chart_of_accounts')
            ->where('tenant_id', $tenantId)
            ->where('is_bank_account', true)
            ->where('id', (int) $validated['account_id'])
            ->first();
        if (!$account) {
            return back()->withErrors(['account_id' => 'Select a valid bank account from the Chart of Accounts.'])->withInput();
        }

        $factoryId = (int) DB::table('factories')->where('tenant_id', $tenantId)->orderBy('id')->value('id');
        if (!$factoryId) {
            return back()->withErrors(['account_id' => 'Set up a factory for this tenant before reconciling bank accounts.'])->withInput();
        }

        $status = $validated['status'] ?? 'open';

        DB::table('bank_reconciliations')->insert([
            'tenant_id' => $tenantId,
            'factory_id' => $factoryId,
            'account_id' => $account->id,
            'reference' => trim((string) $validated['reference']),
            'statement_date' => Carbon::parse($validated['statement_date'])->toDateString(),
            'book_balance' => round((float) $validated['book_balance'], 2),
            'bank_balance' => round((float) $validated['bank_balance'], 2),
            'status' => $status,
            'reconciled_date' => $status === 'reconciled' ? now()->toDateString() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('accounting.bank-reconciliation')
            ->with('success', 'Bank reconciliation ' . trim((string) $validated['reference']) . ' created successfully.');
    }

    public function reconcileBankReconciliation(Request $request, string $id)
    {
        $tenantId = $this->tenantId();
        $reconciliation = DB::table('bank_reconciliations')
            ->where('tenant_id', $tenantId)
            ->where('id', $id)
            ->first();
        abort_if(!$reconciliation, 404);

        if ($reconciliation->status === 'reconciled') {
            return back()->withErrors(['reconciliation' => 'This reconciliation is already marked as reconciled.']);
        }

        DB::table('bank_reconciliations')->where('id', $id)->where('tenant_id', $tenantId)->update([
            'status' => 'reconciled',
            'reconciled_date' => now()->toDateString(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Reconciliation ' . ($reconciliation->reference ?: '#' . $id) . ' marked as reconciled.');
    }

    public function deleteBankReconciliation(string $id)
    {
        $tenantId = $this->tenantId();
        $reconciliation = DB::table('bank_reconciliations')
            ->where('tenant_id', $tenantId)
            ->where('id', $id)
            ->first();
        abort_if(!$reconciliation, 404);

        DB::table('bank_reconciliations')->where('id', $id)->where('tenant_id', $tenantId)->delete();

        return redirect()->route('accounting.bank-reconciliation')->with('success', 'Bank reconciliation deleted.');
    }

    public function exportBankReconciliations(Request $request)
    {
        $tenantId = $this->tenantId();
        $status = (string) $request->query('status', '');
        $from = $request->query('from');
        $to = $request->query('to');

        $query = DB::table('bank_reconciliations as br')
            ->leftJoin('chart_of_accounts as coa', 'coa.id', '=', 'br.account_id')
            ->where('br.tenant_id', $tenantId)
            ->select([
                'br.reference', 'coa.name as account_name', 'br.statement_date',
                'br.book_balance', 'br.bank_balance', 'br.status', 'br.reconciled_date',
            ])
            ->orderByDesc('br.statement_date')
            ->orderByDesc('br.id');
        if (in_array($status, ['open', 'pending', 'reconciled'], true)) $query->where('br.status', $status);
        if ($from) $query->whereDate('br.statement_date', '>=', $from);
        if ($to) $query->whereDate('br.statement_date', '<=', $to);

        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('br.reference', 'ilike', '%' . $q . '%')
                    ->orWhere('coa.name', 'ilike', '%' . $q . '%');
            });
        }

        $rows = $query->limit(20000)->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Reference', 'Bank Account', 'Statement Date', 'Book Balance', 'Bank Balance', 'Difference', 'Status', 'Reconciled On']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->reference,
                    $r->account_name,
                    $r->statement_date ? Carbon::parse($r->statement_date)->format('Y-m-d') : '',
                    (float) $r->book_balance,
                    (float) $r->bank_balance,
                    (float) $r->bank_balance - (float) $r->book_balance,
                    $r->status,
                    $r->reconciled_date ? Carbon::parse($r->reconciled_date)->format('Y-m-d') : '',
                ]);
            }
            fclose($out);
        }, 'bank_reconciliations_' . now()->format('Ymd_His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    // ============ Production Advanced — Scheduling ============

    public function productionSchedule(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('production_orders as po')
            ->leftJoin('items as i', 'i.id', '=', 'po.item_id')
            ->where('po.tenant_id', $tenantId)
            ->select([
                'po.id', 'po.order_number', 'i.name as item_name',
                'po.planned_start_date', 'po.planned_end_date',
                'po.actual_start_date', 'po.actual_end_date',
                'po.status', 'po.planned_quantity',
            ])
            ->orderBy('po.planned_start_date')
            ->orderByDesc('po.id');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['po.order_number', 'i.name'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['planned_start_date'] = $this->day($row['planned_start_date']);
            $row['planned_end_date'] = $this->day($row['planned_end_date']);
            $row['actual_start_date'] = $this->day($row['actual_start_date']);
            $row['actual_end_date'] = $this->day($row['actual_end_date']);
            $row['status_label'] = ucfirst(str_replace('_', ' ', $row['status']));

            return $row;
        }, $payload['data']);

        return Inertia::render('Production/Schedule', [
            'orders' => $payload,
        ]);
    }

    // ============ Maintenance Advanced — Preventive Maintenance ============

    public function preventiveMaintenance(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('preventive_maintenance as pm')
            ->leftJoin('machines as m', 'm.id', '=', 'pm.machine_id')
            ->where('pm.tenant_id', $tenantId)
            ->select([
                'pm.id', 'pm.title', 'm.name as machine_name',
                'pm.schedule_type', 'pm.next_due_date', 'pm.last_done_date',
                'pm.status', 'pm.assigned_to',
            ])
            ->orderBy('pm.next_due_date')
            ->orderByDesc('pm.id');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['pm.title', 'm.name'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['next_due_date'] = $this->day($row['next_due_date']);
            $row['last_done_date'] = $this->day($row['last_done_date']);
            $row['status_label'] = ucfirst(str_replace('_', ' ', $row['status']));

            return $row;
        }, $payload['data']);

        $summary = DB::table('preventive_maintenance')
            ->where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'overdue') as overdue")
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'due_soon') as due_soon")
            ->first();

        return Inertia::render('Maintenance/Preventive', [
            'maintenance' => $payload,
            'pm_summary' => [
                'total' => (int) $summary->total,
                'overdue' => (int) $summary->overdue,
                'due_soon' => (int) $summary->due_soon,
            ],
        ]);
    }

    public function createPreventiveMaintenance(): Response
    {
        $machines = DB::table('machines')
            ->where('tenant_id', $this->tenantId())
            ->where('status', 'active')
            ->select(['id', 'machine_code', 'name'])
            ->orderBy('machine_code')
            ->get();

        return Inertia::render('Maintenance/CreatePreventive', [
            'machines' => $machines,
        ]);
    }

    // ============ HR Advanced — Time & Attendance ============

    public function timeAttendance(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('time_entries as te')
            ->leftJoin('employees as e', 'e.id', '=', 'te.employee_id')
            ->where('te.tenant_id', $tenantId)
            ->select([
                'te.id', 'te.entry_date', 'e.employee_code', 'e.first_name',
                'e.last_name', 'te.clock_in', 'te.clock_out',
                'te.hours_worked', 'te.status',
            ])
            ->orderByDesc('te.entry_date')
            ->orderByDesc('te.id');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['e.employee_code', 'e.first_name', 'e.last_name'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['entry_date'] = $this->day($row['entry_date']);
            $row['name'] = $row['first_name'] . ' ' . $row['last_name'];
            $row['status_label'] = ucfirst(str_replace('_', ' ', $row['status']));
            unset($row['first_name'], $row['last_name']);

            return $row;
        }, $payload['data']);

        return Inertia::render('HR/TimeAttendance', [
            'entries' => $payload,
        ]);
    }

    public function createTimeEntry(): Response
    {
        $employees = DB::table('employees')
            ->where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->select(['id', 'employee_code', 'first_name', 'last_name'])
            ->orderBy('employee_code')
            ->get();

        return Inertia::render('HR/CreateTimeEntry', [
            'employees' => $employees,
        ]);
    }

    // ============ Analytics Dashboard ============

    public function analytics(): Response
    {
        $tenantId = $this->tenantId();

        $productionToday = (float) DB::table('production_orders')
            ->where('tenant_id', $tenantId)
            ->whereDate('actual_end_date', today())
            ->whereIn('status', ['completed', 'closed'])
            ->sum('good_quantity');

        $revenueMonth = (float) DB::table('customer_invoices')
            ->where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->whereMonth('invoice_date', now()->month)
            ->whereYear('invoice_date', now()->year)
            ->sum('total_amount');

        $ordersDelayed = (int) DB::table('production_orders')
            ->where('tenant_id', $tenantId)
            ->whereNotNull('planned_end_date')
            ->where('planned_end_date', '<', now())
            ->whereNotIn('status', ['completed', 'closed', 'cancelled'])
            ->count();

        $stockValue = (float) DB::table('stock_balances as sb')
            ->join('items as i', 'i.id', '=', 'sb.item_id')
            ->where('sb.tenant_id', $tenantId)
            ->selectRaw('COALESCE(SUM(sb.on_hand * i.default_purchase_price), 0) as value')
            ->value('value');

        $qualityRejectionRate = 0.0;
        $inspected = (int) DB::table('quality_inspections')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['passed', 'failed', 'rejected'])
            ->count();
        $rejected = (int) DB::table('quality_inspections')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['failed', 'rejected'])
            ->count();
        if ($inspected > 0) {
            $qualityRejectionRate = round(($rejected / $inspected) * 100, 1);
        }

        // Monthly production trend (last 6 months)
        $monthlyProduction = DB::table('production_orders')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['completed', 'closed'])
            ->whereDate('actual_end_date', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw('DATE_TRUNC(\'month\', actual_end_date) as month, SUM(good_quantity) as qty')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Production by status
        $productionByStatus = DB::table('production_orders')
            ->where('tenant_id', $tenantId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        return Inertia::render('Analytics', [
            'production_today' => $productionToday,
            'revenue_month' => $revenueMonth,
            'orders_delayed' => $ordersDelayed,
            'stock_value' => $stockValue,
            'quality_rejection_rate' => $qualityRejectionRate,
            'monthly_production' => $monthlyProduction,
            'production_by_status' => $productionByStatus,
        ]);
    }

    // ============ MRP — Material Requirements Planning ============

    public function mrp(): Response
    {
        $tenantId = $this->tenantId();

        $items = DB::table('items as i')
            ->leftJoin('stock_balances as sb', function ($join) use ($tenantId) {
                $join->on('sb.item_id', '=', 'i.id')
                    ->where('sb.tenant_id', $tenantId);
            })
            ->leftJoin('item_categories as c', 'c.id', '=', 'i.category_id')
            ->where('i.tenant_id', $tenantId)
            ->select([
                'i.id', 'i.sku', 'i.name', 'i.reorder_level',
                'c.name as category_name',
                DB::raw('COALESCE(SUM(sb.on_hand), 0) as on_hand'),
            ])
            ->groupBy('i.id', 'i.sku', 'i.name', 'i.reorder_level', 'c.name')
            ->orderBy('i.sku')
            ->get();

        $mrpItems = $items->map(function ($item) {
            $onHand = (float) $item->on_hand;
            $reorderLevel = (float) $item->reorder_level;
            $needsReorder = $onHand < $reorderLevel;
            $shortfall = $needsReorder ? max(0, $reorderLevel - $onHand) : 0;

            return [
                'sku' => $item->sku,
                'name' => $item->name,
                'category' => $item->category_name ?? '—',
                'on_hand' => $onHand,
                'reorder_level' => $reorderLevel,
                'needs_reorder' => $needsReorder,
                'shortfall' => $shortfall,
                'priority' => $needsReorder ? ($shortfall > $reorderLevel ? 'critical' : 'high') : 'normal',
            ];
        });

        $summary = [
            'total_items' => $mrpItems->count(),
            'needs_reorder' => $mrpItems->filter(fn ($i) => $i['needs_reorder'])->count(),
            'critical' => $mrpItems->filter(fn ($i) => $i['priority'] === 'critical')->count(),
        ];

        return Inertia::render('MRP/MRP', [
            'items' => $mrpItems,
            'summary' => $summary,
        ]);
    }

    public function runMRP(): Response
    {
        return Inertia::render('MRP/RunMRP');
    }

    // ============ Production — Work Orders ============

    public function workOrders(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('work_orders as wo')
            ->leftJoin('production_orders as po', 'po.id', '=', 'wo.production_order_id')
            ->leftJoin('boms as b', 'b.id', '=', 'po.bom_header_id')
            ->leftJoin('items as i', 'i.id', '=', 'b.item_id')
            ->where('wo.tenant_id', $tenantId)
            ->select([
                'wo.id',
                DB::raw("CONCAT('WO-', wo.id) as work_order_number"),
                'wo.status',
                'wo.started_at as planned_start_date',
                'wo.completed_at as planned_end_date',
                'wo.started_at as actual_start_date',
                'wo.completed_at as actual_end_date',
                'wo.planned_quantity as quantity',
                'wo.actual_quantity as completed_quantity',
                'po.order_number as production_order',
                'i.name as item_name',
            ])
            ->orderByDesc('wo.started_at')
            ->orderByDesc('wo.id');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['po.order_number', 'i.name'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['planned_start_date'] = $this->day($row['planned_start_date']);
            $row['planned_end_date'] = $this->day($row['planned_end_date']);
            $row['actual_start_date'] = $this->day($row['actual_start_date']);
            $row['actual_end_date'] = $this->day($row['actual_end_date']);
            $row['status_label'] = ucfirst(str_replace('_', ' ', $row['status'] ?? 'draft'));
            $qty = (float) ($row['quantity'] ?? 0);
            $done = (float) ($row['completed_quantity'] ?? 0);
            $row['completion_pct'] = $qty > 0 ? round(($done / $qty) * 100, 1) : 0;

            return $row;
        }, $payload['data']);

        $summary = DB::table('work_orders')
            ->where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'in_progress') as in_progress")
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'completed') as completed")
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'delayed') as delayed")
            ->first();

        return Inertia::render('Production/WorkOrders', [
            'work_orders' => $payload,
            'wo_summary' => [
                'total' => (int) $summary->total,
                'in_progress' => (int) $summary->in_progress,
                'completed' => (int) $summary->completed,
                'delayed' => (int) $summary->delayed,
            ],
        ]);
    }

    public function createWorkOrder(): Response
    {
        $productionOrders = DB::table('production_orders as po')
            ->leftJoin('items as i', 'i.id', '=', 'po.item_id')
            ->where('po.tenant_id', $this->tenantId())
            ->whereIn('po.status', ['approved', 'material_reserved'])
            ->select(['po.id', 'po.order_number', 'i.name as item_name'])
            ->orderByDesc('po.id')
            ->limit(50)
            ->get();

        $workCentres = DB::table('work_centres')
            ->where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->select(['id', 'name', 'code'])
            ->orderBy('code')
            ->get();

        return Inertia::render('Production/CreateWorkOrder', [
            'production_orders' => $productionOrders,
            'work_centres' => $workCentres,
        ]);
    }

    public function workOrderOperations(string $id): Response
    {
        $operations = DB::table('work_order_operations as woo')
            ->leftJoin('routing_operations as ro', 'ro.id', '=', 'woo.routing_operation_id')
            ->where('woo.work_order_id', $id)
            ->select([
                'woo.id', 'woo.operation_name', 'woo.status',
                'woo.planned_start_date', 'woo.planned_end_date',
                'woo.actual_start_date', 'woo.actual_end_date',
                'ro.operation_code', 'ro.work_centre_name',
            ])
            ->orderBy('woo.planned_start_date')
            ->get();

        return Inertia::render('Production/WorkOrderOperations', [
            'work_order_id' => $id,
            'operations' => $operations,
        ]);
    }

    // ============ Quality — Non-Conformance Reports ============

    public function nonConformanceReports(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('quality_non_conformance as qnc')
            ->leftJoin('items as i', 'i.id', '=', 'qnc.item_id')
            ->leftJoin('employees as e', 'e.id', '=', 'qnc.reported_by')
            ->where('qnc.tenant_id', $tenantId)
            ->select([
                'qnc.id', 'qnc.ncr_number', 'qnc.title', 'qnc.description',
                'qnc.severity', 'qnc.status', 'qnc.reported_date',
                'qnc.due_date', 'qnc.closed_date',
                'i.name as item_name',
                'e.first_name', 'e.last_name',
            ])
            ->orderByDesc('qnc.reported_date')
            ->orderByDesc('qnc.id');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['qnc.ncr_number', 'qnc.title', 'i.name'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['reported_date'] = $this->day($row['reported_date']);
            $row['due_date'] = $this->day($row['due_date']);
            $row['closed_date'] = $this->day($row['closed_date']);
            $row['reported_by'] = $row['first_name'] . ' ' . $row['last_name'];
            $row['severity_label'] = ucfirst(str_replace('_', ' ', $row['severity']));
            $row['status_label'] = ucfirst(str_replace('_', ' ', $row['status']));
            unset($row['first_name'], $row['last_name']);

            return $row;
        }, $payload['data']);

        $summary = DB::table('quality_non_conformance')
            ->where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'open') as open")
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'closed') as closed")
            ->selectRaw("COUNT(*) FILTER (WHERE severity = 'critical') as critical")
            ->first();

        return Inertia::render('Quality/NCR', [
            'ncr_reports' => $payload,
            'ncr_summary' => [
                'total' => (int) $summary->total,
                'open' => (int) $summary->open,
                'closed' => (int) $summary->closed,
                'critical' => (int) $summary->critical,
            ],
        ]);
    }

    public function createNonConformanceReport(): Response
    {
        $items = DB::table('items')
            ->where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->select(['id', 'sku', 'name'])
            ->orderBy('sku')
            ->limit(100)
            ->get();

        $employees = DB::table('employees')
            ->where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->select(['id', 'employee_code', 'first_name', 'last_name'])
            ->orderBy('employee_code')
            ->get();

        return Inertia::render('Quality/CreateNCR', [
            'items' => $items,
            'employees' => $employees,
        ]);
    }

    // ============ Quality — CAPA ============

    public function capa(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('quality_capa as qc')
            ->leftJoin('quality_non_conformance as qnc', 'qnc.id', '=', 'qc.ncr_id')
            ->leftJoin('employees as e', 'e.id', '=', 'qc.assigned_to')
            ->where('qc.tenant_id', $tenantId)
            ->select([
                'qc.id', 'qc.capa_number', 'qc.title', 'qc.description',
                'qc.type', 'qc.status', 'qc.due_date', 'qc.closed_date',
                'qnc.ncr_number',
                'e.first_name', 'e.last_name',
            ])
            ->orderByDesc('qc.created_at')
            ->orderByDesc('qc.id');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['qc.capa_number', 'qc.title', 'qnc.ncr_number'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['due_date'] = $this->day($row['due_date']);
            $row['closed_date'] = $this->day($row['closed_date']);
            $row['assigned_to'] = $row['first_name'] . ' ' . $row['last_name'];
            $row['type_label'] = ucfirst(str_replace('_', ' ', $row['type']));
            $row['status_label'] = ucfirst(str_replace('_', ' ', $row['status']));
            unset($row['first_name'], $row['last_name']);

            return $row;
        }, $payload['data']);

        $summary = DB::table('quality_capa')
            ->where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'open') as open")
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'closed') as closed")
            ->first();

        return Inertia::render('Quality/CAPA', [
            'capa_reports' => $payload,
            'capa_summary' => [
                'total' => (int) $summary->total,
                'open' => (int) $summary->open,
                'closed' => (int) $summary->closed,
            ],
        ]);
    }

    public function createCAPA(): Response
    {
        $ncrs = DB::table('quality_non_conformance')
            ->where('tenant_id', $this->tenantId())
            ->where('status', 'open')
            ->select(['id', 'ncr_number', 'title'])
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $employees = DB::table('employees')
            ->where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->select(['id', 'employee_code', 'first_name', 'last_name'])
            ->orderBy('employee_code')
            ->get();

        return Inertia::render('Quality/CreateCAPA', [
            'ncrs' => $ncrs,
            'employees' => $employees,
        ]);
    }

    // ============ Accounting — Fixed Assets ============

    public function fixedAssets(): Response
    {
        $tenantId = $this->tenantId();

        $hasAssetName = Schema::hasColumn('fixed_assets', 'asset_name');
        $nameCol = $hasAssetName ? 'asset_name' : 'name';
        $categoryCol = $hasAssetName ? 'asset_category' : 'category';
        $dateCol = $hasAssetName ? 'purchase_date' : 'acquisition_date';
        $costCol = $hasAssetName ? 'purchase_cost' : 'acquisition_cost';
        $hasLocation = Schema::hasColumn('fixed_assets', 'location');
        $hasAccount = Schema::hasColumn('fixed_assets', 'account_id');

        $query = DB::table('fixed_assets as fa')
            ->where('fa.tenant_id', $tenantId);

        $select = [
            'fa.id',
            'fa.asset_code',
            "fa.{$nameCol} as asset_name",
            "fa.{$categoryCol} as asset_category",
            "fa.{$dateCol} as purchase_date",
            "fa.{$costCol} as purchase_cost",
            'fa.accumulated_depreciation',
            'fa.status',
        ];
        if ($hasLocation) $select[] = 'fa.location';
        if ($hasAccount) $query->leftJoin('chart_of_accounts as coa', 'coa.id', '=', 'fa.account_id');
        if ($hasAccount) $select[] = 'fa.account_id';
        if ($hasAccount) $select[] = 'coa.name as account_name';

        $query->select($select)->orderBy('fa.asset_code');

        $searchColumns = ['fa.asset_code', "fa.{$nameCol}"];
        if ($hasLocation) $searchColumns[] = 'fa.location';

        $payload = $this->paginate($this->search($query, request('q'), $searchColumns));

        $payload['data'] = array_map(function (array $row) {
            $cost = (float) ($row['purchase_cost'] ?? 0);
            $accumulated = (float) ($row['accumulated_depreciation'] ?? 0);

            $row['purchase_date'] = $this->day($row['purchase_date'] ?? null);
            $row['purchase_cost'] = $this->money($cost);
            $row['accumulated_depreciation'] = $this->money($accumulated);
            $row['net_book_value'] = $this->money($cost - $accumulated);
            $row['status_label'] = ucfirst(str_replace('_', ' ', (string) ($row['status'] ?? '')));
            $row['location'] = $row['location'] ?? null;

            return $row;
        }, $payload['data']);

        $summary = DB::table('fixed_assets')
            ->where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COALESCE(SUM({$costCol}), 0) as total_cost")
            ->selectRaw("COALESCE(SUM({$costCol} - accumulated_depreciation), 0) as total_nbv")
            ->first();

        return Inertia::render('Accounting/FixedAssets', [
            'assets' => $payload,
            'asset_summary' => [
                'total' => (int) $summary->total,
                'total_cost' => $this->money($summary->total_cost),
                'total_nbv' => $this->money($summary->total_nbv),
            ],
        ]);
    }

    public function createFixedAsset(): Response
    {
        $accounts = DB::table('chart_of_accounts')
            ->where('tenant_id', $this->tenantId())
            ->where('type', 'asset')
            ->select(['id', 'code', 'name'])
            ->orderBy('code')
            ->get();

        return Inertia::render('Accounting/CreateFixedAsset', [
            'accounts' => $accounts,
        ]);
    }

    public function storeFixedAsset(Request $request)
    {
        $tenantId = $this->tenantId();

        $validated = $request->validate([
            'asset_code' => ['required', 'string', 'max:60'],
            'asset_name' => ['required', 'string', 'max:200'],
            'asset_category' => ['nullable', 'string', 'max:100'],
            'account_id' => ['nullable', 'integer'],
            'purchase_date' => ['required', 'date'],
            'purchase_cost' => ['required', 'numeric', 'min:0'],
            'salvage_value' => ['nullable', 'numeric', 'min:0'],
            'useful_life_years' => ['required', 'integer', 'min:1', 'max:60'],
            'location' => ['nullable', 'string', 'max:200'],
            'status' => ['nullable', 'in:active,disposed,retired'],
        ]);

        $code = trim((string) $validated['asset_code']);
        $exists = DB::table('fixed_assets')
            ->where('tenant_id', $tenantId)
            ->where('asset_code', $code)
            ->exists();
        if ($exists) {
            return back()->withErrors(['asset_code' => 'This asset code is already in use.'])->withInput();
        }

        $companyId = (int) DB::table('companies')->where('tenant_id', $tenantId)->orderBy('id')->value('id');
        $factoryId = (int) DB::table('factories')->where('tenant_id', $tenantId)->orderBy('id')->value('id');
        if (!$companyId) {
            return back()->withErrors(['asset_code' => 'Set up a company for this tenant before creating fixed assets.'])->withInput();
        }

        $hasAssetName = Schema::hasColumn('fixed_assets', 'asset_name');
        $hasLocation = Schema::hasColumn('fixed_assets', 'location');
        $hasAccount = Schema::hasColumn('fixed_assets', 'account_id');
        $hasFactory = Schema::hasColumn('fixed_assets', 'factory_id');
        $hasNetBook = Schema::hasColumn('fixed_assets', 'net_book_value');
        $hasSalvage = Schema::hasColumn('fixed_assets', 'salvage_value');
        $hasLife = Schema::hasColumn('fixed_assets', 'useful_life_years');

        if ($hasFactory && !$factoryId) {
            return back()->withErrors(['asset_code' => 'Set up a factory for this tenant before creating fixed assets.'])->withInput();
        }

        $accountId = (int) ($validated['account_id'] ?? 0);
        if ($accountId > 0) {
            $validAccount = DB::table('chart_of_accounts')
                ->where('tenant_id', $tenantId)
                ->where('type', 'asset')
                ->where('id', $accountId)
                ->exists();
            if (!$validAccount) {
                return back()->withErrors(['account_id' => 'Select a valid asset account.'])->withInput();
            }
        }
        if ($hasAccount && !$accountId) {
            $accountId = (int) DB::table('chart_of_accounts')
                ->where('tenant_id', $tenantId)
                ->where('type', 'asset')
                ->orderBy('code')
                ->value('id');
            if (!$accountId) {
                return back()->withErrors(['account_id' => 'Create at least one asset account in the Chart of Accounts first.'])->withInput();
            }
        }

        $cost = round((float) $validated['purchase_cost'], 2);
        $salvage = round((float) ($validated['salvage_value'] ?? 0), 2);
        $life = (int) $validated['useful_life_years'];
        $purchaseDate = Carbon::parse($validated['purchase_date']);
        $status = $validated['status'] ?? 'active';
        $category = trim((string) ($validated['asset_category'] ?? '')) ?: 'General';

        $data = [
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'asset_code' => $code,
            'accumulated_depreciation' => 0,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if ($hasFactory && $factoryId) $data['factory_id'] = $factoryId;
        if ($hasLocation) $data['location'] = $validated['location'] ?? null;
        if ($hasAccount) $data['account_id'] = $accountId;
        if ($hasSalvage) $data['salvage_value'] = $salvage;
        if ($hasLife) $data['useful_life_years'] = $life;
        if ($hasNetBook) $data['net_book_value'] = $cost;

        if ($hasAssetName) {
            $data['asset_name'] = $validated['asset_name'];
            $data['asset_category'] = $category;
            $data['purchase_date'] = $purchaseDate->toDateString();
            $data['purchase_cost'] = $cost;
        } else {
            $data['name'] = $validated['asset_name'];
            $data['category'] = $category;
            $data['acquisition_date'] = $purchaseDate->toDateString();
            $data['acquisition_cost'] = $cost;
        }

        $assetId = DB::transaction(function () use ($data, $tenantId, $cost, $salvage, $life, $purchaseDate) {
            $assetId = DB::table('fixed_assets')->insertGetId($data);

            if (Schema::hasTable('fixed_asset_depreciation') && $life > 0) {
                $depreciable = max(0, $cost - $salvage);
                $annual = $depreciable > 0 ? round($depreciable / $life, 2) : 0;
                if ($annual > 0) {
                    $accumulated = 0.0;
                    $rows = [];
                    for ($year = 1; $year <= $life; $year++) {
                        $start = $purchaseDate->copy()->addYears($year - 1)->startOfDay();
                        $end = $start->copy()->addYear()->subDay();
                        $amount = $year === $life ? round($depreciable - $accumulated, 2) : $annual;
                        $accumulated = round($accumulated + $amount, 2);

                        $rows[] = [
                            'tenant_id' => $tenantId,
                            'fixed_asset_id' => $assetId,
                            'period_start' => $start->toDateString(),
                            'period_end' => $end->toDateString(),
                            'depreciation_amount' => $amount,
                            'accumulated_depreciation' => $accumulated,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    DB::table('fixed_asset_depreciation')->insert($rows);
                }
            }

            return $assetId;
        });

        return redirect()->route('accounting.fixed-assets')
            ->with('success', 'Fixed asset ' . $code . ' created successfully with depreciation schedule.');
    }

    public function fixedAssetShow(string $id): Response
    {
        $tenantId = $this->tenantId();

        $hasAssetName = Schema::hasColumn('fixed_assets', 'asset_name');
        $nameCol = $hasAssetName ? 'asset_name' : 'name';
        $categoryCol = $hasAssetName ? 'asset_category' : 'category';
        $dateCol = $hasAssetName ? 'purchase_date' : 'acquisition_date';
        $costCol = $hasAssetName ? 'purchase_cost' : 'acquisition_cost';
        $hasLocation = Schema::hasColumn('fixed_assets', 'location');
        $hasAccount = Schema::hasColumn('fixed_assets', 'account_id');
        $hasSalvage = Schema::hasColumn('fixed_assets', 'salvage_value');
        $hasLife = Schema::hasColumn('fixed_assets', 'useful_life_years');

        $query = DB::table('fixed_assets as fa')->where('fa.tenant_id', $tenantId)->where('fa.id', $id);
        $select = [
            'fa.id', 'fa.asset_code', "fa.{$nameCol} as asset_name", "fa.{$categoryCol} as asset_category",
            "fa.{$dateCol} as purchase_date", "fa.{$costCol} as purchase_cost", 'fa.accumulated_depreciation', 'fa.status',
        ];
        if ($hasLocation) $select[] = 'fa.location';
        if ($hasSalvage) $select[] = 'fa.salvage_value';
        if ($hasLife) $select[] = 'fa.useful_life_years';
        if ($hasAccount) {
            $query->leftJoin('chart_of_accounts as coa', 'coa.id', '=', 'fa.account_id');
            $select[] = 'fa.account_id';
            $select[] = 'coa.code as account_code';
            $select[] = 'coa.name as account_name';
        }
        $asset = $query->select($select)->first();
        abort_if(!$asset, 404);

        $cost = (float) ($asset->purchase_cost ?? 0);
        $accumulated = (float) ($asset->accumulated_depreciation ?? 0);

        $schedule = collect();
        if (Schema::hasTable('fixed_asset_depreciation')) {
            $schedule = DB::table('fixed_asset_depreciation')
                ->where('tenant_id', $tenantId)
                ->where('fixed_asset_id', $id)
                ->orderBy('period_start')
                ->get()
                ->map(fn ($r) => [
                    'id' => $r->id,
                    'period_start' => $r->period_start ? Carbon::parse($r->period_start)->format('d M Y') : null,
                    'period_end' => $r->period_end ? Carbon::parse($r->period_end)->format('d M Y') : null,
                    'depreciation_amount' => (float) $r->depreciation_amount,
                    'accumulated_depreciation' => (float) $r->accumulated_depreciation,
                    'net_book_value' => max(0, $cost - (float) $r->accumulated_depreciation),
                ])
                ->values();
        }

        return Inertia::render('Accounting/FixedAssetShow', [
            'asset' => [
                'id' => $asset->id,
                'asset_code' => $asset->asset_code,
                'asset_name' => $asset->asset_name,
                'asset_category' => $asset->asset_category,
                'purchase_date' => $asset->purchase_date ? Carbon::parse($asset->purchase_date)->format('d M Y') : null,
                'purchase_cost' => $cost,
                'salvage_value' => (float) ($asset->salvage_value ?? 0),
                'useful_life_years' => (int) ($asset->useful_life_years ?? 0),
                'accumulated_depreciation' => $accumulated,
                'net_book_value' => $cost - $accumulated,
                'status' => $asset->status,
                'location' => $asset->location ?? null,
                'account_id' => $asset->account_id ?? null,
                'account_code' => $asset->account_code ?? null,
                'account_name' => $asset->account_name ?? null,
            ],
            'schedule' => $schedule,
        ]);
    }

    // ============ HR — Leave Management ============

    public function leaveManagement(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('leave_requests as lr')
            ->leftJoin('employees as e', 'e.id', '=', 'lr.employee_id')
            ->where('lr.tenant_id', $tenantId)
            ->select([
                'lr.id', 'lr.leave_number', 'e.employee_code', 'e.first_name', 'e.last_name',
                'lr.leave_type', 'lr.start_date', 'lr.end_date', 'lr.status',
                'lr.approved_by', 'lr.created_at',
            ])
            ->orderByDesc('lr.created_at')
            ->orderByDesc('lr.id');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['lr.leave_number', 'e.employee_code'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['start_date'] = $this->day($row['start_date']);
            $row['end_date'] = $this->day($row['end_date']);
            $row['employee_name'] = $row['first_name'] . ' ' . $row['last_name'];
            $row['leave_type_label'] = ucfirst(str_replace('_', ' ', $row['leave_type']));
            $row['status_label'] = ucfirst(str_replace('_', ' ', $row['status']));
            unset($row['first_name'], $row['last_name']);

            return $row;
        }, $payload['data']);

        $summary = DB::table('leave_requests')
            ->where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'pending') as pending")
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'approved') as approved")
            ->first();

        return Inertia::render('HR/Leave', [
            'leave_requests' => $payload,
            'leave_summary' => [
                'total' => (int) $summary->total,
                'pending' => (int) $summary->pending,
                'approved' => (int) $summary->approved,
            ],
        ]);
    }

    public function createLeaveRequest(): Response
    {
        $employees = DB::table('employees')
            ->where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->select(['id', 'employee_code', 'first_name', 'last_name'])
            ->orderBy('employee_code')
            ->get();

        return Inertia::render('HR/CreateLeave', [
            'employees' => $employees,
        ]);
    }

    // ============ HR — Shift Management ============

    public function shiftManagement(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('shifts as s')
            ->leftJoin('employees as e', 'e.id', '=', 's.employee_id')
            ->where('s.tenant_id', $tenantId)
            ->select([
                's.id', 's.shift_name', 's.shift_date', 's.start_time', 's.end_time',
                's.status', 's.location',
                'e.employee_code', 'e.first_name', 'e.last_name',
            ])
            ->orderBy('s.shift_date')
            ->orderByDesc('s.id');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['s.shift_name', 'e.employee_code', 's.location'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['shift_date'] = $this->day($row['shift_date']);
            $row['employee_name'] = $row['first_name'] . ' ' . $row['last_name'];
            $row['status_label'] = ucfirst(str_replace('_', ' ', $row['status']));
            unset($row['first_name'], $row['last_name']);

            return $row;
        }, $payload['data']);

        return Inertia::render('HR/Shifts', [
            'shifts' => $payload,
        ]);
    }

    public function createShift(): Response
    {
        $employees = DB::table('employees')
            ->where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->select(['id', 'employee_code', 'first_name', 'last_name'])
            ->orderBy('employee_code')
            ->get();

        return Inertia::render('HR/CreateShift', [
            'employees' => $employees,
        ]);
    }

    // ============ Purchasing — Vendor Scorecards ============

    public function vendorScorecards(): Response
    {
        $tenantId = $this->tenantId();

        $suppliers = DB::table('suppliers')
            ->where('tenant_id', $tenantId)
            ->select(['id', 'name', 'code', 'contact_person', 'email'])
            ->orderBy('name')
            ->get();

        $scorecards = $suppliers->map(function ($supplier) use ($tenantId) {
            $poCount = (int) DB::table('purchase_orders')
                ->where('tenant_id', $tenantId)
                ->where('supplier_id', $supplier->id)
                ->count();

            $onTime = (int) DB::table('purchase_orders')
                ->where('tenant_id', $tenantId)
                ->where('supplier_id', $supplier->id)
                ->where('status', 'completed')
                ->count();

            $avgRating = $poCount > 0 ? round(($onTime / $poCount) * 100, 1) : 0;

            $totalSpent = (float) DB::table('supplier_invoices')
                ->where('tenant_id', $tenantId)
                ->where('supplier_id', $supplier->id)
                ->where('status', 'paid')
                ->sum('total_amount');

            $qualityIssues = (int) DB::table('quality_inspections')
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['failed', 'rejected'])
                ->count();

            return [
                'id' => $supplier->id,
                'code' => $supplier->code,
                'name' => $supplier->name,
                'contact_person' => $supplier->contact_person,
                'email' => $supplier->email,
                'po_count' => $poCount,
                'on_time_rate' => $avgRating,
                'total_spent' => $this->money($totalSpent),
                'quality_issues' => $qualityIssues,
                'score' => $avgRating >= 90 ? 'A' : ($avgRating >= 75 ? 'B' : ($avgRating >= 60 ? 'C' : 'D')),
            ];
        });

        return Inertia::render('Purchasing/VendorScorecards', [
            'scorecards' => $scorecards,
        ]);
    }

    // ============ Documents — Document Management ============

    public function documents(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('documents as d')
            ->leftJoin('employees as e', 'e.id', '=', 'd.uploaded_by')
            ->where('d.tenant_id', $tenantId)
            ->select([
                'd.id', 'd.document_number', 'd.title', 'd.category',
                'd.file_name', 'd.file_size', 'd.version',
                'd.status', 'd.upload_date', 'd.expiry_date',
                'e.first_name', 'e.last_name',
            ])
            ->orderByDesc('d.upload_date')
            ->orderByDesc('d.id');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['d.document_number', 'd.title', 'd.file_name'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['upload_date'] = $this->day($row['upload_date']);
            $row['expiry_date'] = $this->day($row['expiry_date']);
            $row['uploaded_by'] = $row['first_name'] . ' ' . $row['last_name'];
            $row['category_label'] = ucfirst(str_replace('_', ' ', $row['category']));
            $row['status_label'] = ucfirst(str_replace('_', ' ', $row['status']));
            unset($row['first_name'], $row['last_name']);

            return $row;
        }, $payload['data']);

        return Inertia::render('Documents/Documents', [
            'documents' => $payload,
        ]);
    }

    public function createDocument(): Response
    {
        $employees = DB::table('employees')
            ->where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->select(['id', 'employee_code', 'first_name', 'last_name'])
            ->orderBy('employee_code')
            ->get();

        return Inertia::render('Documents/CreateDocument', [
            'employees' => $employees,
        ]);
    }

    // ============ Reporting — Custom Reports ============

    public function customReports(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('custom_reports as cr')
            ->leftJoin('employees as e', 'e.id', '=', 'cr.created_by')
            ->where('cr.tenant_id', $tenantId)
            ->select([
                'cr.id', 'cr.report_name', 'cr.report_type', 'cr.status',
                'cr.frequency', 'cr.last_run', 'cr.next_run',
                'e.first_name', 'e.last_name',
            ])
            ->orderByDesc('cr.created_at')
            ->orderByDesc('cr.id');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['cr.report_name', 'cr.report_type'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['last_run'] = $this->day($row['last_run']);
            $row['next_run'] = $this->day($row['next_run']);
            $row['created_by'] = $row['first_name'] . ' ' . $row['last_name'];
            $row['report_type_label'] = ucfirst(str_replace('_', ' ', $row['report_type']));
            $row['status_label'] = ucfirst(str_replace('_', ' ', $row['status']));
            $row['frequency_label'] = ucfirst(str_replace('_', ' ', $row['frequency']));
            unset($row['first_name'], $row['last_name']);

            return $row;
        }, $payload['data']);

        return Inertia::render('Reports/CustomReports', [
            'reports' => $payload,
        ]);
    }

    public function createCustomReport(): Response
    {
        $employees = DB::table('employees')
            ->where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->select(['id', 'employee_code', 'first_name', 'last_name'])
            ->orderBy('employee_code')
            ->get();

        return Inertia::render('Reports/CreateCustomReport', [
            'employees' => $employees,
        ]);
    }

    // ============ Notifications — Real-time with Sound ============

    public function notifications(Request $request): Response
    {
        $tenantId = $this->tenantId();
        $q = trim((string) $request->query('q', ''));
        $type = $request->query('type', 'all');
        $status = $request->query('status', 'all');

        try {
            $query = DB::table('notifications as n')
                ->leftJoin('users as u', 'u.id', '=', 'n.user_id')
                ->where('n.tenant_id', $tenantId)
                ->select([
                    'n.id', 'n.title', 'n.type', 'n.data',
                    DB::raw("COALESCE(n.body, n.message, '') as body"),
                    'n.read_at', DB::raw('COALESCE(n.is_read, false) as is_read_flag'), 'n.created_at',
                    'u.name as actor_name',
                ])
                ->orderByDesc('n.created_at');

            if ($q !== '') {
                $query->where(function ($nested) use ($q) {
                    $nested->where('n.title', 'ilike', "%{$q}%")
                        ->orWhere(DB::raw("COALESCE(n.body, n.message, '')"), 'ilike', "%{$q}%")
                        ->orWhere('u.name', 'ilike', "%{$q}%");
                });
            }
            if ($type && $type !== 'all') $query->where('n.type', $type);
            if ($status === 'unread') $query->whereNull('n.read_at')->where(function ($n2) { $n2->whereNull('n.is_read')->orWhere('n.is_read', false); });
            elseif ($status === 'read') $query->where(function ($nested) { $nested->whereNotNull('n.read_at')->orWhere('n.is_read', true); });

            $payload = $this->paginate($query);
            $payload['data'] = array_map(function (array $row) {
                $row['is_read'] = !is_null($row['read_at']) || (bool) ($row['is_read_flag'] ?? false);
                unset($row['is_read_flag']);
                $row['created_at'] = $row['created_at'] ? Carbon::parse($row['created_at'])->format('d M Y, H:i') : null;
                $row['data'] = is_string($row['data']) ? (json_decode($row['data'], true) ?: []) : ($row['data'] ?? []);
                $row['actor_name'] = $row['actor_name'] ?? 'System';
                $row['type'] = $row['type'] ?? 'info';
                $row['body'] = $row['body'] ?? '';
                return $row;
            }, $payload['data']);

            $base = DB::table('notifications')->where('tenant_id', $tenantId);
            $unreadCount = (int) (clone $base)->whereNull('read_at')->where(function ($n2) { $n2->whereNull('is_read')->orWhere('is_read', false); })->count();
            $totalCount = (int) (clone $base)->count();
            $byType = (clone $base)->select('type', DB::raw('COUNT(*) as count'))->groupBy('type')->get()->mapWithKeys(fn ($r) => [($r->type ?? 'info') => (int) $r->count])->all();

            return Inertia::render('Notifications', [
                'notifications' => $payload,
                'unread_count' => $unreadCount,
                'notification_summary' => ['total' => $totalCount, 'unread' => $unreadCount, 'read' => max(0, $totalCount - $unreadCount), 'by_type' => $byType],
                'filters' => ['q' => $q, 'type' => $type ?: 'all', 'status' => $status ?: 'all'],
            ]);
        } catch (\Throwable $e) {
            // Fallback for schema variants (e.g. body vs message, no is_read column)
            $query = DB::table('notifications as n')
                ->leftJoin('users as u', 'u.id', '=', 'n.user_id')
                ->where('n.tenant_id', $tenantId)
                ->select(['n.id', 'n.title', 'n.type', 'n.data', 'n.body', 'n.message', 'n.read_at', 'n.created_at', 'u.name as actor_name'])
                ->orderByDesc('n.created_at');

            if ($q !== '') {
                $query->where(function ($nested) use ($q) {
                    $nested->where('n.title', 'ilike', "%{$q}%")
                        ->orWhere('n.body', 'ilike', "%{$q}%")
                        ->orWhere('n.message', 'ilike', "%{$q}%")
                        ->orWhere('u.name', 'ilike', "%{$q}%");
                });
            }
            if ($type && $type !== 'all') $query->where('n.type', $type);
            if ($status === 'unread') $query->whereNull('n.read_at');
            elseif ($status === 'read') $query->whereNotNull('n.read_at');

            $payload = $this->paginate($query);
            $payload['data'] = array_map(function (array $row) {
                $row['body'] = $row['body'] ?? $row['message'] ?? '';
                unset($row['message']);
                $row['is_read'] = !is_null($row['read_at']);
                $row['created_at'] = $row['created_at'] ? Carbon::parse($row['created_at'])->format('d M Y, H:i') : null;
                $row['data'] = is_string($row['data']) ? (json_decode($row['data'], true) ?: []) : ($row['data'] ?? []);
                $row['actor_name'] = $row['actor_name'] ?? 'System';
                $row['type'] = $row['type'] ?? 'info';
                return $row;
            }, $payload['data']);

            $base = DB::table('notifications')->where('tenant_id', $tenantId);
            $unreadCount = (int) (clone $base)->whereNull('read_at')->count();
            $totalCount = (int) (clone $base)->count();
            $byType = (clone $base)->select('type', DB::raw('COUNT(*) as count'))->groupBy('type')->get()->mapWithKeys(fn ($r) => [($r->type ?? 'info') => (int) $r->count])->all();

            return Inertia::render('Notifications', [
                'notifications' => $payload,
                'unread_count' => $unreadCount,
                'notification_summary' => ['total' => $totalCount, 'unread' => $unreadCount, 'read' => max(0, $totalCount - $unreadCount), 'by_type' => $byType],
                'filters' => ['q' => $q, 'type' => $type ?: 'all', 'status' => $status ?: 'all'],
            ]);
        }
    }

    public function deleteNotification(Request $request, string $id)
    {
        $tenantId = $this->tenantId();
        $deleted = DB::table('notifications')->where('tenant_id', $tenantId)->where('id', $id)->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => (bool) $deleted]);
        }

        return back()->with('success', 'Notification deleted.');
    }

    public function deleteAllReadNotifications(Request $request)
    {
        $tenantId = $this->tenantId();
        $count = DB::table('notifications')
            ->where('tenant_id', $tenantId)
            ->where(function ($nested) {
                $nested->whereNotNull('read_at')->orWhere('is_read', true);
            })
            ->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'deleted' => $count]);
        }

        return back()->with('success', "Deleted {$count} read notifications.");
    }

    public function exportNotifications(Request $request)
    {
        $tenantId = $this->tenantId();
        try {
            $rows = DB::table('notifications as n')
                ->leftJoin('users as u', 'u.id', '=', 'n.user_id')
                ->where('n.tenant_id', $tenantId)
                ->select(['n.id', 'n.title', DB::raw("COALESCE(n.body, n.message, '') as body"), 'n.type', 'n.read_at', 'n.created_at', 'u.name as actor_name'])
                ->orderByDesc('n.created_at')
                ->get();
        } catch (\Throwable $e) {
            $rows = DB::table('notifications as n')
                ->leftJoin('users as u', 'u.id', '=', 'n.user_id')
                ->where('n.tenant_id', $tenantId)
                ->select(['n.id', 'n.title', 'n.body', 'n.type', 'n.read_at', 'n.created_at', 'u.name as actor_name'])
                ->orderByDesc('n.created_at')
                ->get();
        }

        $filename = 'notifications_' . now()->format('Ymd_His') . '.csv';
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Title', 'Message', 'Type', 'Status', 'Actor', 'Created At']);
            foreach ($rows as $r) {
                fputcsv($out, [$r->id, $r->title, $r->body ?? '', $r->type ?? 'info', !empty($r->read_at) ? 'read' : 'unread', $r->actor_name ?? 'System', $r->created_at]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function notificationUnreadCount(): Response
    {
        $tenantId = $this->tenantId();

        $count = (int) DB::table('notifications')
            ->where('tenant_id', $tenantId)
            ->whereNull('read_at')
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    public function markNotificationRead(string $id): Response
    {
        $tenantId = $this->tenantId();

        DB::table('notifications')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function markAllNotificationsRead(): Response
    {
        $tenantId = $this->tenantId();

        DB::table('notifications')
            ->where('tenant_id', $tenantId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    // ============ RBAC — Role Management ============

    public function roles(): Response
    {
        $tenantId = $this->tenantId();

        $roles = DB::table('roles')
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        return Inertia::render('RBAC/Roles', [
            'roles' => $roles,
        ]);
    }

    public function createRole(): Response
    {
        $permissions = DB::table('permissions')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return Inertia::render('RBAC/CreateRole', [
            'permissions' => $permissions,
        ]);
    }

    public function rolePermissions(string $roleId): Response
    {
        $role = DB::table('roles')->where('id', $roleId)->firstOrFail();
        $permissions = DB::table('role_permissions as rp')
            ->join('permissions as p', 'p.id', '=', 'rp.permission_id')
            ->where('rp.role_id', $roleId)
            ->select(['p.id', 'p.name', 'p.category'])
            ->get();

        return response()->json([
            'role' => $role,
            'permissions' => $permissions,
        ]);
    }

    public function usersWithRoles(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('users as u')
            ->leftJoin('roles as r', 'r.id', '=', 'u.role_id')
            ->where('u.tenant_id', $tenantId)
            ->select([
                'u.id', 'u.name', 'u.email', 'u.is_active',
                'r.name as role_name', 'r.id as role_id',
            ])
            ->orderBy('u.name');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['u.name', 'u.email', 'r.name'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['status'] = $row['is_active'] ? 'active' : 'inactive';
            unset($row['is_active']);

            return $row;
        }, $payload['data']);

        $availableRoles = DB::table('roles')
            ->where('tenant_id', $tenantId)
            ->select(['id', 'name', 'display_name'])
            ->orderBy('name')
            ->get();

        return Inertia::render('RBAC/Users', [
            'users' => $payload,
            'available_roles' => $availableRoles,
        ]);
    }

    public function storeRole(Request $request): Response
    {
        $tenantId = $this->tenantId();

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permission_ids' => 'array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        DB::transaction(function () use ($tenantId, $validated) {
            $roleId = DB::table('roles')->insertGetId([
                'tenant_id' => $tenantId,
                'name' => $validated['name'],
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Store display_name and description in a metadata column or separate table
            // For now, store as role name prefix if needed
            if (!empty($validated['permission_ids'])) {
                foreach ($validated['permission_ids'] as $permId) {
                    DB::table('role_has_permissions')->insert([
                        'role_id' => $roleId,
                        'permission_id' => $permId,
                    ]);
                }
            }
        });

        return redirect()->route('rbac.roles')->with('success', 'Role created successfully.');
    }

    public function editRole(string $id): Response
    {
        $role = DB::table('roles')->where('id', $id)->firstOrFail();
        $permissions = DB::table('permissions')
            ->orderBy('category')
            ->orderBy('name')
            ->get();
        $assigned = DB::table('role_has_permissions')
            ->where('role_id', $id)
            ->pluck('permission_id')
            ->toArray();

        return Inertia::render('RBAC/EditRole', [
            'role' => $role,
            'permissions' => $permissions,
            'assigned' => $assigned,
        ]);
    }

    public function updateRole(Request $request, string $id): Response
    {
        $role = DB::table('roles')->where('id', $id)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name,' . $id,
        ]);

        DB::table('roles')->where('id', $id)->update([
            'name' => $validated['name'],
            'updated_at' => now(),
        ]);

        return redirect()->route('rbac.roles')->with('success', 'Role updated successfully.');
    }

    public function deleteRole(string $id): Response
    {
        $role = DB::table('roles')->where('id', $id)->firstOrFail();

        // Check if any users have this role via model_has_roles
        $userCount = DB::table('model_has_roles')->where('role_id', $id)->count();
        if ($userCount > 0) {
            return response()->json(['success' => false, 'message' => 'Cannot delete role assigned to users.'], 403);
        }

        DB::transaction(function () use ($id) {
            DB::table('role_has_permissions')->where('role_id', $id)->delete();
            DB::table('roles')->where('id', $id)->delete();
        });

        return response()->json(['success' => true]);
    }

    public function updateRolePermissions(Request $request, string $id): Response
    {
        $validated = $request->validate([
            'permission_ids' => 'array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        DB::transaction(function () use ($id, $validated) {
            DB::table('role_has_permissions')->where('role_id', $id)->delete();

            if (!empty($validated['permission_ids'])) {
                foreach ($validated['permission_ids'] as $permId) {
                    DB::table('role_has_permissions')->insert([
                        'role_id' => $id,
                        'permission_id' => $permId,
                    ]);
                }
            }
        });

        return response()->json(['success' => true]);
    }

    public function updateUserRole(Request $request, string $id): Response
    {
        $tenantId = $this->tenantId();

        $validated = $request->validate([
            'role' => 'nullable|string|max:100',
        ]);

        DB::table('users')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->update(['role' => $validated['role'], 'updated_at' => now()]);

        return response()->json(['success' => true]);
    }

    // ============ Notifications — Create & Broadcast ============

    public function storeNotification(Request $request): Response
    {
        $tenantId = $this->tenantId();
        $userId = $request->user()->id ?? null;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'required|string|in:info,warning,error,success',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
            'data' => 'nullable|array',
        ]);

        $targetUserIds = $validated['user_ids'] ?? [];

        // If no specific users, notify all users in the tenant
        if (empty($targetUserIds)) {
            $targetUserIds = DB::table('users')
                ->where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();
        }

        $notifications = [];
        foreach ($targetUserIds as $targetUserId) {
            $notificationId = DB::table('notifications')->insertGetId([
                'tenant_id' => $tenantId,
                'user_id' => $targetUserId,
                'title' => $validated['title'],
                'body' => $validated['body'],
                'type' => $validated['type'],
                'data' => $validated['data'] ? json_encode($validated['data']) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $notifications[] = [
                'id' => $notificationId,
                'title' => $validated['title'],
                'body' => $validated['body'],
                'type' => $validated['type'],
                'tenant_id' => $tenantId,
                'actor_name' => $request->user()->name ?? 'System',
                'data' => $validated['data'] ?? [],
                'created_at' => now()->format('Y-m-d H:i:s'),
            ];
        }

        // Broadcast the notification event via Reverb
        foreach ($notifications as $notification) {
            broadcast(new \App\Events\NotificationSent($notification))->toOthers();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification sent to ' . count($notifications) . ' user(s).',
            'notifications' => $notifications,
            'sent' => count($notifications),
        ]);
    }

    public function notificationTest(): Response
    {
        return Inertia::render('Notifications/NotificationTest');
    }

    // ============ Advanced Planning & Scheduling (APS) ============

    public function aps(): Response
    {
        $tenantId = $this->tenantId();

        $workOrders = DB::table('work_orders as wo')
            ->leftJoin('production_orders as po', 'po.id', '=', 'wo.production_order_id')
            ->leftJoin('boms as b', 'b.id', '=', 'po.bom_header_id')
            ->leftJoin('items as i', 'i.id', '=', 'b.item_id')
            ->where('wo.tenant_id', $tenantId)
            ->select([
                'wo.id',
                DB::raw("CONCAT('WO-', wo.id) as work_order_number"),
                'wo.status',
                'wo.started_at as planned_start_date',
                'wo.completed_at as planned_end_date',
                'wo.started_at as actual_start_date',
                'wo.completed_at as actual_end_date',
                'wo.planned_quantity as quantity',
                'wo.actual_quantity as completed_quantity',
                'po.order_number as production_order',
                'i.name as item_name',
            ])
            ->orderBy('wo.started_at')
            ->get();

        $workCentres = DB::table('work_centres')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->select(['id', 'code', 'name', 'capacity_per_hour'])
            ->get();

        $summary = [
            'total_work_orders' => $workOrders->count(),
            'in_progress' => $workOrders->where('status', 'in_progress')->count(),
            'completed' => $workOrders->where('status', 'completed')->count(),
            'delayed' => $workOrders->where('status', 'delayed')->count(),
            'total_work_centres' => $workCentres->count(),
        ];

        return Inertia::render('APS/APS', [
            'work_orders' => $workOrders,
            'work_centres' => $workCentres,
            'summary' => $summary,
        ]);
    }

    public function apsGantt(): Response
    {
        $tenantId = $this->tenantId();

        $ganttData = DB::table('work_orders as wo')
            ->leftJoin('production_orders as po', 'po.id', '=', 'wo.production_order_id')
            ->leftJoin('boms as b', 'b.id', '=', 'po.bom_header_id')
            ->leftJoin('items as i', 'i.id', '=', 'b.item_id')
            ->where('wo.tenant_id', $tenantId)
            ->select([
                'wo.id',
                DB::raw("CONCAT('WO-', wo.id) as work_order_number"),
                'wo.status',
                'wo.started_at as planned_start_date',
                'wo.completed_at as planned_end_date',
                'wo.started_at as actual_start_date',
                'wo.completed_at as actual_end_date',
                'wo.planned_quantity as quantity',
                'wo.actual_quantity as completed_quantity',
                'po.order_number as production_order',
                'i.name as item_name',
            ])
            ->orderBy('wo.started_at')
            ->get();

        return Inertia::render('APS/Gantt', [
            'gantt_data' => $ganttData,
        ]);
    }

    public function apsCapacity(): Response
    {
        $tenantId = $this->tenantId();

        $capacityData = DB::table('work_centres as wc')
            ->where('wc.tenant_id', $tenantId)
            ->where('wc.is_active', true)
            ->leftJoin('work_orders as wo', function($join) use ($tenantId) {
                $join->on('wo.work_centre_id', '=', 'wc.id')
                     ->where('wo.status', 'in_progress')
                     ->where('wo.tenant_id', $tenantId);
            })
            ->select([
                'wc.id', 'wc.code', 'wc.name', 'wc.capacity_per_hour',
                DB::raw('COUNT(wo.id) as active_work_orders'),
            ])
            ->groupBy('wc.id', 'wc.code', 'wc.name', 'wc.capacity_per_hour')
            ->orderBy('wc.code')
            ->get();

        return Inertia::render('APS/Capacity', [
            'capacity_data' => $capacityData,
        ]);
    }

    // ============ Quality — SPC Control Charts ============

    public function spc(): Response
    {
        $tenantId = $this->tenantId();

        $controlCharts = DB::table('quality_control_charts as qcc')
            ->where('qcc.tenant_id', $tenantId)
            ->select(['qcc.id', 'qcc.chart_name', 'qcc.chart_type', 'qcc.item_id',
                'qcc.upper_control_limit', 'qcc.lower_control_limit',
                'qcc.target_value', 'qcc.status', 'qcc.created_at',
                'i.name as item_name'])
            ->leftJoin('items as i', 'i.id', '=', 'qcc.item_id')
            ->orderBy('qcc.chart_name')
            ->get();

        $summary = [
            'total_charts' => $controlCharts->count(),
            'active_charts' => $controlCharts->where('status', 'active')->count(),
            'out_of_control' => $controlCharts->where('status', 'out_of_control')->count(),
        ];

        return Inertia::render('Quality/SPC', [
            'control_charts' => $controlCharts,
            'summary' => $summary,
        ]);
    }

    public function spcChart(string $controlChartId): Response
    {
        $tenantId = $this->tenantId();

        $chart = DB::table('quality_control_charts as qcc')
            ->where('qcc.id', $controlChartId)
            ->where('qcc.tenant_id', $tenantId)
            ->first();

        $dataPoints = DB::table('quality_control_chart_points as qcp')
            ->where('qcp.control_chart_id', $controlChartId)
            ->orderBy('qcp.measured_at')
            ->get();

        return Inertia::render('Quality/SPCChart', [
            'chart' => $chart,
            'data_points' => $dataPoints,
        ]);
    }

    // ============ HR — Payroll ============

    public function payroll(): Response
    {
        $tenantId = $this->tenantId();

        $payrollRuns = DB::table('payroll_runs as pr')
            ->where('pr.tenant_id', $tenantId)
            ->select(['pr.id', 'pr.run_name', 'pr.pay_period_start', 'pr.pay_period_end',
                'pr.status', 'pr.total_gross', 'pr.total_net', 'pr.created_at'])
            ->orderByDesc('pr.pay_period_end')
            ->get();

        $summary = [
            'total_runs' => $payrollRuns->count(),
            'completed_runs' => $payrollRuns->where('status', 'completed')->count(),
            'total_net_pay' => $payrollRuns->sum('total_net'),
        ];

        return Inertia::render('HR/Payroll', [
            'payroll_runs' => $payrollRuns,
            'summary' => $summary,
        ]);
    }

    public function createPayroll(): Response
    {
        $employees = DB::table('employees')
            ->where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->select(['id', 'employee_code', 'name', 'hourly_rate', 'salary'])
            ->orderBy('employee_code')
            ->get();

        return Inertia::render('HR/CreatePayroll', [
            'employees' => $employees,
        ]);
    }

    // ============ HR — Performance Reviews ============

    public function performanceReviews(): Response
    {
        $tenantId = $this->tenantId();

        $reviews = DB::table('performance_reviews as pr')
            ->leftJoin('employees as e', 'e.id', '=', 'pr.employee_id')
            ->where('pr.tenant_id', $tenantId)
            ->select(['pr.id', 'pr.review_title', 'pr.review_date', 'pr.review_period',
                'pr.overall_rating', 'pr.status', 'e.name as employee_name',
                'e.employee_code'])
            ->orderByDesc('pr.review_date')
            ->get();

        $summary = [
            'total_reviews' => $reviews->count(),
            'completed_reviews' => $reviews->where('status', 'completed')->count(),
            'pending_reviews' => $reviews->where('status', 'pending')->count(),
        ];

        return Inertia::render('HR/PerformanceReviews', [
            'reviews' => $reviews,
            'summary' => $summary,
        ]);
    }

    public function createPerformanceReview(): Response
    {
        $employees = DB::table('employees')
            ->where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->select(['id', 'employee_code', 'name'])
            ->orderBy('employee_code')
            ->get();

        return Inertia::render('HR/CreatePerformanceReview', [
            'employees' => $employees,
        ]);
    }

    // ============ HR — Training Management ============

    public function training(): Response
    {
        $tenantId = $this->tenantId();

        $training = DB::table('training_programs as tp')
            ->where('tp.tenant_id', $tenantId)
            ->select(['tp.id', 'tp.program_name', 'tp.training_type', 'tp.start_date',
                'tp.end_date', 'tp.status', 'tp.cost',
                DB::raw('COUNT(tr.id) as enrolled_count')])
            ->leftJoin('training_records as tr', function($join) {
                $join->on('tr.training_program_id', '=', 'tp.id')
                     ->where('tr.status', 'enrolled');
            })
            ->groupBy('tp.id', 'tp.program_name', 'tp.training_type', 'tp.start_date',
                'tp.end_date', 'tp.status', 'tp.cost')
            ->orderBy('tp.start_date')
            ->get();

        return Inertia::render('HR/Training', [
            'training_programs' => $training,
        ]);
    }

    public function createTraining(): Response
    {
        $employees = DB::table('employees')
            ->where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->select(['id', 'employee_code', 'name'])
            ->orderBy('employee_code')
            ->get();

        return Inertia::render('HR/CreateTraining', [
            'employees' => $employees,
        ]);
    }

    // ============ Purchasing — RFQ Management ============

    public function rfq(): Response
    {
        $tenantId = $this->tenantId();

        $rfqs = DB::table('rfqs as r')
            ->leftJoin('suppliers as s', 's.id', '=', 'r.supplier_id')
            ->where('r.tenant_id', $tenantId)
            ->select(['r.id', 'r.rfq_number', 'r.title', 'r.status',
                'r.request_date', 'r.response_due_date', 'r.total_amount',
                's.name as supplier_name'])
            ->orderByDesc('r.request_date')
            ->get();

        $summary = [
            'total_rfqs' => $rfqs->count(),
            'open_rfqs' => $rfqs->where('status', 'open')->count(),
            'awarded_rfqs' => $rfqs->where('status', 'awarded')->count(),
        ];

        return Inertia::render('Purchasing/RFQ', [
            'rfqs' => $rfqs,
            'summary' => $summary,
        ]);
    }

    public function createRfq(): Response
    {
        $suppliers = DB::table('suppliers')
            ->where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->select(['id', 'name', 'code'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Purchasing/CreateRFQ', [
            'suppliers' => $suppliers,
        ]);
    }

    // ============ Purchasing — Contracts ============

    public function contracts(): Response
    {
        $tenantId = $this->tenantId();

        $contracts = DB::table('supplier_contracts as sc')
            ->leftJoin('suppliers as s', 's.id', '=', 'sc.supplier_id')
            ->where('sc.tenant_id', $tenantId)
            ->select(['sc.id', 'sc.contract_number', 'sc.title', 'sc.contract_type',
                'sc.start_date', 'sc.end_date', 'sc.status', 'sc.total_value',
                's.name as supplier_name'])
            ->orderBy('sc.start_date')
            ->get();

        $summary = [
            'total_contracts' => $contracts->count(),
            'active_contracts' => $contracts->where('status', 'active')->count(),
            'expiring_soon' => $contracts->where('end_date', '<', now()->addDays(30))->count(),
        ];

        return Inertia::render('Purchasing/Contracts', [
            'contracts' => $contracts,
            'summary' => $summary,
        ]);
    }

    public function createContract(): Response
    {
        $suppliers = DB::table('suppliers')
            ->where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->select(['id', 'name', 'code'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Purchasing/CreateContract', [
            'suppliers' => $suppliers,
        ]);
    }

    // ============ Sales — CPQ ============

    public function cpq(): Response
    {
        $tenantId = $this->tenantId();

        $quotes = DB::table('cpq_quotes as cq')
            ->leftJoin('customers as c', 'c.id', '=', 'cq.customer_id')
            ->where('cq.tenant_id', $tenantId)
            ->select(['cq.id', 'cq.quote_number', 'c.name as customer_name',
                'cq.status', 'cq.quote_date', 'cq.valid_until', 'cq.total_amount',
                'cq.approval_status'])
            ->orderByDesc('cq.quote_date')
            ->get();

        $summary = [
            'total_quotes' => $quotes->count(),
            'draft_quotes' => $quotes->where('status', 'draft')->count(),
            'approved_quotes' => $quotes->where('approval_status', 'approved')->count(),
        ];

        return Inertia::render('Sales/CPQ', [
            'quotes' => $quotes,
            'summary' => $summary,
        ]);
    }

    public function createCpq(): Response
    {
        $customers = DB::table('customers')
            ->where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->select(['id', 'name', 'code'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Sales/CreateCPQ', [
            'customers' => $customers,
        ]);
    }

    // ============ Sales — Commissions ============

    public function commissions(): Response
    {
        $tenantId = $this->tenantId();

        $commissions = DB::table('sales_commissions as sc')
            ->leftJoin('employees as e', 'e.id', '=', 'sc.employee_id')
            ->where('sc.tenant_id', $tenantId)
            ->select(['sc.id', 'sc.commission_period', 'sc.commission_type',
                'sc.base_amount', 'sc.commission_rate', 'sc.commission_amount',
                'sc.status', 'sc.paid_at', 'e.name as employee_name'])
            ->orderByDesc('sc.commission_period')
            ->get();

        $summary = [
            'total_commissions' => $commissions->count(),
            'total_amount' => $commissions->sum('commission_amount'),
            'paid_amount' => $commissions->where('status', 'paid')->sum('commission_amount'),
        ];

        return Inertia::render('Sales/Commissions', [
            'commissions' => $commissions,
            'summary' => $summary,
        ]);
    }

    // ============ Advanced Inventory — Cross-docking ============

    public function crossDocking(): Response
    {
        $tenantId = $this->tenantId();

        $crossDocks = DB::table('cross_docking as cd')
            ->leftJoin('items as i', 'i.id', '=', 'cd.item_id')
            ->where('cd.tenant_id', $tenantId)
            ->select(['cd.id', 'cd.reference', 'i.name as item_name',
                'cd.quantity', 'cd.received_date', 'cd.ship_date', 'cd.status'])
            ->orderByDesc('cd.received_date')
            ->get();

        return Inertia::render('Inventory/CrossDocking', [
            'cross_docks' => $crossDocks,
        ]);
    }

    // ============ Advanced Inventory — Kitting ============

    public function kitting(): Response
    {
        $tenantId = $this->tenantId();

        $kits = DB::table('kitting as k')
            ->leftJoin('items as i', 'i.id', '=', 'k.item_id')
            ->where('k.tenant_id', $tenantId)
            ->select(['k.id', 'p.kit_number', 'k.item_name', 'k.quantity',
                'k.status', 'k.created_at'])
            ->orderByDesc('k.created_at')
            ->get();

        return Inertia::render('Inventory/Kitting', [
            'kits' => $kits,
        ]);
    }

    // ============ Advanced Inventory — Consignment ============

    public function consignment(): Response
    {
        $tenantId = $this->tenantId();

        $consignments = DB::table('consignment_inventory as ci')
            ->leftJoin('suppliers as s', 's.id', '=', 'ci.supplier_id')
            ->leftJoin('items as i', 'i.id', '=', 'ci.item_id')
            ->where('ci.tenant_id', $tenantId)
            ->select(['ci.id', 'ci.reference', 's.name as supplier_name',
                'i.name as item_name', 'ci.quantity_on_hand', 'ci.quantity_consumed',
                'ci.status', 'ci.last_reconciled'])
            ->orderByDesc('ci.created_at')
            ->get();

        return Inertia::render('Inventory/Consignment', [
            'consignments' => $consignments,
        ]);
    }

    // ============ Advanced Costing — Activity-Based Costing ============

    public function activityBasedCosting(): Response
    {
        $tenantId = $this->tenantId();

        $activities = DB::table('activity_centers as ac')
            ->where('ac.tenant_id', $tenantId)
            ->select(['ac.id', 'ac.activity_name', 'ac.activity_type',
                'ac.total_cost', 'ac.allocation_base', 'ac.allocation_rate',
                'ac.status', 'ac.created_at'])
            ->orderBy('ac.activity_name')
            ->get();

        $summary = [
            'total_activities' => $activities->count(),
            'total_cost' => $activities->sum('total_cost'),
        ];

        return Inertia::render('Costing/ActivityBased', [
            'activities' => $activities,
            'summary' => $summary,
        ]);
    }

    // ============ Compliance & Audit — Audit Trail ============

    public function auditTrail(Request $request): Response
    {
        $tenantId = $this->tenantId();
        $q = trim((string) $request->query('q', ''));
        $event = $request->query('event');

        $build = function (string $table, array $map) use ($tenantId, $q, $event) {
            $query = DB::table($table . ' as a')
                ->leftJoin('users as u', 'u.id', '=', DB::raw($map['user']))
                ->where(DB::raw($map['tenant']), $tenantId)
                ->select([
                    'a.id',
                    DB::raw($map['event'] . ' as event_type'),
                    DB::raw($map['table'] . ' as table_name'),
                    DB::raw($map['record'] . ' as record_id'),
                    DB::raw($map['old'] . ' as old_values'),
                    DB::raw($map['new'] . ' as new_values'),
                    DB::raw($map['created'] . ' as created_at'),
                    'u.name as user_name',
                ])
                ->orderByDesc(DB::raw($map['created']));

            if ($q !== '') {
                $query->where(function ($n) use ($q, $map) {
                    $n->where(DB::raw($map['event']), 'ilike', "%{$q}%")
                      ->orWhere(DB::raw($map['table']), 'ilike', "%{$q}%")
                      ->orWhere('u.name', 'ilike', "%{$q}%");
                });
            }
            if ($event) $query->where(DB::raw($map['event']), $event);
            return $query;
        };

        try {
            $payload = $this->paginate($build('audit_trail', [
                'user' => 'a.user_id', 'tenant' => 'a.tenant_id', 'event' => 'a.event_type', 'table' => 'a.table_name',
                'record' => 'a.record_id', 'old' => 'a.old_values', 'new' => 'a.new_values', 'created' => 'a.created_at',
            ]));
        } catch (\Throwable $e) {
            $payload = $this->paginate($build('audit_logs', [
                'user' => 'a.user_id', 'tenant' => 'a.tenant_id', 'event' => 'a.action', 'table' => 'a.subject_type',
                'record' => 'a.subject_id', 'old' => 'a.old_values', 'new' => 'a.new_values', 'created' => 'a.created_at',
            ]));
        }

        $payload['data'] = array_map(function (array $row) {
            $row['created_at'] = $row['created_at'] ? Carbon::parse($row['created_at'])->format('d M Y, H:i:s') : null;
            $row['user_name'] = $row['user_name'] ?? 'System';
            foreach (['old_values', 'new_values'] as $k) {
                if (is_string($row[$k] ?? null)) {
                    $decoded = json_decode($row[$k], true);
                    $row[$k] = $decoded ? json_encode($decoded, JSON_UNESCAPED_UNICODE) : $row[$k];
                } elseif (is_array($row[$k] ?? null)) {
                    $row[$k] = json_encode($row[$k], JSON_UNESCAPED_UNICODE);
                }
            }
            return $row;
        }, $payload['data']);

        return Inertia::render('Compliance/AuditTrail', [
            'audit_logs' => $payload,
            'filters' => ['q' => $q, 'event' => $event],
        ]);
    }

    public function exportAuditTrail(Request $request)
    {
        $tenantId = $this->tenantId();
        try {
            $rows = DB::table('audit_trail')->where('tenant_id', $tenantId)->orderByDesc('created_at')->limit(5000)->get();
            $map = fn($r) => [$r->created_at, $r->event_type ?? '', $r->table_name ?? '', $r->record_id ?? '', $r->user_id ?? '', $r->old_values ?? '', $r->new_values ?? ''];
        } catch (\Throwable $e) {
            $rows = DB::table('audit_logs')->where('tenant_id', $tenantId)->orderByDesc('created_at')->limit(5000)->get();
            $map = fn($r) => [$r->created_at, $r->action ?? '', $r->subject_type ?? '', $r->subject_id ?? '', $r->user_id ?? '', $r->old_values ?? '', $r->new_values ?? ''];
        }

        return response()->streamDownload(function () use ($rows, $map) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Timestamp', 'Event', 'Entity', 'Record ID', 'User ID', 'Old Values', 'New Values']);
            foreach ($rows as $r) fputcsv($out, $map($r));
            fclose($out);
        }, 'audit_trail_' . now()->format('Ymd_His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    // ============ Workflow Automation — Approvals ============

    public function approvals(Request $request): Response
    {
        $tenantId = $this->tenantId();
        $status = $request->query('status');
        $q = trim((string) $request->query('q', ''));

        $query = DB::table('workflow_approvals as wa')
            ->leftJoin('users as u', 'u.id', '=', 'wa.approver_id')
            ->where('wa.tenant_id', $tenantId)
            ->select([
                'wa.id', 'wa.workflow_type', 'wa.reference_number', 'wa.status',
                DB::raw('COALESCE(wa.requested_at, wa.requested_date, wa.created_at) as requested_at'),
                DB::raw('COALESCE(wa.approved_at, wa.approved_date) as approved_at'),
                'wa.comments', 'u.name as approver_name',
            ])
            ->orderByDesc(DB::raw('COALESCE(wa.requested_at, wa.requested_date, wa.created_at)'));

        if ($status && $status !== 'all') $query->where('wa.status', $status);
        if ($q !== '') {
            $query->where(function ($n) use ($q) {
                $n->where('wa.workflow_type', 'ilike', "%{$q}%")
                  ->orWhere('wa.reference_number', 'ilike', "%{$q}%")
                  ->orWhere('wa.comments', 'ilike', "%{$q}%")
                  ->orWhere('u.name', 'ilike', "%{$q}%");
            });
        }

        $payload = $this->paginate($query);
        $payload['data'] = array_map(function (array $row) {
            $row['requested_at'] = $row['requested_at'] ? Carbon::parse($row['requested_at'])->format('d M Y, H:i') : null;
            $row['approved_at'] = $row['approved_at'] ? Carbon::parse($row['approved_at'])->format('d M Y, H:i') : null;
            $row['approver_name'] = $row['approver_name'] ?? '—';
            $row['comments'] = $row['comments'] ?? '';
            return $row;
        }, $payload['data']);

        $base = DB::table('workflow_approvals')->where('tenant_id', $tenantId);
        $summary = [
            'total_approvals' => (int) (clone $base)->count(),
            'pending_approvals' => (int) (clone $base)->where('status', 'pending')->count(),
            'approved' => (int) (clone $base)->where('status', 'approved')->count(),
            'rejected' => (int) (clone $base)->where('status', 'rejected')->count(),
        ];

        return Inertia::render('Workflow/Approvals', [
            'approvals' => $payload,
            'summary' => $summary,
            'filters' => ['status' => $status ?? 'all', 'q' => $q],
        ]);
    }

    public function approveRequest(Request $request, string $id)
    {
        $tenantId = $this->tenantId();
        $comment = $request->input('comments');
        $updates = ['status' => 'approved', 'comments' => $comment, 'updated_at' => now()];
        try { $updates['approved_at'] = now(); } catch (\Throwable $e) {}
        try { $updates['approved_date'] = now()->toDateString(); } catch (\Throwable $e) {}
        if ($request->user()) { try { $updates['approver_id'] = $request->user()->id; } catch (\Throwable $e) {} }

        // update with only columns that exist by attempting minimal set
        $row = DB::table('workflow_approvals')->where('tenant_id', $tenantId)->where('id', $id)->first();
        abort_if(!$row, 404);
        $safe = ['status' => 'approved', 'updated_at' => now()];
        if (property_exists($row, 'comments')) $safe['comments'] = $comment;
        if (property_exists($row, 'approved_at')) $safe['approved_at'] = now();
        if (property_exists($row, 'approved_date')) $safe['approved_date'] = now()->toDateString();
        if (property_exists($row, 'approver_id') && $request->user()) $safe['approver_id'] = $request->user()->id;
        DB::table('workflow_approvals')->where('tenant_id', $tenantId)->where('id', $id)->update($safe);

        return $request->expectsJson() ? response()->json(['success' => true]) : back()->with('success', 'Request approved.');
    }

    public function rejectRequest(Request $request, string $id)
    {
        $tenantId = $this->tenantId();
        $comment = $request->input('comments');
        $row = DB::table('workflow_approvals')->where('tenant_id', $tenantId)->where('id', $id)->first();
        abort_if(!$row, 404);
        $safe = ['status' => 'rejected', 'updated_at' => now()];
        if (property_exists($row, 'comments')) $safe['comments'] = $comment;
        if (property_exists($row, 'approved_at')) $safe['approved_at'] = now();
        if (property_exists($row, 'approved_date')) $safe['approved_date'] = now()->toDateString();
        if (property_exists($row, 'approver_id') && $request->user()) $safe['approver_id'] = $request->user()->id;
        DB::table('workflow_approvals')->where('tenant_id', $tenantId)->where('id', $id)->update($safe);

        return $request->expectsJson() ? response()->json(['success' => true]) : back()->with('success', 'Request rejected.');
    }

    public function workflowRules(): Response
    {
        $tenantId = $this->tenantId();

        $rules = DB::table('workflow_rules as wr')
            ->where('wr.tenant_id', $tenantId)
            ->select(['wr.id', 'wr.rule_name', 'wr.workflow_type', 'wr.condition',
                'wr.action', 'wr.status', 'wr.created_at'])
            ->orderBy('wr.rule_name')
            ->get();

        return Inertia::render('Workflow/Rules', [
            'rules' => $rules,
        ]);
    }

    // ============ Integrations — Webhooks ============

    public function webhooks(): Response
    {
        $tenantId = $this->tenantId();

        $webhooks = DB::table('webhooks as w')
            ->where('w.tenant_id', $tenantId)
            ->select(['w.id', 'w.name', 'w.url', 'w.events',
                'w.status', 'w.last_triggered', 'w.created_at'])
            ->orderBy('w.name')
            ->get();

        return Inertia::render('Integrations/Webhooks', [
            'webhooks' => $webhooks,
        ]);
    }

    public function createWebhook(): Response
    {
        return Inertia::render('Integrations/CreateWebhook');
    }

    // ============ Customer Portal ============

    public function customerDashboard(): Response
    {
        $tenantId = $this->tenantId();

        $orders = DB::table('sales_orders as so')
            ->where('so.tenant_id', $tenantId)
            ->select(['so.id', 'so.so_number', 'so.status', 'so.order_date', 'so.total_amount'])
            ->orderByDesc('so.order_date')
            ->limit(10)
            ->get();

        $invoices = DB::table('customer_invoices as ci')
            ->where('ci.tenant_id', $tenantId)
            ->select(['ci.id', 'ci.invoice_number', 'ci.status', 'ci.invoice_date', 'ci.due_date', 'ci.total_amount'])
            ->orderByDesc('ci.invoice_date')
            ->limit(10)
            ->get();

        $quotes = DB::table('sales_quotes as sq')
            ->where('sq.tenant_id', $tenantId)
            ->select(['sq.id', 'sq.quote_number', 'sq.status', 'sq.quote_date', 'sq.valid_until', 'sq.total_amount'])
            ->orderByDesc('sq.quote_date')
            ->limit(10)
            ->get();

        $summary = [
            'total_orders' => $orders->count(),
            'open_orders' => $orders->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'total_invoices' => $invoices->count(),
            'unpaid_invoices' => $invoices->where('status', '!=', 'paid')->count(),
            'open_quotes' => $quotes->where('status', 'sent')->count(),
        ];

        return Inertia::render('CustomerPortal/Dashboard', [
            'orders' => $orders,
            'invoices' => $invoices,
            'quotes' => $quotes,
            'summary' => $summary,
        ]);
    }

    public function customerOrders(): Response
    {
        $tenantId = $this->tenantId();

        $orders = DB::table('sales_orders as so')
            ->where('so.tenant_id', $tenantId)
            ->select(['so.id', 'so.so_number', 'so.status', 'so.order_date', 'so.delivery_date', 'so.total_amount'])
            ->orderByDesc('so.order_date')
            ->get();

        return Inertia::render('CustomerPortal/Orders', [
            'orders' => $orders,
        ]);
    }

    public function customerOrderDetail(string $id): Response
    {
        $tenantId = $this->tenantId();

        $order = DB::table('sales_orders as so')
            ->where('so.id', $id)
            ->where('so.tenant_id', $tenantId)
            ->first();

        $lines = DB::table('sales_order_lines as sol')
            ->leftJoin('items as i', 'i.id', '=', 'sol.item_id')
            ->where('sol.sales_order_id', $id)
            ->select(['sol.id', 'i.sku', 'i.name as item_name', 'sol.quantity', 'sol.unit_price', 'sol.total_price'])
            ->get();

        return Inertia::render('CustomerPortal/OrderDetail', [
            'order' => $order,
            'lines' => $lines,
        ]);
    }

    public function portalCustomerInvoices(): Response
    {
        $tenantId = $this->tenantId();

        $invoices = DB::table('customer_invoices as ci')
            ->where('ci.tenant_id', $tenantId)
            ->select(['ci.id', 'ci.invoice_number', 'ci.status', 'ci.invoice_date', 'ci.due_date', 'ci.total_amount', 'ci.total_amount_paid'])
            ->orderByDesc('ci.invoice_date')
            ->get();

        return Inertia::render('CustomerPortal/Invoices', [
            'invoices' => $invoices,
        ]);
    }

    public function customerQuotes(): Response
    {
        $tenantId = $this->tenantId();

        $quotes = DB::table('sales_quotes as sq')
            ->where('sq.tenant_id', $tenantId)
            ->select(['sq.id', 'sq.quote_number', 'sq.status', 'sq.quote_date', 'sq.valid_until', 'sq.total_amount'])
            ->orderByDesc('sq.quote_date')
            ->get();

        return Inertia::render('CustomerPortal/Quotes', [
            'quotes' => $quotes,
        ]);
    }

    public function customerAcceptQuote(string $id): Response
    {
        $tenantId = $this->tenantId();

        DB::table('sales_quotes')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->update(['status' => 'accepted', 'updated_at' => now()]);

        return response()->json(['success' => true]);
    }

    // ============ Supplier Portal ============

    public function supplierDashboard(): Response
    {
        $tenantId = $this->tenantId();

        $pos = DB::table('purchase_orders as po')
            ->where('po.tenant_id', $tenantId)
            ->select(['po.id', 'po.po_number', 'po.status', 'po.order_date', 'po.expected_delivery_date', 'po.total_amount'])
            ->orderByDesc('po.order_date')
            ->limit(10)
            ->get();

        $invoices = DB::table('supplier_invoices as si')
            ->where('si.tenant_id', $tenantId)
            ->select(['si.id', 'si.invoice_number', 'si.status', 'si.invoice_date', 'si.due_date', 'si.total_amount'])
            ->orderByDesc('si.invoice_date')
            ->limit(10)
            ->get();

        $summary = [
            'total_pos' => $pos->count(),
            'open_pos' => $pos->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'total_invoices' => $invoices->count(),
            'unpaid_invoices' => $invoices->where('status', '!=', 'paid')->count(),
        ];

        return Inertia::render('SupplierPortal/Dashboard', [
            'pos' => $pos,
            'invoices' => $invoices,
            'summary' => $summary,
        ]);
    }

    public function supplierPurchaseOrders(): Response
    {
        $tenantId = $this->tenantId();

        $pos = DB::table('purchase_orders as po')
            ->where('po.tenant_id', $tenantId)
            ->select(['po.id', 'po.po_number', 'po.status', 'po.order_date', 'po.expected_delivery_date', 'po.total_amount'])
            ->orderByDesc('po.order_date')
            ->get();

        return Inertia::render('SupplierPortal/PurchaseOrders', [
            'pos' => $pos,
        ]);
    }

    public function supplierPODetail(string $id): Response
    {
        $tenantId = $this->tenantId();

        $po = DB::table('purchase_orders as po')
            ->where('po.id', $id)
            ->where('po.tenant_id', $tenantId)
            ->first();

        $lines = DB::table('purchase_order_lines as pol')
            ->leftJoin('items as i', 'i.id', '=', 'pol.item_id')
            ->where('pol.purchase_order_id', $id)
            ->select(['pol.id', 'i.sku', 'i.name as item_name', 'pol.quantity', 'pol.unit_price', 'pol.total_price'])
            ->get();

        return Inertia::render('SupplierPortal/PODetail', [
            'po' => $po,
            'lines' => $lines,
        ]);
    }

    public function supplierInvoicesPortal(): Response
    {
        $tenantId = $this->tenantId();

        $invoices = DB::table('supplier_invoices as si')
            ->where('si.tenant_id', $tenantId)
            ->select(['si.id', 'si.invoice_number', 'si.status', 'si.invoice_date', 'si.due_date', 'si.total_amount'])
            ->orderByDesc('si.invoice_date')
            ->get();

        return Inertia::render('SupplierPortal/Invoices', [
            'invoices' => $invoices,
        ]);
    }

    public function supplierAcceptInvoice(string $id): Response
    {
        $tenantId = $this->tenantId();

        DB::table('supplier_invoices')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->update(['status' => 'accepted', 'updated_at' => now()]);

        return response()->json(['success' => true]);
    }

    // ============ EHS — Environment, Health & Safety ============

    public function ehsDashboard(): Response
    {
        $tenantId = $this->tenantId();

        $incidents = DB::table('ehs_incidents as ei')
            ->where('ei.tenant_id', $tenantId)
            ->select(['ei.id', 'ei.incident_number', 'ei.incident_type', 'ei.status', 'ei.incident_date', 'ei.severity'])
            ->orderByDesc('ei.incident_date')
            ->limit(10)
            ->get();

        $summary = [
            'total_incidents' => $incidents->count(),
            'open_incidents' => $incidents->where('status', 'open')->count(),
            'critical_incidents' => $incidents->where('severity', 'critical')->count(),
        ];

        return Inertia::render('EHS/Dashboard', [
            'incidents' => $incidents,
            'summary' => $summary,
        ]);
    }

    public function ehsIncidents(): Response
    {
        $tenantId = $this->tenantId();

        $incidents = DB::table('ehs_incidents as ei')
            ->where('ei.tenant_id', $tenantId)
            ->select(['ei.id', 'ei.incident_number', 'ei.incident_type', 'ei.status', 'ei.incident_date', 'ei.severity', 'ei.description'])
            ->orderByDesc('ei.incident_date')
            ->get();

        return Inertia::render('EHS/Incidents', [
            'incidents' => $incidents,
        ]);
    }

    public function createEHSIncident(): Response
    {
        $employees = DB::table('employees')
            ->where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->select(['id', 'employee_code', 'name'])
            ->orderBy('employee_code')
            ->get();

        return Inertia::render('EHS/CreateIncident', [
            'employees' => $employees,
        ]);
    }

    public function ehsNearMisses(): Response
    {
        $tenantId = $this->tenantId();

        $nearMisses = DB::table('ehs_near_misses as enm')
            ->where('enm.tenant_id', $tenantId)
            ->select(['enm.id', 'enm.incident_number', 'enm.description', 'enm.incident_date', 'enm.status'])
            ->orderByDesc('enm.incident_date')
            ->get();

        return Inertia::render('EHS/NearMisses', [
            'near_misses' => $nearMisses,
        ]);
    }

    public function createEHSNearMiss(): Response
    {
        $employees = DB::table('employees')
            ->where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->select(['id', 'employee_code', 'name'])
            ->orderBy('employee_code')
            ->get();

        return Inertia::render('EHS/CreateNearMiss', [
            'employees' => $employees,
        ]);
    }

    public function ehsSafetyAudits(): Response
    {
        $tenantId = $this->tenantId();

        $audits = DB::table('ehs_safety_audits as es')
            ->where('es.tenant_id', $tenantId)
            ->select(['es.id', 'es.audit_number', 'es.audit_type', 'es.audit_date', 'es.status', 'es.score', 'es.auditor_name'])
            ->orderByDesc('es.audit_date')
            ->get();

        return Inertia::render('EHS/SafetyAudits', [
            'audits' => $audits,
        ]);
    }

    public function createEHSSafetyAudit(): Response
    {
        $employees = DB::table('employees')
            ->where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->select(['id', 'employee_code', 'name'])
            ->orderBy('employee_code')
            ->get();

        return Inertia::render('EHS/CreateSafetyAudit', [
            'employees' => $employees,
        ]);
    }

    // ============ Energy Management ============

    public function energyDashboard(): Response
    {
        $tenantId = $this->tenantId();

        $consumption = DB::table('energy_consumption as ec')
            ->where('ec.tenant_id', $tenantId)
            ->select(['ec.id', 'ec.meter_name', 'ec.consumption_date', 'ec.consumption_value', 'ec.unit', 'ec.cost'])
            ->orderByDesc('ec.consumption_date')
            ->limit(20)
            ->get();

        $summary = [
            'total_consumption' => $consumption->sum('consumption_value'),
            'total_cost' => $consumption->sum('cost'),
            'active_meters' => $consumption->pluck('meter_name')->unique()->count(),
        ];

        return Inertia::render('Energy/Dashboard', [
            'consumption' => $consumption,
            'summary' => $summary,
        ]);
    }

    public function energyConsumption(): Response
    {
        $tenantId = $this->tenantId();

        $consumption = DB::table('energy_consumption as ec')
            ->where('ec.tenant_id', $tenantId)
            ->select(['ec.id', 'ec.meter_name', 'ec.consumption_date', 'ec.consumption_value', 'ec.unit', 'ec.cost'])
            ->orderByDesc('ec.consumption_date')
            ->get();

        return Inertia::render('Energy/Consumption', [
            'consumption' => $consumption,
        ]);
    }

    public function createEnergyConsumption(): Response
    {
        $meters = DB::table('energy_meters')
            ->where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->select(['id', 'meter_name', 'meter_type', 'unit'])
            ->orderBy('meter_name')
            ->get();

        return Inertia::render('Energy/CreateConsumption', [
            'meters' => $meters,
        ]);
    }

    public function energyCosts(): Response
    {
        $tenantId = $this->tenantId();

        $costs = DB::table('energy_consumption as ec')
            ->where('ec.tenant_id', $tenantId)
            ->select([
                DB::raw('DATE_TRUNC(\'month\', ec.consumption_date) as month'),
                DB::raw('SUM(ec.cost) as total_cost'),
                DB::raw('SUM(ec.consumption_value) as total_consumption'),
            ])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return Inertia::render('Energy/Costs', [
            'costs' => $costs,
        ]);
    }

    // ============ Sustainability / Carbon Tracking ============

    public function sustainabilityDashboard(): Response
    {
        $tenantId = $this->tenantId();

        $carbon = DB::table('carbon_tracking as ct')
            ->where('ct.tenant_id', $tenantId)
            ->select(['ct.id', 'ct.entry_date', 'ct.scope', 'ct.carbon_emissions_tons', 'ct.source'])
            ->orderByDesc('ct.entry_date')
            ->limit(20)
            ->get();

        $waste = DB::table('waste_tracking as wt')
            ->where('wt.tenant_id', $tenantId)
            ->select(['wt.id', 'wt.entry_date', 'wt.waste_type', 'wt.quantity_tons', 'wt.disposal_method'])
            ->orderByDesc('wt.entry_date')
            ->limit(20)
            ->get();

        $summary = [
            'total_carbon_tons' => $carbon->sum('carbon_emissions_tons'),
            'total_waste_tons' => $waste->sum('quantity_tons'),
        ];

        return Inertia::render('Sustainability/Dashboard', [
            'carbon' => $carbon,
            'waste' => $waste,
            'summary' => $summary,
        ]);
    }

    public function carbonTracking(): Response
    {
        $tenantId = $this->tenantId();

        $carbon = DB::table('carbon_tracking as ct')
            ->where('ct.tenant_id', $tenantId)
            ->select(['ct.id', 'ct.entry_date', 'ct.scope', 'ct.carbon_emissions_tons', 'ct.source', 'ct.notes'])
            ->orderByDesc('ct.entry_date')
            ->get();

        return Inertia::render('Sustainability/Carbon', [
            'carbon' => $carbon,
        ]);
    }

    public function createCarbonEntry(): Response
    {
        $employees = DB::table('employees')
            ->where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->select(['id', 'employee_code', 'name'])
            ->orderBy('employee_code')
            ->get();

        return Inertia::render('Sustainability/CreateCarbon', [
            'employees' => $employees,
        ]);
    }

    public function wasteTracking(): Response
    {
        $tenantId = $this->tenantId();

        $waste = DB::table('waste_tracking as wt')
            ->where('wt.tenant_id', $tenantId)
            ->select(['wt.id', 'wt.entry_date', 'wt.waste_type', 'wt.quantity_tons', 'wt.disposal_method', 'wt.notes'])
            ->orderByDesc('wt.entry_date')
            ->get();

        return Inertia::render('Sustainability/Waste', [
            'waste' => $waste,
        ]);
    }

    public function createWasteEntry(): Response
    {
        $employees = DB::table('employees')
            ->where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->select(['id', 'employee_code', 'name'])
            ->orderBy('employee_code')
            ->get();

        return Inertia::render('Sustainability/CreateWaste', [
            'employees' => $employees,
        ]);
    }

    // ============ Communications — Email Templates ============

    public function emailTemplates(): Response
    {
        $tenantId = $this->tenantId();

        $templates = DB::table('email_templates as et')
            ->where('et.tenant_id', $tenantId)
            ->select(['et.id', 'et.template_name', 'et.template_type', 'et.subject', 'et.status', 'et.created_at'])
            ->orderBy('et.template_name')
            ->get();

        return Inertia::render('Communications/EmailTemplates', [
            'templates' => $templates,
        ]);
    }

    public function createEmailTemplate(): Response
    {
        return Inertia::render('Communications/CreateEmailTemplate');
    }

    public function editEmailTemplate(string $id): Response
    {
        $template = DB::table('email_templates')
            ->where('id', $id)
            ->where('tenant_id', $this->tenantId())
            ->first();

        return Inertia::render('Communications/EditEmailTemplate', [
            'template' => $template,
        ]);
    }

    public function emailLogs(): Response
    {
        $tenantId = $this->tenantId();

        $logs = DB::table('email_logs as el')
            ->where('el.tenant_id', $tenantId)
            ->select(['el.id', 'el.template_name', 'el.recipient', 'el.subject', 'el.status', 'el.sent_at'])
            ->orderByDesc('el.sent_at')
            ->get();

        return Inertia::render('Communications/EmailLogs', [
            'logs' => $logs,
        ]);
    }

    // ============ Communications — SMS ============

    public function smsTemplates(): Response
    {
        $tenantId = $this->tenantId();

        $templates = DB::table('sms_templates as st')
            ->where('st.tenant_id', $tenantId)
            ->select(['st.id', 'st.template_name', 'st.message', 'st.status', 'st.created_at'])
            ->orderBy('st.template_name')
            ->get();

        return Inertia::render('Communications/SMSTemplates', [
            'templates' => $templates,
        ]);
    }

    public function createSMSTemplate(): Response
    {
        return Inertia::render('Communications/CreateSMSTemplate');
    }

    public function smsLogs(): Response
    {
        $tenantId = $this->tenantId();

        $logs = DB::table('sms_logs as sl')
            ->where('sl.tenant_id', $tenantId)
            ->select(['sl.id', 'sl.recipient', 'sl.message', 'sl.status', 'sl.sent_at'])
            ->orderByDesc('sl.sent_at')
            ->get();

        return Inertia::render('Communications/SMSLogs', [
            'logs' => $logs,
        ]);
    }

    public function sendSMS(Request $request): Response
    {
        $tenantId = $this->tenantId();

        $validated = $request->validate([
            'recipient' => 'required|string',
            'message' => 'required|string',
        ]);

        DB::table('sms_logs')->insert([
            'tenant_id' => $tenantId,
            'recipient' => $validated['recipient'],
            'message' => $validated['message'],
            'status' => 'sent',
            'sent_at' => now(),
            'created_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    // ============ Barcode / QR Generator ============

    public function barcodeGenerator(): Response
    {
        return Inertia::render('Tools/BarcodeGenerator');
    }

    public function qrGenerator(): Response
    {
        return Inertia::render('Tools/QRGenerator');
    }

    // ============ Production Downtime Tracking ============

    public function downtimeTracking(): Response
    {
        $tenantId = $this->tenantId();

        $downtime = DB::table('production_downtime as pd')
            ->leftJoin('machines as m', 'm.id', '=', 'pd.machine_id')
            ->where('pd.tenant_id', $tenantId)
            ->select(['pd.id', 'pd.downtime_number', 'm.name as machine_name', 'pd.downtime_type',
                'pd.start_time', 'pd.end_time', 'pd.duration_minutes', 'pd.reason', 'pd.status'])
            ->orderByDesc('pd.start_time')
            ->get();

        $summary = [
            'total_downtime' => $downtime->sum('duration_minutes'),
            'total_incidents' => $downtime->count(),
        ];

        return Inertia::render('Production/Downtime', [
            'downtime' => $downtime,
            'summary' => $summary,
        ]);
    }

    public function createDowntimeEntry(): Response
    {
        $machines = DB::table('machines')
            ->where('tenant_id', $this->tenantId())
            ->where('status', 'active')
            ->select(['id', 'machine_code', 'name'])
            ->orderBy('machine_code')
            ->get();

        return Inertia::render('Production/CreateDowntime', [
            'machines' => $machines,
        ]);
    }

    // ============ Production Scrap & Waste Tracking ============

    public function scrapTracking(): Response
    {
        $tenantId = $this->tenantId();

        $scrap = DB::table('production_scrap as ps')
            ->leftJoin('items as i', 'i.id', '=', 'ps.item_id')
            ->where('ps.tenant_id', $tenantId)
            ->select(['ps.id', 'ps.scrap_number', 'i.name as item_name',
                'ps.quantity', 'ps.scrap_date', 'ps.reason', 'ps.status', 'ps.cost'])
            ->orderByDesc('ps.scrap_date')
            ->get();

        $summary = [
            'total_scrap' => $scrap->sum('quantity'),
            'total_cost' => $scrap->sum('cost'),
        ];

        return Inertia::render('Production/Scrap', [
            'scrap' => $scrap,
            'summary' => $summary,
        ]);
    }

    public function createScrapEntry(): Response
    {
        $items = DB::table('items')
            ->where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->select(['id', 'sku', 'name'])
            ->orderBy('sku')
            ->get();

        return Inertia::render('Production/CreateScrap', [
            'items' => $items,
        ]);
    }

    // ============ Maintenance — Tool Management ============

    public function toolManagement(): Response
    {
        $tenantId = $this->tenantId();

        $tools = DB::table('tools as t')
            ->where('t.tenant_id', $tenantId)
            ->select(['t.id', 't.tool_code', 't.tool_name', 't.tool_type', 't.status',
                't.purchase_date', 't.last_calibration', 't.next_calibration', 't.location'])
            ->orderBy('t.tool_code')
            ->get();

        return Inertia::render('Maintenance/Tools', [
            'tools' => $tools,
        ]);
    }

    public function createTool(): Response
    {
        return Inertia::render('Maintenance/CreateTool');
    }

    public function calibrateTool(string $id): Response
    {
        $tool = DB::table('tools')
            ->where('id', $id)
            ->where('tenant_id', $this->tenantId())
            ->first();

        return Inertia::render('Maintenance/CalibrateTool', [
            'tool' => $tool,
        ]);
    }

    // ============ Maintenance — Calibration Management ============

    public function calibrationManagement(): Response
    {
        $tenantId = $this->tenantId();

        $calibrations = DB::table('tool_calibrations as tc')
            ->leftJoin('tools as t', 't.id', '=', 'tc.tool_id')
            ->where('tc.tenant_id', $tenantId)
            ->select(['tc.id', 't.tool_code', 't.tool_name', 'tc.calibration_date',
                'tc.next_calibration_date', 'tc.calibrated_by', 'tc.status', 'tc.certificate_number'])
            ->orderBy('tc.next_calibration_date')
            ->get();

        return Inertia::render('Maintenance/Calibration', [
            'calibrations' => $calibrations,
        ]);
    }

    public function calibrationSchedule(): Response
    {
        $tenantId = $this->tenantId();

        $upcoming = DB::table('tools as t')
            ->where('t.tenant_id', $tenantId)
            ->where('t.next_calibration_date', '<=', now()->addDays(30))
            ->where('t.next_calibration_date', '>=', now())
            ->select(['t.id', 't.tool_code', 't.tool_name', 't.next_calibration_date'])
            ->orderBy('t.next_calibration_date')
            ->get();

        return Inertia::render('Maintenance/CalibrationSchedule', [
            'upcoming' => $upcoming,
        ]);
    }

    // ============ Engineering — Change Orders (ECO) ============

    public function changeOrders(): Response
    {
        $tenantId = $this->tenantId();

        $changes = DB::table('engineering_change_orders as eco')
            ->where('eco.tenant_id', $tenantId)
            ->select(['eco.id', 'eco.change_number', 'eco.title', 'eco.change_type',
                'eco.status', 'eco.requested_date', 'eco.approved_date', 'eco.requested_by'])
            ->orderByDesc('eco.requested_date')
            ->get();

        return Inertia::render('Engineering/ChangeOrders', [
            'changes' => $changes,
        ]);
    }

    public function createChangeOrder(): Response
    {
        $employees = DB::table('employees')
            ->where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->select(['id', 'employee_code', 'name'])
            ->orderBy('employee_code')
            ->get();

        return Inertia::render('Engineering/CreateChangeOrder', [
            'employees' => $employees,
        ]);
    }

    public function changeOrderDetail(string $id): Response
    {
        $change = DB::table('engineering_change_orders')
            ->where('id', $id)
            ->where('tenant_id', $this->tenantId())
            ->first();

        return Inertia::render('Engineering/ChangeOrderDetail', [
            'change' => $change,
        ]);
    }

    // ============ HR — Training Matrix ============

    public function trainingMatrix(): Response
    {
        $tenantId = $this->tenantId();

        $matrix = DB::table('training_matrix as tm')
            ->leftJoin('employees as e', 'e.id', '=', 'tm.employee_id')
            ->where('tm.tenant_id', $tenantId)
            ->select(['tm.id', 'e.employee_code', 'e.name as employee_name',
                'tm.skill_name', 'tm.skill_level', 'tm.certification_date', 'tm.expiry_date', 'tm.status'])
            ->orderBy('e.employee_code')
            ->get();

        return Inertia::render('HR/TrainingMatrix', [
            'matrix' => $matrix,
        ]);
    }

    public function createTrainingMatrixEntry(): Response
    {
        $employees = DB::table('employees')
            ->where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->select(['id', 'employee_code', 'name'])
            ->orderBy('employee_code')
            ->get();

        return Inertia::render('HR/CreateTrainingMatrix', [
            'employees' => $employees,
        ]);
    }

    // ============ Analytics — Advanced Charts ============

    public function advancedAnalytics(): Response
    {
        $tenantId = $this->tenantId();

        $productionByMonth = DB::table('production_orders as po')
            ->where('po.tenant_id', $tenantId)
            ->whereIn('po.status', ['completed', 'closed'])
            ->whereDate('po.actual_end_date', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw('DATE_TRUNC(\'month\', po.actual_end_date) as month, SUM(po.good_quantity) as qty')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $revenueByMonth = DB::table('customer_invoices as ci')
            ->where('ci.tenant_id', $tenantId)
            ->where('ci.status', 'paid')
            ->whereDate('ci.invoice_date', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw('DATE_TRUNC(\'month\', ci.invoice_date) as month, SUM(ci.total_amount) as revenue')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $qualityByMonth = DB::table('quality_inspections as qi')
            ->where('qi.tenant_id', $tenantId)
            ->whereDate('qi.inspected_at', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw('DATE_TRUNC(\'month\', qi.inspected_at) as month, SUM(CASE WHEN qi.status = \'passed\' THEN 1 ELSE 0 END) as passed, SUM(CASE WHEN qi.status IN (\'failed\', \'rejected\') THEN 1 ELSE 0 END) as failed')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return Inertia::render('Analytics/Advanced', [
            'production_by_month' => $productionByMonth,
            'revenue_by_month' => $revenueByMonth,
            'quality_by_month' => $qualityByMonth,
        ]);
    }

    // ============ Print / PDF Reports ============

    public function printReport(string $reportType): Response
    {
        $tenantId = $this->tenantId();

        $data = [];
        switch ($reportType) {
            case 'production':
                $data = DB::table('production_orders as po')
                    ->where('po.tenant_id', $tenantId)
                    ->select(['po.order_number', 'po.status', 'po.planned_quantity', 'po.good_quantity', 'po.planned_start_date'])
                    ->orderByDesc('po.planned_start_date')
                    ->get();
                break;
            case 'inventory':
                $data = DB::table('stock_balances as sb')
                    ->join('items as i', 'i.id', '=', 'sb.item_id')
                    ->where('sb.tenant_id', $tenantId)
                    ->select(['i.sku', 'i.name as item_name', 'sb.on_hand'])
                    ->orderBy('i.sku')
                    ->get();
                break;
            case 'sales':
                $data = DB::table('sales_orders as so')
                    ->where('so.tenant_id', $tenantId)
                    ->select(['so.so_number', 'so.status', 'so.total_amount', 'so.order_date'])
                    ->orderByDesc('so.order_date')
                    ->get();
                break;
        }

        return Inertia::render('Reports/PrintReport', [
            'report_type' => $reportType,
            'data' => $data,
        ]);
    }
}

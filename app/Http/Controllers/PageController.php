<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
            $roleData['work_orders_today'] = (int) DB::table('work_orders')->where('tenant_id', $tenantId)->whereDate('planned_start_date', today())->count();
            $roleData['quality_pending'] = (int) DB::table('quality_inspections')->where('tenant_id', $tenantId)->where('status', 'pending')->count();
            $roleData['low_stock_items'] = (int) DB::table('stock_balances as sb')
                ->join('items as i', 'i.id', '=', 'sb.item_id')
                ->where('sb.tenant_id', $tenantId)
                ->whereColumn('sb.on_hand', '<', 'i.reorder_level')
                ->count();
        }

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

        $query = DB::table('journal_entries')
            ->where('tenant_id', $tenantId)
            ->select(['id', 'reference', 'document_date', 'status', 'description'])
            ->orderByDesc('document_date')
            ->orderByDesc('id');

        $payload = $this->paginate(
            $this->search($query, $request->query('q'), ['reference', 'description'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['document_date'] = $this->day($row['document_date']);

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

        return Inertia::render('Accounting', [
            'journal_entries' => $payload,
            'finance_summary' => [
                'trial_balance' => $this->money($trialBalance),
                'receivable' => $this->money($receivable),
                'payable' => $this->money($payable),
            ],
        ]);
    }

    public function chartOfAccounts(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('chart_of_accounts as coa')
            ->where('coa.tenant_id', $tenantId)
            ->select([
                'coa.id',
                'coa.code',
                'coa.name',
                'coa.type',
                'coa.allow_direct_posting',
                'coa.is_cash_account',
                'coa.is_bank_account',
            ])
            ->orderBy('coa.code');

        $payload = $this->paginate($query);

        $payload['data'] = array_map(function (array $row) {
            $row['allow_direct_posting'] = (bool) $row['allow_direct_posting'];
            $row['is_cash_account'] = (bool) $row['is_cash_account'];
            $row['is_bank_account'] = (bool) $row['is_bank_account'];

            return $row;
        }, $payload['data']);

        $byType = DB::table('chart_of_accounts')
            ->where('tenant_id', $tenantId)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get();

        $typeCounts = [];
        foreach ($byType as $t) {
            $typeCounts[$t->type] = (int) $t->count;
        }

        return Inertia::render('AccountingChartOfAccounts', [
            'accounts' => $payload,
            'type_counts' => $typeCounts,
        ]);
    }

    public function trialBalancePage(): Response
    {
        $tenantId = $this->tenantId();

        $accounts = DB::table('chart_of_accounts as coa')
            ->where('coa.tenant_id', $tenantId)
            ->where('coa.allow_direct_posting', true)
            ->select(['coa.id', 'coa.code', 'coa.name', 'coa.type'])
            ->orderBy('coa.code')
            ->get();

        $rows = $accounts->map(function ($account) use ($tenantId) {
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

            $balance = $debit - $credit;

            return [
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
        ]);
    }

    public function profitAndLossPage(): Response
    {
        $tenantId = $this->tenantId();

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

        $getAccountTurnover = function (array $account) use ($tenantId) {
            $debit = (float) DB::table('journal_lines as jl')
                ->join('journal_entries as je', 'je.id', '=', 'jl.entry_id')
                ->where('je.tenant_id', $tenantId)
                ->where('je.status', 'posted')
                ->where('jl.account_id', $account['id'])
                ->sum('jl.debit_amount_base');

            $credit = (float) DB::table('journal_lines as jl')
                ->join('journal_entries as je', 'je.id', '=', 'jl.entry_id')
                ->where('je.tenant_id', $tenantId)
                ->where('je.status', 'posted')
                ->where('jl.account_id', $account['id'])
                ->sum('jl.credit_amount_base');

            return [
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
        ]);
    }

    public function balanceSheetPage(): Response
    {
        $tenantId = $this->tenantId();

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

        $getAccountBalance = function (array $account) use ($tenantId) {
            $debit = (float) DB::table('journal_lines as jl')
                ->join('journal_entries as je', 'je.id', '=', 'jl.entry_id')
                ->where('je.tenant_id', $tenantId)
                ->where('je.status', 'posted')
                ->where('jl.account_id', $account['id'])
                ->sum('jl.debit_amount_base');

            $credit = (float) DB::table('journal_lines as jl')
                ->join('journal_entries as je', 'je.id', '=', 'jl.entry_id')
                ->where('je.tenant_id', $tenantId)
                ->where('je.status', 'posted')
                ->where('jl.account_id', $account['id'])
                ->sum('jl.credit_amount_base');

            return [
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
        ]);
    }

    public function createJournalEntry(): Response
    {
        return Inertia::render('Accounting/JournalEntryCreate');
    }

    public function createAccountPage(): Response
    {
        return Inertia::render('Accounting/AccountCreate');
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

    public function warehouses(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('warehouses')
            ->where('tenant_id', $tenantId)
            ->select(['id', 'code', 'name', 'type', 'location', 'is_active'])
            ->orderBy('code');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['code', 'name', 'type', 'location'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['status'] = $row['is_active'] ? 'active' : 'inactive';
            unset($row['is_active']);

            return $row;
        }, $payload['data']);

        $summary = DB::table('warehouses')
            ->where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(*) FILTER (WHERE is_active) as active')
            ->first();

        return Inertia::render('Warehouses', [
            'warehouses' => $payload,
            'warehouse_summary' => [
                'total' => (int) $summary->total,
                'active' => (int) $summary->active,
            ],
        ]);
    }

    public function createWarehouse(): Response
    {
        return Inertia::render('Warehouses/Create');
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

    public function bankReconciliation(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('bank_reconciliations as br')
            ->leftJoin('chart_of_accounts as coa', 'coa.id', '=', 'br.account_id')
            ->where('br.tenant_id', $tenantId)
            ->select([
                'br.id', 'br.reference', 'coa.name as account_name',
                'br.statement_date', 'br.book_balance', 'br.bank_balance',
                'br.status', 'br.reconciled_date',
            ])
            ->orderByDesc('br.statement_date')
            ->orderByDesc('br.id');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['br.reference', 'coa.name'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['statement_date'] = $this->day($row['statement_date']);
            $row['reconciled_date'] = $this->day($row['reconciled_date']);
            $row['status_label'] = ucfirst(str_replace('_', ' ', $row['status']));

            return $row;
        }, $payload['data']);

        return Inertia::render('Accounting/BankReconciliation', [
            'reconciliations' => $payload,
        ]);
    }

    public function createBankReconciliation(): Response
    {
        $accounts = DB::table('chart_of_accounts')
            ->where('tenant_id', $this->tenantId())
            ->where('is_bank_account', true)
            ->select(['id', 'code', 'name'])
            ->orderBy('code')
            ->get();

        return Inertia::render('Accounting/CreateBankReconciliation', [
            'accounts' => $accounts,
        ]);
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
            ->leftJoin('items as i', 'i.id', '=', 'po.item_id')
            ->where('wo.tenant_id', $tenantId)
            ->select([
                'wo.id', 'wo.work_order_number', 'wo.status',
                'wo.planned_start_date', 'wo.planned_end_date',
                'wo.actual_start_date', 'wo.actual_end_date',
                'wo.quantity', 'wo.completed_quantity',
                'po.order_number as production_order',
                'i.name as item_name',
            ])
            ->orderByDesc('wo.planned_start_date')
            ->orderByDesc('wo.id');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['wo.work_order_number', 'po.order_number', 'i.name'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['planned_start_date'] = $this->day($row['planned_start_date']);
            $row['planned_end_date'] = $this->day($row['planned_end_date']);
            $row['actual_start_date'] = $this->day($row['actual_start_date']);
            $row['actual_end_date'] = $this->day($row['actual_end_date']);
            $row['status_label'] = ucfirst(str_replace('_', ' ', $row['status']));
            $row['completion_pct'] = $row['quantity'] > 0
                ? round(($row['completed_quantity'] / $row['quantity']) * 100, 1)
                : 0;

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

        $query = DB::table('fixed_assets as fa')
            ->leftJoin('chart_of_accounts as coa', 'coa.id', '=', 'fa.account_id')
            ->where('fa.tenant_id', $tenantId)
            ->select([
                'fa.id', 'fa.asset_code', 'fa.asset_name', 'fa.asset_category',
                'fa.purchase_date', 'fa.purchase_cost', 'fa.accumulated_depreciation',
                'fa.net_book_value', 'fa.status', 'fa.location',
                'coa.name as account_name',
            ])
            ->orderBy('fa.asset_code');

        $payload = $this->paginate(
            $this->search($query, request('q'), ['fa.asset_code', 'fa.asset_name', 'fa.location'])
        );

        $payload['data'] = array_map(function (array $row) {
            $row['purchase_date'] = $this->day($row['purchase_date']);
            $row['purchase_cost'] = $this->money($row['purchase_cost']);
            $row['accumulated_depreciation'] = $this->money($row['accumulated_depreciation']);
            $row['net_book_value'] = $this->money($row['net_book_value']);
            $row['status_label'] = ucfirst(str_replace('_', ' ', $row['status']));

            return $row;
        }, $payload['data']);

        $summary = DB::table('fixed_assets')
            ->where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COALESCE(SUM(purchase_cost), 0) as total_cost')
            ->selectRaw('COALESCE(SUM(net_book_value), 0) as total_nbv')
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

    public function notifications(): Response
    {
        $tenantId = $this->tenantId();

        $query = DB::table('notifications as n')
            ->leftJoin('users as u', 'u.id', '=', 'n.user_id')
            ->where('n.tenant_id', $tenantId)
            ->select([
                'n.id', 'n.title', 'n.body', 'n.type', 'n.data',
                'n.read_at', 'n.created_at',
                'u.name as actor_name',
            ])
            ->orderByDesc('n.created_at');

        $payload = $this->paginate($query);

        $payload['data'] = array_map(function (array $row) {
            $row['created_at'] = $this->day($row['created_at']);
            $row['is_read'] = !is_null($row['read_at']);
            $row['data'] = is_string($row['data']) ? json_decode($row['data'], true) : ($row['data'] ?? []);

            return $row;
        }, $payload['data']);

        $unreadCount = (int) DB::table('notifications')
            ->where('tenant_id', $tenantId)
            ->whereNull('read_at')
            ->count();

        return Inertia::render('Notifications', [
            'notifications' => $payload,
            'unread_count' => $unreadCount,
        ]);
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
            ->leftJoin('items as i', 'i.id', '=', 'po.item_id')
            ->where('wo.tenant_id', $tenantId)
            ->select([
                'wo.id', 'wo.work_order_number', 'wo.status',
                'wo.planned_start_date', 'wo.planned_end_date',
                'wo.actual_start_date', 'wo.actual_end_date',
                'wo.quantity', 'wo.completed_quantity',
                'po.order_number as production_order',
                'i.name as item_name',
            ])
            ->orderBy('wo.planned_start_date')
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
            ->leftJoin('items as i', 'i.id', '=', 'po.item_id')
            ->where('wo.tenant_id', $tenantId)
            ->select([
                'wo.id', 'wo.work_order_number', 'wo.status',
                'wo.planned_start_date', 'wo.planned_end_date',
                'wo.actual_start_date', 'wo.actual_end_date',
                'wo.quantity', 'wo.completed_quantity',
                'po.order_number as production_order',
                'i.name as item_name',
            ])
            ->orderBy('wo.planned_start_date')
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

    public function auditTrail(): Response
    {
        $tenantId = $this->tenantId();

        $auditLogs = DB::table('audit_trail as at')
            ->leftJoin('users as u', 'u.id', '=', 'at.user_id')
            ->where('at.tenant_id', $tenantId)
            ->select(['at.id', 'at.event_type', 'at.table_name', 'at.record_id',
                'at.old_values', 'at.new_values', 'at.created_at',
                'u.name as user_name'])
            ->orderByDesc('at.created_at')
            ->paginate(50);

        return Inertia::render('Compliance/AuditTrail', [
            'audit_logs' => $auditLogs,
        ]);
    }

    // ============ Workflow Automation — Approvals ============

    public function approvals(): Response
    {
        $tenantId = $this->tenantId();

        $approvals = DB::table('workflow_approvals as wa')
            ->leftJoin('users as u', 'u.id', '=', 'wa.approver_id')
            ->where('wa.tenant_id', $tenantId)
            ->select(['wa.id', 'wa.workflow_type', 'wa.reference_number',
                'wa.status', 'wa.requested_at', 'wa.approved_at',
                'wa.comments', 'u.name as approver_name'])
            ->orderByDesc('wa.requested_at')
            ->get();

        $summary = [
            'total_approvals' => $approvals->count(),
            'pending_approvals' => $approvals->where('status', 'pending')->count(),
            'approved' => $approvals->where('status', 'approved')->count(),
            'rejected' => $approvals->where('status', 'rejected')->count(),
        ];

        return Inertia::render('Workflow/Approvals', [
            'approvals' => $approvals,
            'summary' => $summary,
        ]);
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

    public function customerInvoices(): Response
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

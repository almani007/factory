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

        $productionToday = (float) DB::table('production_orders')
            ->where('tenant_id', $tenantId)
            ->whereDate('actual_end_date', today())
            ->whereIn('status', ['completed', 'closed'])
            ->sum('good_quantity');

        $rawMaterialValue = (float) DB::table('stock_balances as sb')
            ->join('items as i', 'i.id', '=', 'sb.item_id')
            ->join('item_categories as c', 'c.id', '=', 'i.category_id')
            ->where('sb.tenant_id', $tenantId)
            ->where('c.code', 'RM')
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
            ->limit(4)
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

        return Inertia::render('Dashboard', [
            'stats' => [
                'production_today' => $productionToday,
                'raw_material_value' => $rawMaterialValue,
                'orders_delayed' => $ordersDelayed,
                'finished_goods' => $finishedGoods,
                'quality_rejection_rate' => $inspected > 0 ? round(($rejected / $inspected) * 100, 1) : 0.0,
                'actual_unit_cost' => round($unitCost, 2),
            ],
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
}

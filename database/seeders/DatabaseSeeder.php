<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Demo dataset for Brixel FactoryOS.
 *
 * The stack seeds itself on first boot (see docker/php/entrypoint.sh), so this
 * runs against an empty database and creates one fully-populated primary
 * tenant plus a handful of secondary tenants for the platform console.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('tenants')->exists()) {
            return;
        }

        $now = now();
        $today = Carbon::today();

        /*
        |--------------------------------------------------------------------------
        | Tenants & organisation
        |--------------------------------------------------------------------------
        */
        $tenantId = DB::table('tenants')->insertGetId([
            'name' => 'Zoventra Industries',
            'slug' => 'zoventra-industries',
            'subdomain' => 'zoventra',
            'email' => 'operations@zoventra.test',
            'phone' => '+92 42 111 000 111',
            'address' => 'Plot 42, Sundar Industrial Estate, Lahore',
            'tax_id' => 'NTN-4210395-7',
            'currency' => 'PKR',
            'timezone' => 'Asia/Karachi',
            'locale' => 'en',
            'status' => 'active',
            'plan' => 'enterprise',
            'trial_ends_at' => null,
            'subscription_ends_at' => $today->copy()->addYear(),
            'settings' => json_encode([
                'costing_method' => 'weighted_average',
                'default_warehouse' => 'Main Raw Store',
                'fiscal_year_start' => '07-01',
                'quality_gate' => 'enabled',
            ]),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $secondaryTenants = [
            ['Metro Textiles', 'metro-textiles', 'admin@metrotextiles.test', 'growth', 'active'],
            ['Gulf Precision Works', 'gulf-precision', 'ops@gulfprecision.test', 'enterprise', 'active'],
            ['Northern Foods', 'northern-foods', 'it@northernfoods.test', 'starter', 'suspended'],
            ['Sindh Packaging Co.', 'sindh-packaging', 'hello@sindhpackaging.test', 'growth', 'active'],
        ];

        foreach ($secondaryTenants as [$name, $slug, $email, $plan, $status]) {
            DB::table('tenants')->insert([
                'name' => $name,
                'slug' => $slug,
                'subdomain' => $slug,
                'email' => $email,
                'phone' => '+92 21 111 222 333',
                'address' => 'Karachi, Pakistan',
                'currency' => 'PKR',
                'timezone' => 'Asia/Karachi',
                'locale' => 'en',
                'status' => $status,
                'plan' => $plan,
                'trial_ends_at' => null,
                'subscription_ends_at' => $today->copy()->addMonths(8),
                'settings' => json_encode([]),
                'is_active' => $status === 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $companyId = DB::table('companies')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'Zoventra Manufacturing (Pvt) Ltd',
            'slug' => 'zoventra-manufacturing',
            'email' => 'finance@zoventra.test',
            'phone' => '+92 42 111 000 111',
            'address' => 'Plot 42, Sundar Industrial Estate',
            'city' => 'Lahore',
            'country' => 'Pakistan',
            'tax_id' => 'NTN-4210395-7',
            'currency' => 'PKR',
            'tax_jurisdiction' => 'PK',
            'timezone' => 'Asia/Karachi',
            'locale' => 'en',
            'settings' => json_encode(['invoice_prefix' => 'INV']),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $factoryId = DB::table('factories')->insertGetId([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'name' => 'Lahore Plant 1',
            'code' => 'LHR-01',
            'address' => 'Plot 42, Sundar Industrial Estate',
            'city' => 'Lahore',
            'country' => 'Pakistan',
            'timezone' => 'Asia/Karachi',
            'working_hours' => json_encode(['start' => '08:00', 'end' => '20:00']),
            'shifts' => json_encode(['A' => '08:00-16:00', 'B' => '16:00-00:00']),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('factories')->insert([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'name' => 'Karachi Assembly Unit',
            'code' => 'KHI-02',
            'address' => 'Korangi Industrial Area',
            'city' => 'Karachi',
            'country' => 'Pakistan',
            'timezone' => 'Asia/Karachi',
            'working_hours' => json_encode(['start' => '09:00', 'end' => '18:00']),
            'shifts' => json_encode(['A' => '09:00-18:00']),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $deptIds = [];
        foreach (['Production' => 'PROD', 'Quality' => 'QA', 'Stores' => 'STR', 'Finance' => 'FIN'] as $name => $code) {
            $deptIds[$code] = DB::table('departments')->insertGetId([
                'tenant_id' => $tenantId,
                'factory_id' => $factoryId,
                'name' => $name,
                'code' => $code,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */
        $users = [
            ['Ayesha Khan', 'admin@zoventra.test', 'super_admin', 'PROD'],
            ['Bilal Ahmed', 'bilal.ahmed@zoventra.test', 'production_manager', 'PROD'],
            ['Sana Iqbal', 'sana.iqbal@zoventra.test', 'quality_manager', 'QA'],
            ['Usman Tariq', 'usman.tariq@zoventra.test', 'accountant', 'FIN'],
            ['Hira Malik', 'hira.malik@zoventra.test', 'store_keeper', 'STR'],
        ];

        $userIds = [];
        foreach ($users as [$name, $email, $role, $dept]) {
            $userIds[] = DB::table('users')->insertGetId([
                'tenant_id' => $tenantId,
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => $role,
                'phone' => '+92 300 1234567',
                'is_active' => true,
                'mfa_enabled' => false,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        $adminId = $userIds[0];
        $productionManagerId = $userIds[1];
        $qualityManagerId = $userIds[2];

        foreach ($userIds as $id) {
            DB::table('user_factory_access')->insert([
                'user_id' => $id,
                'factory_id' => $factoryId,
                'permissions' => json_encode(['*']),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach (['super_admin' => 'Super Administrator', 'production_manager' => 'Production Manager', 'quality_manager' => 'Quality Manager', 'accountant' => 'Accountant', 'store_keeper' => 'Store Keeper'] as $roleName => $label) {
            DB::table('roles')->insert([
                'tenant_id' => $tenantId,
                'name' => $roleName,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Units, categories, warehouses, items
        |--------------------------------------------------------------------------
        */
        $unitIds = [];
        foreach ([['Pieces', 'PCS', 'pcs'], ['Metres', 'M', 'm'], ['Kilograms', 'KG', 'kg'], ['Litres', 'L', 'l']] as [$name, $code, $symbol]) {
            $unitIds[$code] = DB::table('units')->insertGetId([
                'tenant_id' => $tenantId,
                'name' => $name,
                'code' => $code,
                'symbol' => $symbol,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $categoryIds = [];
        foreach ([['Raw Materials', 'RM'], ['Components', 'CP'], ['Finished Goods', 'FG'], ['Packaging', 'PK']] as [$name, $code]) {
            $categoryIds[$code] = DB::table('item_categories')->insertGetId([
                'tenant_id' => $tenantId,
                'name' => $name,
                'code' => $code,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $warehouseIds = [];
        foreach ([['Main Raw Store', 'RAW-01', 'general'], ['Finished Goods Store', 'FG-01', 'finished_goods']] as [$name, $code, $type]) {
            $warehouseIds[$code] = DB::table('warehouses')->insertGetId([
                'tenant_id' => $tenantId,
                'factory_id' => $factoryId,
                'name' => $name,
                'code' => $code,
                'address' => 'Plot 42, Sundar Industrial Estate, Lahore',
                'type' => $type,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $items = [
            // sku, name, category, unit, purchase price, production cost, on hand, warehouse
            ['RM-2001', 'Stainless Steel Sheet 1.2mm', 'RM', 'KG', 1850, 0, 1240, 'RAW-01'],
            ['RM-2002', 'Powder Coat Paint — Graphite', 'RM', 'KG', 720, 0, 380, 'RAW-01'],
            ['RM-2003', 'Food-grade Rubber Gasket', 'RM', 'PCS', 45, 0, 8600, 'RAW-01'],
            ['RM-2004', 'Polypropylene Granules', 'RM', 'KG', 310, 0, 2150, 'RAW-01'],
            ['CP-3001', 'Bottle Cap Assembly', 'CP', 'PCS', 38, 0, 5400, 'RAW-01'],
            ['CP-3002', 'Reinforced Handle Grip', 'CP', 'PCS', 62, 0, 3100, 'RAW-01'],
            ['FG-1001', 'Insulated Water Bottle 750ml', 'FG', 'PCS', 0, 149, 1840, 'FG-01'],
            ['FG-1002', 'Steel Storage Cabinet 4-tier', 'FG', 'PCS', 0, 4850, 260, 'FG-01'],
            ['FG-1003', 'Aluminium Tool Case Pro', 'FG', 'PCS', 0, 2150, 415, 'FG-01'],
            ['FG-1004', 'Industrial Fan Housing 18"', 'FG', 'PCS', 0, 1320, 190, 'FG-01'],
            ['PK-4001', 'Carton Box — Large', 'PK', 'PCS', 95, 0, 4200, 'RAW-01'],
            ['PK-4002', 'Shipping Label Roll', 'PK', 'PCS', 210, 0, 540, 'RAW-01'],
        ];

        $itemIds = [];
        $itemNames = [];
        $itemValues = [];
        foreach ($items as [$sku, $name, $cat, $unit, $purchase, $cost, $onHand, $warehouse]) {
            $itemIds[$sku] = DB::table('items')->insertGetId([
                'tenant_id' => $tenantId,
                'factory_id' => $factoryId,
                'category_id' => $categoryIds[$cat],
                'sku' => $sku,
                'name' => $name,
                'description' => $name . ' used in Zoventra production.',
                'brand' => 'Zoventra',
                'unit_id' => $unitIds[$unit],
                'min_stock' => 100,
                'reorder_level' => 200,
                'default_purchase_price' => $purchase,
                'standard_production_cost' => $cost,
                'is_active' => true,
                'track_batch' => false,
                'track_serial' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $itemNames[$sku] = $name;
            $itemValues[$sku] = $purchase;

            DB::table('stock_balances')->insert([
                'tenant_id' => $tenantId,
                'factory_id' => $factoryId,
                'warehouse_id' => $warehouseIds[$warehouse],
                'item_id' => $itemIds[$sku],
                'on_hand' => $onHand,
                'reserved' => 0,
                'in_transit' => 0,
                'unit_id' => $unitIds[$unit],
                'last_movement_at' => $now->copy()->subHours(6),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Suppliers, customers, sales orders
        |--------------------------------------------------------------------------
        */
        $suppliers = [
            ['Karachi Steel Mills', 'SUP-001', 'Imran Sheikh', 'sales@karachisteel.test', 'Metals'],
            ['Pak Polymers Ltd', 'SUP-002', 'Nadia Farooq', 'orders@pakpolymers.test', 'Plastics'],
            ['Descon Chemicals', 'SUP-003', 'Farhan Ali', 'supply@desconchem.test', 'Chemicals'],
            ['Atlas Hardware Co.', 'SUP-004', 'Rabia Aslam', 'info@atlashardware.test', 'Hardware'],
            ['Sunrise Packaging', 'SUP-005', 'Kamran Butt', 'hello@sunrisepack.test', 'Packaging'],
            ['Precision Tools Co.', 'SUP-006', 'Adeel Raza', 'tools@precisionco.test', 'Tooling'],
        ];

        $supplierIds = [];
        foreach ($suppliers as [$name, $code, $contact, $email, $category]) {
            $supplierIds[] = DB::table('suppliers')->insertGetId([
                'tenant_id' => $tenantId,
                'name' => $name,
                'code' => $code,
                'contact_person' => $contact,
                'email' => $email,
                'phone' => '+92 21 3456 7890',
                'address' => 'Industrial Area, Karachi',
                'tax_id' => 'NTN-' . random_int(1000000, 9999999),
                'payment_terms' => 'Net 30',
                'category' => $category,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $customers = [
            ['Habib Metro Retail', 'CUS-001', 'Zainab Qureshi', 'procurement@habibmetro.test', 2500000],
            ['Al-Fatah Stores', 'CUS-002', 'Ahmed Raza', 'buying@alfatah.test', 1800000],
            ['Metro Cash & Carry', 'CUS-003', 'Sadia Noor', 'orders@metro.test', 4200000],
            ['Daraz Marketplace', 'CUS-004', 'Hassan Javed', 'seller@daraz.test', 1200000],
            ['Sapphire Retail', 'CUS-005', 'Mariam Shah', 'supply@sapphire.test', 3000000],
            ['Imtiaz Super Market', 'CUS-006', 'Owais Khan', 'purchase@imtiaz.test', 950000],
        ];

        $customerIds = [];
        foreach ($customers as [$name, $code, $contact, $email, $limit]) {
            $customerIds[] = DB::table('customers')->insertGetId([
                'tenant_id' => $tenantId,
                'name' => $name,
                'code' => $code,
                'contact_person' => $contact,
                'email' => $email,
                'phone' => '+92 42 3567 8901',
                'address' => 'Gulberg III, Lahore',
                'city' => 'Lahore',
                'country' => 'Pakistan',
                'tax_id' => 'NTN-' . random_int(1000000, 9999999),
                'credit_limit' => $limit,
                'payment_terms' => 'Net 45',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $salesOrderIds = [];
        $salesOrderNumbers = [];
        foreach ([
            ['SO-2026-0101', 0, -12, 'confirmed'],
            ['SO-2026-0102', 1, -9, 'confirmed'],
            ['SO-2026-0103', 2, -7, 'in_production'],
            ['SO-2026-0104', 3, -5, 'confirmed'],
            ['SO-2026-0105', 4, -3, 'in_production'],
            ['SO-2026-0106', 5, -1, 'draft'],
            ['SO-2026-0107', 2, 0, 'confirmed'],
        ] as [$number, $customerIndex, $dayOffset, $status]) {
            $salesOrderIds[] = DB::table('sales_orders')->insertGetId([
                'tenant_id' => $tenantId,
                'customer_id' => $customerIds[$customerIndex],
                'so_number' => $number,
                'order_date' => $today->copy()->addDays($dayOffset),
                'delivery_date' => $today->copy()->addDays($dayOffset + 14),
                'status' => $status,
                'notes' => 'Scheduled fulfilment for ' . $customers[$customerIndex][0],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $salesOrderNumbers[] = $number;
        }

        /*
        |--------------------------------------------------------------------------
        | Bills of materials & production orders
        |--------------------------------------------------------------------------
        */
        $bom = [];
        $finishedSkus = ['FG-1001', 'FG-1002', 'FG-1003', 'FG-1004'];
        foreach ($finishedSkus as $sku) {
            $headerId = DB::table('bom_headers')->insertGetId([
                'tenant_id' => $tenantId,
                'factory_id' => $factoryId,
                'item_id' => $itemIds[$sku],
                'name' => 'BOM — ' . $itemNames[$sku],
                'description' => 'Standard bill of materials for ' . $itemNames[$sku],
                'is_active' => true,
                'is_default' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $versionId = DB::table('bom_versions')->insertGetId([
                'bom_header_id' => $headerId,
                'tenant_id' => $tenantId,
                'version' => '1.0',
                'effective_from' => $today->copy()->subMonths(6),
                'is_active' => true,
                'standard_material_cost' => 0,
                'notes' => 'Initial released version',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $bom[$sku] = [$headerId, $versionId];
        }

        // [order number, finished sku, qty, status, start offset, end offset, good qty, priority]
        $orderSpecs = [
            ['WO-2026-0101', 'FG-1001', 1200, 'completed', -14, -12, 1180, 'normal'],
            ['WO-2026-0102', 'FG-1002', 120, 'completed', -11, -9, 118, 'high'],
            ['WO-2026-0103', 'FG-1003', 400, 'completed', -8, -6, 392, 'normal'],
            ['WO-2026-0104', 'FG-1001', 900, 'in_production', -4, 2, 0, 'high'],
            ['WO-2026-0105', 'FG-1004', 250, 'in_production', -3, 1, 0, 'normal'],
            ['WO-2026-0106', 'FG-1002', 80, 'in_production', -2, 3, 0, 'normal'],
            ['WO-2026-0107', 'FG-1003', 300, 'quality_inspection', -6, 0, 0, 'high'],
            ['WO-2026-0108', 'FG-1001', 1500, 'approved', 0, 5, 0, 'normal'],
            ['WO-2026-0109', 'FG-1004', 200, 'approved', 0, 4, 0, 'normal'],
            ['WO-2026-0110', 'FG-1002', 60, 'pending_approval', 1, 8, 0, 'low'],
            ['WO-2026-0111', 'FG-1003', 150, 'draft', 3, 10, 0, 'normal'],
            ['WO-2026-0112', 'FG-1001', 500, 'cancelled', -5, -2, 0, 'low'],
            ['WO-2026-0113', 'FG-1001', 480, 'completed', 0, 0, 480, 'high'],
        ];

        $orderIds = [];
        $orderQuantities = [];
        foreach ($orderSpecs as $index => [$number, $sku, $qty, $status, $startOffset, $endOffset, $goodQty, $priority]) {
            [$headerId, $versionId] = $bom[$sku];

            $orderQuantities[] = $qty;
            $orderIds[] = DB::table('production_orders')->insertGetId([
                'tenant_id' => $tenantId,
                'factory_id' => $factoryId,
                'sales_order_id' => $index < count($salesOrderIds) ? $salesOrderIds[$index] : null,
                'bom_header_id' => $headerId,
                'bom_version_id' => $versionId,
                'order_number' => $number,
                'planned_quantity' => $qty,
                'actual_quantity' => $goodQty,
                'good_quantity' => $goodQty,
                'rejected_quantity' => $goodQty > 0 ? max(0, $qty - $goodQty) : 0,
                'rework_quantity' => 0,
                'scrap_quantity' => 0,
                'planned_start_date' => $today->copy()->addDays($startOffset)->setTime(8, 0),
                'planned_end_date' => $today->copy()->addDays($endOffset)->setTime(17, 0),
                'actual_start_date' => $startOffset <= 0 ? $today->copy()->addDays($startOffset)->setTime(8, 30) : null,
                'actual_end_date' => in_array($status, ['completed'], true) ? $today->copy()->addDays($endOffset)->setTime(16, 0) : null,
                'status' => $status,
                'priority' => $priority,
                'notes' => 'Demo work order for ' . $itemNames[$sku],
                'approved_by' => $productionManagerId,
                'approved_at' => $startOffset < 0 ? $now->copy()->subDays(abs($startOffset)) : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Quality inspections
        |--------------------------------------------------------------------------
        */
        $templates = [];
        foreach ([['Incoming Material Inspection', 'IT-IN', 'incoming'], ['In-Process Inspection', 'IT-IP', 'in_process'], ['Final Product Inspection', 'IT-FP', 'final']] as [$name, $code, $type]) {
            $templates[$type] = DB::table('inspection_templates')->insertGetId([
                'tenant_id' => $tenantId,
                'factory_id' => $factoryId,
                'name' => $name,
                'code' => $code,
                'inspection_type' => $type,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $inspectionSpecs = [
            [0, 'incoming', 'passed', -13],
            [1, 'in_process', 'passed', -12],
            [2, 'final', 'passed', -10],
            [3, 'in_process', 'passed', -9],
            [4, 'final', 'failed', -8],
            [5, 'incoming', 'passed', -6],
            [6, 'final', 'pending', -5],
            [7, 'in_process', 'passed', -4],
            [8, 'final', 'rework_required', -3],
            [9, 'incoming', 'passed', -2],
            [10, 'in_process', 'pending', -1],
            [11, 'final', 'passed', 0],
        ];

        foreach ($inspectionSpecs as [$orderIndex, $type, $status, $dayOffset]) {
            DB::table('quality_inspections')->insert([
                'tenant_id' => $tenantId,
                'factory_id' => $factoryId,
                'inspection_template_id' => $templates[$type],
                'inspection_type' => $type,
                'reference_type' => 'production_order',
                'reference_id' => $orderIds[$orderIndex],
                'status' => $status,
                'inspected_by' => $qualityManagerId,
                'inspected_at' => $today->copy()->addDays($dayOffset)->setTime(11, 0),
                'approved_by' => $status === 'passed' ? $adminId : null,
                'approved_at' => $status === 'passed' ? $today->copy()->addDays($dayOffset)->setTime(13, 0) : null,
                'notes' => ucfirst($status) . ' during ' . str_replace('_', ' ', $type) . ' inspection.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Stock movements & balances
        |--------------------------------------------------------------------------
        */
        $movementSpecs = [
            ['GRN-2026-0001', 'receipt', 'RM-2001', 'RAW-01', 800, -12],
            ['GRN-2026-0002', 'receipt', 'RM-2002', 'RAW-01', 240, -11],
            ['GRN-2026-0003', 'receipt', 'RM-2004', 'RAW-01', 1200, -9],
            ['ISS-2026-0001', 'issue', 'RM-2001', 'RAW-01', -320, -8],
            ['ISS-2026-0002', 'issue', 'CP-3001', 'RAW-01', 600, -7],
            ['PRD-2026-0001', 'production_receipt', 'FG-1001', 'FG-01', 1180, -6],
            ['PRD-2026-0002', 'production_receipt', 'FG-1002', 'FG-01', 118, -5],
            ['ADJ-2026-0001', 'adjustment', 'PK-4001', 'RAW-01', -40, -4],
            ['ISS-2026-0003', 'issue', 'RM-2003', 'RAW-01', 1500, -3],
            ['GRN-2026-0004', 'receipt', 'PK-4002', 'RAW-01', 300, -2],
            ['PRD-2026-0003', 'production_receipt', 'FG-1003', 'FG-01', 392, -1],
            ['ADJ-2026-0002', 'adjustment', 'RM-2002', 'RAW-01', 12, 0],
            ['ISS-2026-0004', 'issue', 'CP-3002', 'RAW-01', 420, 0],
            ['GRN-2026-0005', 'receipt', 'PK-4001', 'RAW-01', 900, 0],
        ];

        foreach ($movementSpecs as [$reference, $type, $sku, $warehouse, $qty, $dayOffset]) {
            $unitCost = $itemValues[$sku] ?: 149;
            $movementId = DB::table('stock_movements')->insertGetId([
                'tenant_id' => $tenantId,
                'factory_id' => $factoryId,
                'warehouse_id' => $warehouseIds[$warehouse],
                'movement_type' => $type,
                'reference' => $reference,
                'source_document_type' => str_starts_with($reference, 'GRN') ? 'goods_receipt' : null,
                'transaction_date' => $today->copy()->addDays($dayOffset)->setTime(10, 0),
                'posting_date' => $today->copy()->addDays($dayOffset)->setTime(10, 0),
                'unit_cost' => $unitCost,
                'total_cost' => abs($qty) * $unitCost,
                'user_id' => $userIds[4],
                'reason' => null,
                'posted_at' => $dayOffset <= 0 ? $now->copy()->subHours(4) : null,
                'posted_by' => $dayOffset <= 0 ? $adminId : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('stock_movement_lines')->insert([
                'movement_id' => $movementId,
                'tenant_id' => $tenantId,
                'item_id' => $itemIds[$sku],
                'warehouse_id' => $warehouseIds[$warehouse],
                'quantity' => abs($qty),
                'unit_id' => $unitIds['PCS'],
                'unit_cost' => $unitCost,
                'total_cost' => abs($qty) * $unitCost,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Accounting
        |--------------------------------------------------------------------------
        */
        $bookId = DB::table('accounting_books')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'Primary Ledger',
            'code' => 'PL-01',
            'base_currency' => 'PKR',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $accounts = [];
        foreach ([
            ['ACC-1000', 'Cash & Bank', 'asset'],
            ['ACC-1100', 'Accounts Receivable', 'asset'],
            ['ACC-1200', 'Raw Materials Inventory', 'asset'],
            ['ACC-2000', 'Accounts Payable', 'liability'],
            ['ACC-4000', 'Sales Revenue', 'revenue'],
            ['ACC-5000', 'Cost of Goods Sold', 'expense'],
            ['ACC-6000', 'Production Overhead', 'expense'],
        ] as [$code, $name, $type]) {
            $accounts[$code] = DB::table('chart_of_accounts')->insertGetId([
                'tenant_id' => $tenantId,
                'code' => $code,
                'name' => $name,
                'type' => $type,
                'allow_direct_posting' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $journalSpecs = [
            ['JE-2026-0001', 'Goods receipt GRN-2026-0001 — stainless steel', 'posted', -12, 420000, 'ACC-1200', 'ACC-2000'],
            ['JE-2026-0002', 'Production order WO-2026-0101 material issue', 'posted', -11, 310000, 'ACC-5000', 'ACC-1200'],
            ['JE-2026-0003', 'Production order WO-2026-0101 labour absorption', 'posted', -10, 185000, 'ACC-5000', 'ACC-2000'],
            ['JE-2026-0004', 'Sales invoice SO-2026-0101 — Habib Metro Retail', 'posted', -8, 560000, 'ACC-1100', 'ACC-4000'],
            ['JE-2026-0005', 'Supplier bill SUP-001 — Karachi Steel Mills', 'posted', -7, 290000, 'ACC-1200', 'ACC-2000'],
            ['JE-2026-0006', 'Production order WO-2026-0102 overhead absorption', 'submitted', -5, 120000, 'ACC-6000', 'ACC-2000'],
            ['JE-2026-0007', 'Stock adjustment ADJ-2026-0001', 'posted', -4, 65000, 'ACC-5000', 'ACC-1200'],
            ['JE-2026-0008', 'Customer receipt SO-2026-0102', 'posted', -2, 240000, 'ACC-1000', 'ACC-1100'],
            ['JE-2026-0009', 'Production order WO-2026-0103 variance posting', 'draft', -1, 150000, 'ACC-5000', 'ACC-1200'],
            ['JE-2026-0010', 'Monthly depreciation — plant & machinery', 'draft', 0, 90000, 'ACC-6000', 'ACC-1000'],
        ];

        foreach ($journalSpecs as [$reference, $description, $status, $dayOffset, $amount, $debitAccount, $creditAccount]) {
            $entryId = DB::table('journal_entries')->insertGetId([
                'tenant_id' => $tenantId,
                'accounting_book_id' => $bookId,
                'document_date' => $today->copy()->addDays($dayOffset),
                'posting_date' => $today->copy()->addDays($dayOffset),
                'currency' => 'PKR',
                'exchange_rate_to_base' => 1,
                'reference' => $reference,
                'description' => $description,
                'status' => $status,
                'created_by' => $userIds[3],
                'approved_by' => $status === 'posted' ? $adminId : null,
                'posted_at' => $status === 'posted' ? $today->copy()->addDays($dayOffset)->setTime(15, 0) : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ([[$debitAccount, $amount, 0], [$creditAccount, 0, $amount]] as [$accountCode, $debit, $credit]) {
                DB::table('journal_lines')->insert([
                    'entry_id' => $entryId,
                    'tenant_id' => $tenantId,
                    'account_id' => $accounts[$accountCode],
                    'debit_amount' => $debit,
                    'credit_amount' => $credit,
                    'debit_amount_base' => $debit,
                    'credit_amount_base' => $credit,
                    'currency' => 'PKR',
                    'exchange_rate' => 1,
                    'description' => $description,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Receivables & payables
        |--------------------------------------------------------------------------
        */
        $invoiceSpecs = [
            ['INV-2026-0001', 0, 428000, 'posted', -8],
            ['INV-2026-0002', 1, 312500, 'posted', -6],
            ['INV-2026-0003', 2, 890000, 'posted', -4],
            ['INV-2026-0004', 3, 145000, 'paid', -10],
            ['INV-2026-0005', 4, 265000, 'posted', -2],
        ];

        foreach ($invoiceSpecs as [$number, $customerIndex, $amount, $status, $dayOffset]) {
            DB::table('customer_invoices')->insert([
                'tenant_id' => $tenantId,
                'customer_id' => $customerIds[$customerIndex],
                'invoice_number' => $number,
                'invoice_date' => $today->copy()->addDays($dayOffset),
                'due_date' => $today->copy()->addDays($dayOffset + 45),
                'total_amount' => $amount,
                'tax_amount' => round($amount * 0.17, 2),
                'status' => $status,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $billSpecs = [
            ['BILL-2026-0001', 0, 620000, 'posted', -12],
            ['BILL-2026-0002', 1, 185000, 'posted', -9],
            ['BILL-2026-0003', 2, 98000, 'paid', -7],
            ['BILL-2026-0004', 4, 132000, 'posted', -3],
        ];

        foreach ($billSpecs as [$number, $supplierIndex, $amount, $status, $dayOffset]) {
            DB::table('supplier_bills')->insert([
                'tenant_id' => $tenantId,
                'supplier_id' => $supplierIds[$supplierIndex],
                'bill_number' => $number,
                'bill_date' => $today->copy()->addDays($dayOffset),
                'due_date' => $today->copy()->addDays($dayOffset + 30),
                'total_amount' => $amount,
                'tax_amount' => round($amount * 0.17, 2),
                'status' => $status,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Cost variances
        |--------------------------------------------------------------------------
        */
        $varianceSpecs = [
            [0, 205000, 212400],
            [1, 388000, 379500],
            [2, 645000, 668250],
            [3, 134000, 141800],
            [7, 223500, 219900],
            [12, 71500, 70200],
        ];

        foreach ($varianceSpecs as [$orderIndex, $standard, $actual]) {
            $variance = $actual - $standard;
            DB::table('production_cost_variances')->insert([
                'tenant_id' => $tenantId,
                'production_order_id' => $orderIds[$orderIndex],
                'standard_cost' => $standard,
                'actual_cost' => $actual,
                'variance_amount' => $variance,
                'variance_percentage' => round(($variance / $standard) * 100, 2),
                'material_variance' => round($variance * 0.55, 2),
                'labour_variance' => round($variance * 0.2, 2),
                'machine_variance' => round($variance * 0.15, 2),
                'overhead_variance' => round($variance * 0.1, 2),
                'variance_explanation' => $variance > 0 ? 'Material price increase and overtime absorbed.' : 'Improved yield and lower machine downtime.',
                'calculated_at' => $now->copy()->subDays(2),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Order-level cost roll-ups
        |--------------------------------------------------------------------------
        */
        foreach ($varianceSpecs as [$orderIndex, $standard, $actual]) {
            $qty = $orderQuantities[$orderIndex];

            DB::table('production_costs')->insert([
                'tenant_id' => $tenantId,
                'factory_id' => $factoryId,
                'production_order_id' => $orderIds[$orderIndex],
                'material_cost' => round($actual * 0.58, 2),
                'labour_cost' => round($actual * 0.22, 2),
                'machine_cost' => round($actual * 0.12, 2),
                'overhead_cost' => round($actual * 0.08, 2),
                'wastage_cost' => 0,
                'rework_cost' => 0,
                'total_cost' => $actual,
                'unit_cost' => $qty > 0 ? round($actual / $qty, 2) : 0,
                'quantity' => $qty,
                'costed_at' => $now->copy()->subDays(2),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Dispatches
        |--------------------------------------------------------------------------
        */
        $dispatchSpecs = [
            ['DSP-2026-0001', 0, 'Rashid Mehmood', -10, 'delivered'],
            ['DSP-2026-0002', 1, 'Naveed Akhtar', -8, 'delivered'],
            ['DSP-2026-0003', 2, 'Shahid Iqbal', -6, 'delivered'],
            ['DSP-2026-0004', 3, 'Rashid Mehmood', -4, 'in_transit'],
            ['DSP-2026-0005', 4, 'Waqas Ali', -2, 'in_transit'],
            ['DSP-2026-0006', 5, 'Naveed Akhtar', -1, 'pending'],
            ['DSP-2026-0007', 6, 'Shahid Iqbal', 0, 'pending'],
            ['DSP-2026-0008', null, 'Waqas Ali', 0, 'approved'],
        ];

        foreach ($dispatchSpecs as [$number, $soIndex, $driver, $dayOffset, $status]) {
            DB::table('dispatches')->insert([
                'tenant_id' => $tenantId,
                'factory_id' => $factoryId,
                'sales_order_id' => $soIndex === null ? null : $salesOrderIds[$soIndex],
                'dispatch_number' => $number,
                'dispatch_date' => $today->copy()->addDays($dayOffset),
                'vehicle_details' => json_encode(['type' => 'Truck', 'plate' => 'LEB-' . random_int(1000, 9999)]),
                'driver_name' => $driver,
                'status' => $status,
                'notes' => 'Dispatch against ' . ($soIndex === null ? 'stock transfer' : $salesOrderNumbers[$soIndex]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}

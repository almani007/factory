<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Demo dataset for Zoventra FactoryOS.
 *
 * Seeds one fully-populated garment tenant plus a handful of secondary tenants
 * for the platform console. The garment template configures the manufacturing
 * engine; no garment-specific columns are added to the shared item model.
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
        | Tenants, company and factories
        |--------------------------------------------------------------------------
        */
        $tenantId = DB::table('tenants')->insertGetId([
            'name' => 'Zoventra Apparel',
            'slug' => 'zoventra-apparel',
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
                'industry' => 'garment',
                'costing_method' => 'weighted_average',
                'default_warehouse' => 'Fabric Store',
                'fiscal_year_start' => '07-01',
                'quality_gate' => 'enabled',
                'fabric_tracking' => 'roll',
            ]),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ([
            ['Metro Textiles', 'metro-textiles', 'admin@metrotextiles.test', 'growth', 'active'],
            ['Sindh Knitwear', 'sindh-knitwear', 'it@sindhknitwear.test', 'growth', 'active'],
            ['Gulf Uniforms Co.', 'gulf-uniforms', 'ops@gulfuniforms.test', 'enterprise', 'active'],
            ['Northern Apparel', 'northern-apparel', 'hello@northernapparel.test', 'starter', 'suspended'],
        ] as [$name, $slug, $email, $plan, $status]) {
            DB::table('tenants')->insert([
                'name' => $name,
                'slug' => $slug,
                'subdomain' => $slug,
                'email' => $email,
                'phone' => '+92 21 111 222 333',
                'address' => 'Korangi Industrial Area, Karachi',
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
            'name' => 'Zoventra Apparel (Pvt) Ltd',
            'slug' => 'zoventra-apparel',
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
            'settings' => json_encode(['invoice_prefix' => 'INV', 'industry_template' => 'garment']),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $factoryId = DB::table('factories')->insertGetId([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'name' => 'Lahore Stitching Unit',
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

        $karachiFactoryId = DB::table('factories')->insertGetId([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'name' => 'Karachi Cutting & Finishing',
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
        foreach ([
            'CUT' => 'Cutting',
            'STL' => 'Stitching',
            'FIN' => 'Finishing',
            'QA' => 'Quality',
            'PAK' => 'Packing',
            'FIN2' => 'Finance',
        ] as $code => $name) {
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

        $workCentreIds = [];
        foreach ([
            ['CUT-01', 'Cutting Table 1', 'CUT', 1200],
            ['STL-A', 'Stitching Line A', 'STL', 420],
            ['STL-B', 'Stitching Line B', 'STL', 380],
            ['FIN-01', 'Finishing Station', 'FIN', 600],
            ['PAK-01', 'Packing Bench', 'PAK', 900],
        ] as [$code, $name, $dept, $capacity]) {
            $workCentreIds[$code] = DB::table('work_centres')->insertGetId([
                'tenant_id' => $tenantId,
                'factory_id' => $factoryId,
                'department_id' => $deptIds[$dept],
                'name' => $name,
                'code' => $code,
                'capacity_per_hour' => $capacity,
                'efficiency_percentage' => 92,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $shiftIds = [];
        foreach ([['A', '08:00:00', '16:00:00', 'Shift A — Cutting & Stitching'], ['B', '16:00:00', '00:00:00', 'Shift B — Stitching']] as [$code, $start, $end, $label]) {
            $shiftIds[$code] = DB::table('shifts')->insertGetId([
                'tenant_id' => $tenantId,
                'factory_id' => $factoryId,
                'name' => $label,
                'start_time' => $start,
                'end_time' => $end,
                'break_minutes' => 45,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('working_calendars')->insert([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'factory_id' => $factoryId,
            'name' => 'Lahore six-day week',
            'working_days' => json_encode(['mon', 'tue', 'wed', 'thu', 'fri', 'sat']),
            'start_time' => '08:00:00',
            'end_time' => '20:00:00',
            'holidays' => json_encode(['2026-08-14', '2026-12-25']),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ([
            ['default_cost_method', 'weighted_average'],
            ['fabric_tracking', 'roll'],
            ['quality_gate', 'enabled'],
            ['default_warehouse', 'Fabric Store'],
            ['allow_negative_stock', 'false'],
            ['timezone', 'Asia/Karachi'],
        ] as [$key, $value]) {
            DB::table('company_settings')->insert([
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'setting_key' => $key,
                'setting_value' => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Users, access and roles
        |--------------------------------------------------------------------------
        */
        $users = [
            ['Ayesha Khan', 'admin@zoventra.test', 'super_admin', 'FIN2', 'Owner / Platform Admin'],
            ['Bilal Ahmed', 'bilal.ahmed@zoventra.test', 'production_manager', 'STL', 'Production Manager'],
            ['Sana Iqbal', 'sana.iqbal@zoventra.test', 'quality_manager', 'QA', 'Quality Manager'],
            ['Usman Tariq', 'usman.tariq@zoventra.test', 'accountant', 'FIN2', 'Finance Manager'],
            ['Hira Malik', 'hira.malik@zoventra.test', 'store_keeper', 'CUT', 'Fabric Store Keeper'],
        ];

        $userIds = [];
        foreach ($users as [$name, $email, $role, $dept, $designation]) {
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
        $accountantId = $userIds[3];
        $storeKeeperId = $userIds[4];

        foreach ($userIds as $id) {
            DB::table('user_factory_access')->insert([
                'user_id' => $id,
                'factory_id' => $factoryId,
                'permissions' => json_encode(['*']),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('user_company_access')->insert([
                'tenant_id' => $tenantId,
                'user_id' => $id,
                'company_id' => $companyId,
                'permissions' => json_encode(['*']),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $employeeIds = [];
        foreach ([
            ['EMP-001', 'Rashid Mehmood', 'Cutting Supervisor', 'CUT'],
            ['EMP-002', 'Naveed Akhtar', 'Stitching Supervisor', 'STL'],
            ['EMP-003', 'Shahid Iqbal', 'Finishing Supervisor', 'FIN'],
            ['EMP-004', 'Waqas Ali', 'Packing Supervisor', 'PAK'],
            ['EMP-005', 'Farah Naz', 'In-line Quality Inspector', 'QA'],
            ['EMP-006', 'Imran Yousaf', 'Fabric Store Assistant', 'CUT'],
        ] as [$code, $name, $designation, $dept]) {
            $employeeIds[] = DB::table('employees')->insertGetId([
                'tenant_id' => $tenantId,
                'factory_id' => $factoryId,
                'employee_code' => $code,
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)) . '@zoventra.test',
                'phone' => '+92 300 7654321',
                'department_id' => $deptIds[$dept],
                'designation' => $designation,
                'hire_date' => $today->copy()->subYears(2),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach (['super_admin', 'production_manager', 'quality_manager', 'accountant', 'store_keeper'] as $roleName) {
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
        | Industry template
        |--------------------------------------------------------------------------
        */
        $templateId = DB::table('industry_templates')->insertGetId([
            'name' => 'Garment Manufacturing',
            'code' => 'GARMENT',
            'description' => 'Cut-make-trim template for woven and knitted apparel, uniforms and private label.',
            'default_modules' => json_encode(['Products', 'Inventory', 'Purchasing', 'Manufacturing', 'Production', 'Quality', 'Costing', 'Sales', 'Dispatch', 'Accounting', 'Reporting']),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ([
            ['default_uom', 'PCS'],
            ['fabric_tracking', 'roll'],
            ['production_model', 'MTS+MTO'],
            ['size_consumption', 'enabled'],
            ['quality_gate', 'enabled'],
            ['subcontracting', 'optional'],
        ] as [$key, $value]) {
            DB::table('industry_template_settings')->insert([
                'industry_template_id' => $templateId,
                'setting_key' => $key,
                'setting_value' => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach (['Products', 'Inventory', 'Purchasing', 'Manufacturing', 'Production', 'Quality', 'Costing', 'Sales', 'Dispatch', 'Accounting', 'Reporting'] as $module) {
            DB::table('industry_template_modules')->insert([
                'industry_template_id' => $templateId,
                'module_name' => $module,
                'is_enabled' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Units, categories, warehouses
        |--------------------------------------------------------------------------
        */
        $unitIds = [];
        foreach ([
            ['Pieces', 'PCS', 'pcs'],
            ['Metres', 'MTR', 'm'],
            ['Kilograms', 'KG', 'kg'],
            ['Rolls', 'ROLL', 'roll'],
            ['Cones', 'CONE', 'cone'],
            ['Cartons', 'CTN', 'ctn'],
        ] as [$name, $code, $symbol]) {
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
        foreach ([
            'FAB' => 'Fabrics',
            'TRM' => 'Trims & Accessories',
            'PKG' => 'Packaging',
            'FG' => 'Finished Goods',
        ] as $code => $name) {
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
        foreach ([
            'FAB-01' => ['Fabric Store', 'raw_materials'],
            'TRM-01' => ['Trims & Accessories Store', 'raw_materials'],
            'WIP-01' => ['Cutting WIP Store', 'wip'],
            'FG-01' => ['Finished Goods Store', 'finished_goods'],
        ] as $code => [$name, $type]) {
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

        /*
        |--------------------------------------------------------------------------
        | Item master
        |--------------------------------------------------------------------------
        */
        $itemSpecs = [
            // sku, name, category, unit, type, tracking, purchase price, standard cost, on hand, warehouse, reorder
            ['FAB-1001', 'Single Jersey Cotton 180 GSM', 'FAB', 'KG', 'raw_material', 'roll', 1850, 0, 1240, 'FAB-01', 300],
            ['FAB-1002', 'Brushed Fleece 320 GSM', 'FAB', 'KG', 'raw_material', 'roll', 2150, 0, 860, 'FAB-01', 250],
            ['FAB-1003', 'Indigo Denim 12 oz', 'FAB', 'MTR', 'raw_material', 'roll', 1250, 0, 2150, 'FAB-01', 500],
            ['FAB-1004', 'Pique Cotton 220 GSM', 'FAB', 'KG', 'raw_material', 'roll', 1950, 0, 940, 'FAB-01', 250],
            ['FAB-1005', 'Cotton Poplin 120 GSM', 'FAB', 'MTR', 'raw_material', 'roll', 980, 0, 1780, 'FAB-01', 400],
            ['TRM-2001', 'Polyester Sewing Thread 5000m', 'TRM', 'CONE', 'raw_material', 'batch', 320, 0, 480, 'TRM-01', 80],
            ['TRM-2002', 'Metal Zipper 20 cm', 'TRM', 'PCS', 'raw_material', 'none', 85, 0, 5400, 'TRM-01', 1000],
            ['TRM-2003', 'Shirt Button 18L', 'TRM', 'PCS', 'raw_material', 'none', 6, 0, 28000, 'TRM-01', 5000],
            ['TRM-2004', 'Woven Brand Label', 'TRM', 'PCS', 'raw_material', 'none', 12, 0, 12000, 'TRM-01', 2500],
            ['TRM-2005', 'Care & Size Label', 'TRM', 'PCS', 'raw_material', 'none', 4, 0, 15000, 'TRM-01', 3000],
            ['TRM-2006', 'Cotton Drawcord', 'TRM', 'MTR', 'raw_material', 'none', 45, 0, 3200, 'TRM-01', 600],
            ['PKG-3001', 'Polybag 12×16', 'PKG', 'PCS', 'packaging', 'none', 9, 0, 8600, 'TRM-01', 2000],
            ['PKG-3002', 'Printed Hang Tag', 'PKG', 'PCS', 'packaging', 'none', 15, 0, 9200, 'TRM-01', 2000],
            ['PKG-3003', 'Export Carton (50 pcs)', 'PKG', 'CTN', 'packaging', 'none', 220, 0, 640, 'TRM-01', 120],
            ['FG-1001', 'Classic Crew T-Shirt', 'FG', 'PCS', 'finished_good', 'none', 0, 620, 1840, 'FG-01', 300],
            ['FG-1002', 'Fleece Hoodie', 'FG', 'PCS', 'finished_good', 'none', 0, 1850, 620, 'FG-01', 150],
            ['FG-1003', 'Slim Fit Denim Jeans', 'FG', 'PCS', 'finished_good', 'none', 0, 2450, 415, 'FG-01', 120],
            ['FG-1004', 'Uniform Pique Polo', 'FG', 'PCS', 'finished_good', 'none', 0, 890, 980, 'FG-01', 200],
            ['FG-1005', 'Cotton Poplin Shirt', 'FG', 'PCS', 'finished_good', 'none', 0, 1120, 260, 'FG-01', 100],
        ];

        $itemIds = [];
        $itemNames = [];
        $itemUnits = [];
        $prices = [];
        $onHand = [];
        foreach ($itemSpecs as [$sku, $name, $cat, $unit, $type, $tracking, $purchase, $cost, $stock, $warehouse, $reorder]) {
            $itemIds[$sku] = DB::table('items')->insertGetId([
                'tenant_id' => $tenantId,
                'factory_id' => $factoryId,
                'category_id' => $categoryIds[$cat],
                'sku' => $sku,
                'name' => $name,
                'item_type' => $type,
                'description' => $name . ' used in Zoventra garment production.',
                'brand' => 'Zoventra Apparel',
                'unit_id' => $unitIds[$unit],
                'min_stock' => $reorder > 0 ? (int) round($reorder * 0.3) : 0,
                'reorder_level' => $reorder,
                'default_purchase_price' => $purchase,
                'standard_production_cost' => $cost,
                'is_active' => true,
                'is_purchasable' => $type !== 'finished_good',
                'is_manufacturable' => $type === 'finished_good',
                'is_sellable' => $type === 'finished_good',
                'track_batch' => in_array($tracking, ['batch', 'roll'], true),
                'track_serial' => false,
                'tracking_method' => $tracking,
                'specifications' => json_encode($cat === 'FAB' ? ['composition' => 'cotton', 'finish' => 'pre-shrunk'] : []),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $itemNames[$sku] = $name;
            $itemUnits[$sku] = $unit;
            $prices[$sku] = $purchase ?: $cost;
            $onHand[$sku] = $stock;

            DB::table('stock_balances')->insert([
                'tenant_id' => $tenantId,
                'factory_id' => $factoryId,
                'warehouse_id' => $warehouseIds[$warehouse],
                'item_id' => $itemIds[$sku],
                'on_hand' => $stock,
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
        | Garment attributes, sizes and variants
        |--------------------------------------------------------------------------
        */
        $colourAttr = DB::table('item_attributes')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'Colour',
            'code' => 'COLOUR',
            'input_type' => 'select',
            'applies_to' => 'variant',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $sizeAttr = DB::table('item_attributes')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'Size',
            'code' => 'SIZE',
            'input_type' => 'select',
            'applies_to' => 'variant',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $colourIds = [];
        foreach (['Navy' => 'NVY', 'White' => 'WHT', 'Black' => 'BLK', 'Charcoal' => 'CHR', 'Olive' => 'OLV', 'Indigo' => 'IND', 'Stone' => 'STN', 'Sky' => 'SKY', 'Blue' => 'BLU', 'Sand' => 'SND'] as $value => $code) {
            $colourIds[$value] = DB::table('item_attribute_values')->insertGetId([
                'tenant_id' => $tenantId,
                'item_attribute_id' => $colourAttr,
                'value' => $value,
                'code' => $code,
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $sizeIds = [];
        foreach (['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $index => $size) {
            $sizeIds[$size] = DB::table('item_attribute_values')->insertGetId([
                'tenant_id' => $tenantId,
                'item_attribute_id' => $sizeAttr,
                'value' => $size,
                'code' => $size,
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $sizeSetId = DB::table('size_sets')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'Adult Apparel S–XXL',
            'code' => 'ADULT-6',
            'description' => 'Standard adult size run for woven and knitted tops.',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $sizeSetValueIds = [];
        foreach (['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $index => $size) {
            $sizeSetValueIds[$size] = DB::table('size_set_values')->insertGetId([
                'tenant_id' => $tenantId,
                'size_set_id' => $sizeSetId,
                'value' => $size,
                'code' => $size,
                'sort_order' => $index,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $styleColours = [
            'FG-1001' => ['Navy', 'White', 'Black'],
            'FG-1002' => ['Charcoal', 'Olive', 'Navy'],
            'FG-1003' => ['Indigo', 'Black', 'Stone'],
            'FG-1004' => ['White', 'Navy', 'Sky'],
            'FG-1005' => ['White', 'Blue', 'Sand'],
        ];
        $sizes = ['S', 'M', 'L', 'XL'];

        $variantIds = [];
        foreach ($styleColours as $sku => $colours) {
            foreach ($colours as $colour) {
                foreach ($sizes as $size) {
                    $code = $colourIds[$colour] ? strtoupper(substr($colour, 0, 3)) : 'CLR';
                    $variantSku = $sku . '-' . $code . '-' . $size;
                    $variantId = DB::table('item_variants')->insertGetId([
                        'tenant_id' => $tenantId,
                        'item_id' => $itemIds[$sku],
                        'variant_sku' => $variantSku,
                        'name' => $itemNames[$sku] . ' — ' . $colour . ' / ' . $size,
                        'attributes' => json_encode(['colour' => $colour, 'size' => $size]),
                        'unit_id' => $unitIds['PCS'],
                        'min_stock' => 20,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $variantIds[$sku][$colour][$size] = $variantId;

                    DB::table('item_variant_attribute_values')->insert([
                        [
                            'tenant_id' => $tenantId,
                            'item_variant_id' => $variantId,
                            'item_attribute_id' => $colourAttr,
                            'item_attribute_value_id' => $colourIds[$colour],
                            'created_at' => $now,
                            'updated_at' => $now,
                        ],
                        [
                            'tenant_id' => $tenantId,
                            'item_variant_id' => $variantId,
                            'item_attribute_id' => $sizeAttr,
                            'item_attribute_value_id' => $sizeIds[$size],
                            'created_at' => $now,
                            'updated_at' => $now,
                        ],
                    ]);
                }
            }
        }

        foreach (['FG-1001', 'FG-1002', 'FG-1003', 'FG-1004', 'FG-1005'] as $sku) {
            DB::table('style_revisions')->insert([
                'tenant_id' => $tenantId,
                'item_id' => $itemIds[$sku],
                'revision' => '1.0',
                'status' => 'approved',
                'technical_pack' => 'Initial released tech pack for ' . $itemNames[$sku] . '.',
                'effective_from' => $today->copy()->subMonths(6),
                'approved_by' => $productionManagerId,
                'approved_at' => $now->copy()->subMonths(6),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('style_revisions')->insert([
            'tenant_id' => $tenantId,
            'item_id' => $itemIds['FG-1001'],
            'revision' => '1.1',
            'status' => 'draft',
            'technical_pack' => 'Awaiting buyer approval for revised neck rib and stitch density.',
            'effective_from' => $today->copy()->addMonth(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ([
            ['Technical pack — Classic Crew T-Shirt', 'FG-1001'],
            ['Technical pack — Fleece Hoodie', 'FG-1002'],
            ['Technical pack — Slim Fit Denim', 'FG-1003'],
            ['Technical pack — Uniform Polo', 'FG-1004'],
        ] as [$name, $sku]) {
            DB::table('product_documents')->insert([
                'tenant_id' => $tenantId,
                'item_id' => $itemIds[$sku],
                'name' => $name,
                'document_type' => 'technical_pack',
                'file_path' => 'product-documents/' . strtolower($sku) . '-tech-pack.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 240000,
                'uploaded_by' => $productionManagerId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | BOMs with size-wise consumption
        |--------------------------------------------------------------------------
        */
        $bomSpecs = [
            'FG-1001' => [
                ['FAB-1001', 0.24, 8, ['S' => 0.22, 'M' => 0.24, 'L' => 0.26, 'XL' => 0.28]],
                ['TRM-2001', 0.08, 2, null],
                ['TRM-2004', 1, 1, null],
                ['TRM-2005', 1, 1, null],
                ['PKG-3001', 1, 0, null],
                ['PKG-3002', 1, 0, null],
                ['PKG-3003', 0.02, 0, null],
            ],
            'FG-1002' => [
                ['FAB-1002', 0.78, 9, ['S' => 0.70, 'M' => 0.78, 'L' => 0.86, 'XL' => 0.94]],
                ['TRM-2001', 0.12, 2, null],
                ['TRM-2006', 0.7, 3, null],
                ['TRM-2004', 1, 1, null],
                ['TRM-2005', 1, 1, null],
                ['PKG-3001', 1, 0, null],
                ['PKG-3002', 1, 0, null],
                ['PKG-3003', 0.02, 0, null],
            ],
            'FG-1003' => [
                ['FAB-1003', 1.45, 10, ['S' => 1.35, 'M' => 1.45, 'L' => 1.55, 'XL' => 1.65]],
                ['TRM-2001', 0.18, 2, null],
                ['TRM-2002', 1, 1, null],
                ['TRM-2003', 1, 1, null],
                ['TRM-2004', 1, 1, null],
                ['TRM-2005', 1, 1, null],
                ['PKG-3001', 1, 0, null],
                ['PKG-3003', 0.02, 0, null],
            ],
            'FG-1004' => [
                ['FAB-1004', 0.34, 8, ['S' => 0.30, 'M' => 0.34, 'L' => 0.38, 'XL' => 0.42]],
                ['TRM-2001', 0.10, 2, null],
                ['TRM-2003', 3, 1, null],
                ['TRM-2004', 1, 1, null],
                ['TRM-2005', 1, 1, null],
                ['PKG-3001', 1, 0, null],
                ['PKG-3003', 0.02, 0, null],
            ],
            'FG-1005' => [
                ['FAB-1005', 1.80, 9, ['S' => 1.70, 'M' => 1.80, 'L' => 1.90, 'XL' => 2.00]],
                ['TRM-2001', 0.14, 2, null],
                ['TRM-2003', 8, 1, null],
                ['TRM-2004', 1, 1, null],
                ['TRM-2005', 1, 1, null],
                ['PKG-3001', 1, 0, null],
                ['PKG-3002', 1, 0, null],
                ['PKG-3003', 0.02, 0, null],
            ],
        ];

        $bom = [];
        foreach ($bomSpecs as $sku => $components) {
            $headerId = DB::table('bom_headers')->insertGetId([
                'tenant_id' => $tenantId,
                'factory_id' => $factoryId,
                'item_id' => $itemIds[$sku],
                'name' => 'BOM — ' . $itemNames[$sku],
                'description' => 'Standard garment bill of materials with size-wise consumption.',
                'is_active' => true,
                'is_default' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $versionId = DB::table('bom_versions')->insertGetId([
                'bom_header_id' => $headerId,
                'tenant_id' => $tenantId,
                'version' => '1.0',
                'base_quantity' => 1,
                'base_unit_id' => $unitIds['PCS'],
                'effective_from' => $today->copy()->subMonths(6),
                'is_active' => true,
                'approval_status' => 'approved',
                'standard_material_cost' => 0,
                'notes' => 'Initial released garment BOM.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($components as [$componentSku, $quantity, $wastage, $sizeRows]) {
                $componentId = DB::table('bom_components')->insertGetId([
                    'bom_version_id' => $versionId,
                    'tenant_id' => $tenantId,
                    'item_id' => $itemIds[$componentSku],
                    'quantity' => $quantity,
                    'consumption_type' => $sizeRows ? 'size_wise' : 'fixed',
                    'unit_id' => $unitIds[$itemUnits[$componentSku]],
                    'expected_wastage_percentage' => $wastage,
                    'is_alternative' => false,
                    'notes' => $sizeRows ? 'Size-wise consumption from marker plan.' : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                if ($sizeRows) {
                    foreach ($sizeRows as $size => $sizeQuantity) {
                        DB::table('bom_size_consumptions')->insert([
                            'tenant_id' => $tenantId,
                            'bom_component_id' => $componentId,
                            'size_set_value_id' => $sizeSetValueIds[$size] ?? null,
                            'size_label' => $size,
                            'quantity' => $sizeQuantity,
                            'wastage_percentage' => $wastage,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            }

            $bom[$sku] = [$headerId, $versionId];
        }

        /*
        |--------------------------------------------------------------------------
        | Routings and operations
        |--------------------------------------------------------------------------
        */
        $routingSpecs = [
            'FG-1001' => [
                ['Fabric inspection', 'CUT-01', 10, 0.4],
                ['Cutting', 'CUT-01', 20, 0.8],
                ['Stitching', 'STL-A', 30, 2.4],
                ['Finishing', 'FIN-01', 15, 0.9],
                ['Packing', 'PAK-01', 10, 0.6],
                ['Finished goods receipt', 'PAK-01', 5, 0.2],
            ],
            'FG-1002' => [
                ['Fabric inspection', 'CUT-01', 10, 0.5],
                ['Cutting', 'CUT-01', 20, 1.0],
                ['Embroidery (optional)', 'STL-B', 25, 1.5],
                ['Stitching', 'STL-A', 35, 3.2],
                ['Finishing', 'FIN-01', 15, 1.0],
                ['Packing', 'PAK-01', 10, 0.7],
                ['Finished goods receipt', 'PAK-01', 5, 0.2],
            ],
            'FG-1003' => [
                ['Fabric inspection', 'CUT-01', 10, 0.5],
                ['Cutting', 'CUT-01', 25, 1.2],
                ['Stitching', 'STL-A', 40, 4.2],
                ['Washing (optional)', 'FIN-01', 20, 2.0],
                ['Finishing', 'FIN-01', 15, 1.1],
                ['Packing', 'PAK-01', 10, 0.7],
                ['Finished goods receipt', 'PAK-01', 5, 0.2],
            ],
            'FG-1004' => [
                ['Fabric inspection', 'CUT-01', 10, 0.4],
                ['Cutting', 'CUT-01', 20, 0.8],
                ['Stitching', 'STL-A', 30, 2.6],
                ['Finishing', 'FIN-01', 15, 0.9],
                ['Packing', 'PAK-01', 10, 0.6],
                ['Finished goods receipt', 'PAK-01', 5, 0.2],
            ],
            'FG-1005' => [
                ['Fabric inspection', 'CUT-01', 10, 0.5],
                ['Cutting', 'CUT-01', 20, 0.9],
                ['Stitching', 'STL-B', 35, 3.4],
                ['Finishing', 'FIN-01', 15, 1.0],
                ['Packing', 'PAK-01', 10, 0.6],
                ['Finished goods receipt', 'PAK-01', 5, 0.2],
            ],
        ];

        $routing = [];
        foreach ($routingSpecs as $sku => $operations) {
            $routingId = DB::table('routings')->insertGetId([
                'tenant_id' => $tenantId,
                'factory_id' => $factoryId,
                'item_id' => $itemIds[$sku],
                'name' => 'Routing — ' . $itemNames[$sku],
                'description' => 'Cut, make, finish and pack sequence.',
                'is_active' => true,
                'is_default' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $versionId = DB::table('routing_versions')->insertGetId([
                'routing_id' => $routingId,
                'tenant_id' => $tenantId,
                'version' => '1.0',
                'effective_from' => $today->copy()->subMonths(6),
                'is_active' => true,
                'notes' => 'Initial released routing.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $ops = [];
            foreach ($operations as $index => [$operationName, $workCentre, $setup, $run]) {
                $opId = DB::table('routing_operations')->insertGetId([
                    'routing_version_id' => $versionId,
                    'tenant_id' => $tenantId,
                    'sequence' => $index + 1,
                    'operation_name' => $operationName,
                    'work_centre_id' => $workCentreIds[$workCentre],
                    'setup_time_minutes' => $setup,
                    'run_time_per_unit_minutes' => $run,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $ops[] = [
                    'id' => $opId,
                    'name' => $operationName,
                    'sequence' => $index + 1,
                    'work_centre_id' => $workCentreIds[$workCentre],
                ];
            }

            $routing[$sku] = ['version_id' => $versionId, 'ops' => $ops];
        }

        /*
        |--------------------------------------------------------------------------
        | Suppliers, customers and contacts
        |--------------------------------------------------------------------------
        */
        $supplierSpecs = [
            ['Karachi Cotton Mills', 'SUP-001', 'Imran Sheikh', 'sales@karachicotton.test', 'Fabrics'],
            ['Faisalabad Knit Fabrics', 'SUP-002', 'Nadia Farooq', 'orders@fsdknit.test', 'Knitted fabric'],
            ['Descon Trims & Threads', 'SUP-003', 'Farhan Ali', 'supply@descontrims.test', 'Trims'],
            ['Atlas Zipper Co.', 'SUP-004', 'Rabia Aslam', 'info@atlaszipper.test', 'Accessories'],
            ['Sunrise Packaging', 'SUP-005', 'Kamran Butt', 'hello@sunrisepack.test', 'Packaging'],
            ['Precision Garment Machinery', 'SUP-006', 'Adeel Raza', 'tools@precisiongm.test', 'Machinery'],
        ];

        $supplierIds = [];
        foreach ($supplierSpecs as [$name, $code, $contact, $email, $category]) {
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

            DB::table('supplier_contacts')->insert([
                'tenant_id' => $tenantId,
                'supplier_id' => end($supplierIds),
                'name' => $contact,
                'email' => $email,
                'phone' => '+92 21 3456 7890',
                'role' => 'Sales',
                'is_primary' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $customerSpecs = [
            ['Khaadi Retail', 'CUS-001', 'Zainab Qureshi', 'procurement@khaadi.test', 2500000],
            ['Sapphire Retail', 'CUS-002', 'Ahmed Raza', 'buying@sapphire.test', 1800000],
            ['Outfitters', 'CUS-003', 'Sadia Noor', 'orders@outfitters.test', 4200000],
            ['Gul Ahmed Ideas', 'CUS-004', 'Hassan Javed', 'supply@gulahmed.test', 1200000],
            ['Leisure Club', 'CUS-005', 'Mariam Shah', 'purchase@leisureclub.test', 3000000],
            ['Daraz Marketplace', 'CUS-006', 'Owais Khan', 'seller@daraz.test', 950000],
        ];

        $customerIds = [];
        foreach ($customerSpecs as [$name, $code, $contact, $email, $limit]) {
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

            DB::table('customer_contacts')->insert([
                'tenant_id' => $tenantId,
                'customer_id' => end($customerIds),
                'name' => $contact,
                'email' => $email,
                'phone' => '+92 42 3567 8901',
                'role' => 'Buying',
                'is_primary' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Sales orders
        |--------------------------------------------------------------------------
        */
        $salesOrderIds = [];
        $salesOrderNumbers = [];
        $salesOrderLines = [
            [0, 'FG-1001', 600, 1450],
            [0, 'FG-1004', 300, 1850],
            [1, 'FG-1002', 180, 4200],
            [1, 'FG-1005', 120, 2650],
            [2, 'FG-1001', 900, 1420],
            [2, 'FG-1003', 250, 4850],
            [3, 'FG-1004', 420, 1820],
            [3, 'FG-1002', 90, 4150],
            [4, 'FG-1005', 160, 2750],
            [4, 'FG-1003', 120, 4950],
            [5, 'FG-1001', 350, 1490],
            [5, 'FG-1004', 200, 1890],
            [6, 'FG-1002', 240, 4250],
            [6, 'FG-1005', 180, 2700],
        ];

        $salesOrderSpecs = [
            ['SO-2026-0101', 0, -12, 'confirmed'],
            ['SO-2026-0102', 1, -9, 'confirmed'],
            ['SO-2026-0103', 2, -7, 'in_production'],
            ['SO-2026-0104', 3, -5, 'confirmed'],
            ['SO-2026-0105', 4, -3, 'in_production'],
            ['SO-2026-0106', 5, -1, 'draft'],
            ['SO-2026-0107', 2, 0, 'confirmed'],
        ];

        foreach ($salesOrderSpecs as $index => [$number, $customerIndex, $dayOffset, $status]) {
            $soId = DB::table('sales_orders')->insertGetId([
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'customer_id' => $customerIds[$customerIndex],
                'so_number' => $number,
                'order_date' => $today->copy()->addDays($dayOffset),
                'delivery_date' => $today->copy()->addDays($dayOffset + 21),
                'status' => $status,
                'notes' => 'Garment order for ' . $customerSpecs[$customerIndex][0],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $salesOrderIds[] = $soId;
            $salesOrderNumbers[] = $number;

            foreach ($salesOrderLines as [$soIndex, $sku, $qty, $unitPrice]) {
                if ($soIndex !== $index) {
                    continue;
                }

                $colour = $styleColours[$sku][0];
                $variantId = $variantIds[$sku][$colour]['M'] ?? null;

                DB::table('sales_order_lines')->insert([
                    'sales_order_id' => $soId,
                    'tenant_id' => $tenantId,
                    'item_id' => $itemIds[$sku],
                    'item_variant_id' => $variantId,
                    'quantity' => $qty,
                    'unit_id' => $unitIds['PCS'],
                    'unit_price' => $unitPrice,
                    'total_price' => $qty * $unitPrice,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Production orders and variant breakdown
        |--------------------------------------------------------------------------
        */
        $orderSpecs = [
            ['WO-2026-0101', 'FG-1001', 1200, 'completed', -14, -12, 1180, 'normal', 0],
            ['WO-2026-0102', 'FG-1002', 120, 'completed', -11, -9, 118, 'high', 1],
            ['WO-2026-0103', 'FG-1003', 400, 'completed', -8, -6, 392, 'normal', 2],
            ['WO-2026-0104', 'FG-1001', 900, 'in_production', -4, 2, 0, 'high', 2],
            ['WO-2026-0105', 'FG-1004', 250, 'in_production', -3, 1, 0, 'normal', 3],
            ['WO-2026-0106', 'FG-1002', 80, 'in_production', -2, 3, 0, 'normal', 1],
            ['WO-2026-0107', 'FG-1003', 300, 'quality_inspection', -6, 0, 0, 'high', 4],
            ['WO-2026-0108', 'FG-1001', 1500, 'approved', 0, 5, 0, 'normal', 6],
            ['WO-2026-0109', 'FG-1004', 200, 'approved', 0, 4, 0, 'normal', 3],
            ['WO-2026-0110', 'FG-1002', 60, 'pending_approval', 1, 8, 0, 'low', 1],
            ['WO-2026-0111', 'FG-1005', 150, 'draft', 3, 10, 0, 'normal', null],
            ['WO-2026-0112', 'FG-1001', 500, 'cancelled', -5, -2, 0, 'low', 0],
            ['WO-2026-0113', 'FG-1001', 480, 'completed', 0, 0, 480, 'high', 6],
        ];
        $sizeSplit = ['S' => 0.30, 'M' => 0.30, 'L' => 0.25, 'XL' => 0.15];

        $orderIds = [];
        $orderQuantities = [];
        $orderRecords = [];
        foreach ($orderSpecs as $index => [$number, $sku, $qty, $status, $startOffset, $endOffset, $goodQty, $priority, $soIndex]) {
            [$headerId, $versionId] = $bom[$sku];
            $colour = $styleColours[$sku][0];

            $orderId = DB::table('production_orders')->insertGetId([
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'factory_id' => $factoryId,
                'sales_order_id' => $soIndex === null ? null : ($salesOrderIds[$soIndex] ?? null),
                'bom_header_id' => $headerId,
                'bom_version_id' => $versionId,
                'routing_version_id' => $routing[$sku]['version_id'],
                'order_number' => $number,
                'production_type' => $soIndex === null ? 'mts' : 'mto',
                'source_type' => $soIndex === null ? null : 'sales_order',
                'source_id' => $soIndex === null ? null : ($salesOrderIds[$soIndex] ?? null),
                'planned_quantity' => $qty,
                'actual_quantity' => $goodQty,
                'good_quantity' => $goodQty,
                'rejected_quantity' => $goodQty > 0 ? max(0, $qty - $goodQty) : 0,
                'rework_quantity' => $status === 'quality_inspection' ? 12 : 0,
                'scrap_quantity' => $goodQty > 0 ? max(0, $qty - $goodQty) : 0,
                'planned_start_date' => $today->copy()->addDays($startOffset)->setTime(8, 0),
                'planned_end_date' => $today->copy()->addDays($endOffset)->setTime(17, 0),
                'actual_start_date' => $startOffset <= 0 ? $today->copy()->addDays($startOffset)->setTime(8, 30) : null,
                'actual_end_date' => in_array($status, ['completed', 'closed'], true) ? $today->copy()->addDays($endOffset)->setTime(16, 0) : null,
                'status' => $status,
                'priority' => $priority,
                'notes' => 'Garment work order for ' . $itemNames[$sku],
                'approved_by' => $productionManagerId,
                'approved_at' => $startOffset < 0 ? $now->copy()->subDays(abs($startOffset)) : null,
                'released_at' => in_array($status, ['approved', 'material_reserved', 'in_production', 'quality_inspection', 'completed', 'closed'], true) ? $now->copy()->subDays(max(1, abs($startOffset))) : null,
                'completed_at' => in_array($status, ['completed', 'closed'], true) ? $today->copy()->addDays($endOffset)->setTime(16, 0) : null,
                'created_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $orderIds[] = $orderId;
            $orderQuantities[] = $qty;
            $orderRecords[] = [
                'id' => $orderId,
                'sku' => $sku,
                'qty' => $qty,
                'status' => $status,
                'colour' => $colour,
                'routing' => $routing[$sku],
                'start' => $startOffset,
                'end' => $endOffset,
            ];

            $remaining = $qty;
            $sizeLabels = array_keys($sizeSplit);
            foreach ($sizeSplit as $sizeIndex => $ratio) {
                $lineQty = $sizeIndex === array_key_last($sizeSplit) ? $remaining : (int) round($qty * $ratio);
                $remaining -= $lineQty;

                DB::table('production_order_lines')->insert([
                    'production_order_id' => $orderId,
                    'tenant_id' => $tenantId,
                    'item_id' => $itemIds[$sku],
                    'item_variant_id' => $variantIds[$sku][$colour][$sizeIndex],
                    'size_label' => $sizeIndex,
                    'colour' => $colour,
                    'quantity' => $lineQty,
                    'unit_id' => $unitIds['PCS'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Work orders, operation progress and assignments
        |--------------------------------------------------------------------------
        */
        $workOrderIds = [];
        foreach ($orderRecords as $orderIndex => $record) {
            $status = $record['status'];
            $completedOps = match ($status) {
                'completed', 'closed' => count($record['routing']['ops']),
                'quality_inspection' => count($record['routing']['ops']) - 2,
                'in_production' => 2,
                'material_reserved' => 1,
                default => 0,
            };

            foreach ($record['routing']['ops'] as $opIndex => $op) {
                if ($status === 'cancelled') {
                    $woStatus = 'cancelled';
                } elseif ($opIndex < $completedOps) {
                    $woStatus = 'completed';
                } elseif ($opIndex === $completedOps && in_array($status, ['in_production', 'quality_inspection'], true)) {
                    $woStatus = 'in_progress';
                } elseif ($status === 'pending_approval') {
                    $woStatus = 'pending';
                } else {
                    $woStatus = 'pending';
                }

                $good = $woStatus === 'completed' ? $record['qty'] : 0;

                $workOrderId = DB::table('work_orders')->insertGetId([
                    'tenant_id' => $tenantId,
                    'company_id' => $companyId,
                    'factory_id' => $factoryId,
                    'production_order_id' => $record['id'],
                    'work_centre_id' => $op['work_centre_id'],
                    'operator_id' => $productionManagerId,
                    'operation_name' => $op['name'],
                    'planned_quantity' => $record['qty'],
                    'actual_quantity' => $good,
                    'good_quantity' => $good,
                    'rejected_quantity' => $status === 'quality_inspection' && $opIndex === max(0, $completedOps - 1) ? 12 : 0,
                    'started_at' => $opIndex < $completedOps ? $today->copy()->addDays($record['start'])->setTime(9, 0) : null,
                    'completed_at' => $woStatus === 'completed' ? $today->copy()->addDays($record['start'] + $opIndex)->setTime(16, 0) : null,
                    'downtime_minutes' => $opIndex === 2 ? 25 : 0,
                    'status' => $woStatus,
                    'notes' => 'Operation ' . $op['sequence'] . ' of ' . count($record['routing']['ops']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $workOrderIds[$orderIndex][] = $workOrderId;

                DB::table('work_order_operations')->insert([
                    'work_order_id' => $workOrderId,
                    'tenant_id' => $tenantId,
                    'routing_operation_id' => $op['id'],
                    'operation_name' => $op['name'],
                    'sequence' => $op['sequence'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                if ($opIndex < 2 && in_array($status, ['in_production', 'quality_inspection', 'completed'], true)) {
                    DB::table('work_order_assignments')->insert([
                        'tenant_id' => $tenantId,
                        'work_order_id' => $workOrderId,
                        'work_centre_id' => $op['work_centre_id'],
                        'employee_id' => $employeeIds[$opIndex % count($employeeIds)],
                        'team_name' => $op['name'] . ' team',
                        'planned_hours' => round(($record['qty'] * 0.02) / 60, 2),
                        'actual_hours' => round(($record['qty'] * 0.021) / 60, 2),
                        'assigned_at' => $now->copy()->subDays(2),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Material issues, production reporting and completed output
        |--------------------------------------------------------------------------
        */
        $movementCounter = 0;
        foreach ($orderRecords as $orderIndex => $record) {
            if (! in_array($record['status'], ['completed', 'closed', 'in_production', 'quality_inspection'], true)) {
                continue;
            }

            $fabricSku = $bomSpecs[$record['sku']][0][0];
            $fabricQty = $record['qty'] * $bomSpecs[$record['sku']][0][1] * 1.08;
            $threadQty = $record['qty'] * ($record['sku'] === 'FG-1002' ? 0.12 : 0.08);

            $issueId = DB::table('material_issues')->insertGetId([
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'factory_id' => $factoryId,
                'production_order_id' => $record['id'],
                'work_order_id' => $workOrderIds[$orderIndex][0] ?? null,
                'reference' => 'MI-2026-' . str_pad((string) ++$movementCounter, 4, '0', STR_PAD_LEFT),
                'issue_date' => $today->copy()->addDays($record['start'])->setTime(9, 0),
                'issued_by' => $storeKeeperId,
                'notes' => 'Issued to ' . $record['sku'] . ' cutting.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ([[$fabricSku, $fabricQty], ['TRM-2001', $threadQty]] as [$issueSku, $issueQty]) {
                DB::table('material_issue_lines')->insert([
                    'material_issue_id' => $issueId,
                    'tenant_id' => $tenantId,
                    'item_id' => $itemIds[$issueSku],
                    'quantity' => round($issueQty, 3),
                    'unit_id' => $unitIds[$itemUnits[$issueSku]],
                    'warehouse_id' => $warehouseIds[$itemUnits[$issueSku] === 'KG' || $itemUnits[$issueSku] === 'MTR' ? 'FAB-01' : 'TRM-01'],
                    'unit_cost' => $prices[$issueSku],
                    'total_cost' => round($issueQty * $prices[$issueSku], 2),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $entryCount = $record['status'] === 'completed' ? 3 : 1;
            for ($entryIndex = 0; $entryIndex < $entryCount; $entryIndex++) {
                $entryQty = $record['status'] === 'completed' ? $record['qty'] : (int) round($record['qty'] * 0.45);
                $entryId = DB::table('production_entries')->insertGetId([
                    'tenant_id' => $tenantId,
                    'company_id' => $companyId,
                    'factory_id' => $factoryId,
                    'production_order_id' => $record['id'],
                    'work_order_id' => $workOrderIds[$orderIndex][$entryIndex] ?? null,
                    'entry_date' => $today->copy()->addDays($record['start'] + $entryIndex)->setTime(15, 0),
                    'good_quantity' => $entryQty,
                    'rejected_quantity' => $entryIndex === 0 ? 4 : 2,
                    'rework_quantity' => $entryIndex === 0 ? 2 : 0,
                    'scrap_quantity' => $entryIndex === 0 ? 1 : 0,
                    'operator_id' => $productionManagerId,
                    'shift_id' => $shiftIds['A'],
                    'notes' => 'Operation output reported by line supervisor.',
                    'approved_by' => $productionManagerId,
                    'approved_at' => $now->copy()->subDays(1),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $remainingOutput = $entryQty;
                foreach ($sizeSplit as $size => $ratio) {
                    $outputQty = $size === array_key_last($sizeSplit) ? $remainingOutput : (int) round($entryQty * $ratio);
                    $remainingOutput -= $outputQty;

                    DB::table('production_entry_outputs')->insert([
                        'production_entry_id' => $entryId,
                        'tenant_id' => $tenantId,
                        'item_id' => $itemIds[$record['sku']],
                        'quantity' => $outputQty,
                        'unit_id' => $unitIds['PCS'],
                        'warehouse_id' => $warehouseIds['FG-01'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Fabric batches, rolls and stock movements
        |--------------------------------------------------------------------------
        */
        $fabricSkus = ['FAB-1001', 'FAB-1002', 'FAB-1003', 'FAB-1004', 'FAB-1005'];
        $batchIds = [];
        $rollIds = [];
        foreach ($fabricSkus as $fabricIndex => $sku) {
            for ($batchIndex = 1; $batchIndex <= 2; $batchIndex++) {
                $batchId = DB::table('stock_batches')->insertGetId([
                    'tenant_id' => $tenantId,
                    'item_id' => $itemIds[$sku],
                    'batch_number' => 'B-2026-' . str_pad((string) ($fabricIndex * 2 + $batchIndex), 4, '0', STR_PAD_LEFT),
                    'manufacturing_date' => $today->copy()->subDays(20 + $batchIndex),
                    'received_quantity' => 620,
                    'remaining_quantity' => 500 - ($batchIndex * 60),
                    'unit_id' => $unitIds[$itemUnits[$sku]],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $batchIds[$sku][] = $batchId;

                for ($rollIndex = 1; $rollIndex <= 3; $rollIndex++) {
                    $length = $itemUnits[$sku] === 'MTR' ? 110 + ($rollIndex * 10) : 85 + ($rollIndex * 5);
                    $rollId = DB::table('stock_rolls')->insertGetId([
                        'tenant_id' => $tenantId,
                        'factory_id' => $factoryId,
                        'warehouse_id' => $warehouseIds['FAB-01'],
                        'item_id' => $itemIds[$sku],
                        'batch_id' => $batchId,
                        'roll_number' => 'ROLL-' . str_pad((string) ($fabricIndex * 10 + $batchIndex * 3 + $rollIndex), 5, '0', STR_PAD_LEFT),
                        'length' => $length,
                        'remaining_length' => $length - 6,
                        'weight' => $itemUnits[$sku] === 'MTR' ? 28 + $rollIndex : 22 + $rollIndex,
                        'width' => $sku === 'FAB-1003' ? 150 : 180,
                        'gsm' => match ($sku) {
                            'FAB-1001' => 180,
                            'FAB-1002' => 320,
                            'FAB-1003' => 410,
                            'FAB-1004' => 220,
                            'FAB-1005' => 120,
                        },
                        'shade_code' => 'SH-' . $sku . '-' . $batchIndex,
                        'status' => 'available',
                        'received_at' => $today->copy()->subDays(20 + $batchIndex),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $rollIds[$sku][] = $rollId;
                }
            }
        }

        // Receipts for the fabric rolls.
        foreach ($fabricSkus as $index => $sku) {
            $qty = $itemUnits[$sku] === 'MTR' ? 360 : 300;
            $unitCost = $prices[$sku];
            $movementId = DB::table('stock_movements')->insertGetId([
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'factory_id' => $factoryId,
                'warehouse_id' => $warehouseIds['FAB-01'],
                'movement_type' => 'receipt',
                'reference' => 'GRN-2026-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'source_document_type' => 'goods_receipt',
                'source_document_id' => null,
                'transaction_date' => $today->copy()->subDays(22 - $index)->setTime(10, 0),
                'posting_date' => $today->copy()->subDays(22 - $index)->setTime(10, 0),
                'unit_cost' => $unitCost,
                'total_cost' => $qty * $unitCost,
                'user_id' => $storeKeeperId,
                'posted_at' => $now->copy()->subHours(8),
                'posted_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('stock_movement_lines')->insert([
                'movement_id' => $movementId,
                'tenant_id' => $tenantId,
                'item_id' => $itemIds[$sku],
                'batch_id' => $batchIds[$sku][0],
                'roll_id' => $rollIds[$sku][0],
                'warehouse_id' => $warehouseIds['FAB-01'],
                'quantity' => $qty,
                'unit_id' => $unitIds[$itemUnits[$sku]],
                'unit_cost' => $unitCost,
                'total_cost' => $qty * $unitCost,
                'source_type' => 'goods_receipt',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Fabric issued to cutting for active orders.
        foreach (array_slice($orderRecords, 3, 3) as $index => $record) {
            $sku = $bomSpecs[$record['sku']][0][0];
            $qty = round($record['qty'] * $bomSpecs[$record['sku']][0][1], 2);
            $unitCost = $prices[$sku];
            $movementId = DB::table('stock_movements')->insertGetId([
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'factory_id' => $factoryId,
                'warehouse_id' => $warehouseIds['FAB-01'],
                'movement_type' => 'issue',
                'reference' => 'ISS-2026-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'source_document_type' => 'material_issue',
                'transaction_date' => $today->copy()->subDays(3 - $index)->setTime(11, 0),
                'posting_date' => $today->copy()->subDays(3 - $index)->setTime(11, 0),
                'unit_cost' => $unitCost,
                'total_cost' => $qty * $unitCost,
                'user_id' => $storeKeeperId,
                'posted_at' => $now->copy()->subHours(5),
                'posted_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('stock_movement_lines')->insert([
                'movement_id' => $movementId,
                'tenant_id' => $tenantId,
                'item_id' => $itemIds[$sku],
                'batch_id' => $batchIds[$sku][0],
                'warehouse_id' => $warehouseIds['FAB-01'],
                'quantity' => $qty,
                'unit_id' => $unitIds[$itemUnits[$sku]],
                'unit_cost' => $unitCost,
                'total_cost' => $qty * $unitCost,
                'source_type' => 'material_issue',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Finished goods receipts.
        foreach (array_slice($orderRecords, 0, 3) as $index => $record) {
            $sku = $record['sku'];
            $qty = $record['qty'];
            $unitCost = $prices[$sku];
            $colour = $record['colour'];
            $variantId = $variantIds[$sku][$colour]['M'] ?? null;
            $movementId = DB::table('stock_movements')->insertGetId([
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'factory_id' => $factoryId,
                'warehouse_id' => $warehouseIds['FG-01'],
                'movement_type' => 'production_receipt',
                'reference' => 'PRD-2026-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'source_document_type' => 'production_order',
                'source_document_id' => $record['id'],
                'transaction_date' => $today->copy()->subDays(6 - $index)->setTime(16, 0),
                'posting_date' => $today->copy()->subDays(6 - $index)->setTime(16, 0),
                'unit_cost' => $unitCost,
                'total_cost' => $qty * $unitCost,
                'user_id' => $productionManagerId,
                'posted_at' => $now->copy()->subHours(4),
                'posted_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('stock_movement_lines')->insert([
                'movement_id' => $movementId,
                'tenant_id' => $tenantId,
                'item_id' => $itemIds[$sku],
                'item_variant_id' => $variantId,
                'warehouse_id' => $warehouseIds['FG-01'],
                'quantity' => $qty,
                'unit_id' => $unitIds['PCS'],
                'unit_cost' => $unitCost,
                'total_cost' => $qty * $unitCost,
                'source_type' => 'production_order',
                'source_id' => $record['id'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Quality: templates, inspections, defects and rework
        |--------------------------------------------------------------------------
        */
        $templates = [];
        foreach ([
            ['Incoming Fabric Inspection', 'IT-FAB', 'incoming'],
            ['Cutting Inspection', 'IT-CUT', 'cutting'],
            ['In-Process Stitching Inspection', 'IT-STL', 'in_process'],
            ['Final Garment Inspection', 'IT-FINAL', 'final'],
            ['Packing Inspection', 'IT-PACK', 'packing'],
        ] as [$name, $code, $type]) {
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
            [3, 'cutting', 'passed', -9],
            [4, 'final', 'failed', -8],
            [5, 'incoming', 'passed', -6],
            [6, 'final', 'pending', -5],
            [7, 'in_process', 'passed', -4],
            [8, 'final', 'rework_required', -3],
            [9, 'incoming', 'passed', -2],
            [10, 'cutting', 'pending', -1],
            [11, 'packing', 'passed', 0],
        ];
        $inspectionIds = [];
        foreach ($inspectionSpecs as [$orderIndex, $type, $status, $dayOffset]) {
            $inspectionIds[] = DB::table('quality_inspections')->insertGetId([
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
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
                'notes' => ucfirst(str_replace('_', ' ', $status)) . ' during ' . str_replace('_', ' ', $type) . ' inspection.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $inspectionAql = [
            ['stitching', 'DEF-STL-01', 'Open seam at side panel', 6],
            ['measurement', 'DEF-MEA-01', 'Body length 1.5 cm below tolerance', 3],
            ['fabric', 'DEF-FAB-01', 'Shade variation between rolls', 8],
        ];
        foreach ($inspectionAql as $index => [$category, $code, $description, $qty]) {
            DB::table('quality_defects')->insert([
                'tenant_id' => $tenantId,
                'factory_id' => $factoryId,
                'quality_inspection_id' => $inspectionIds[4 + $index] ?? $inspectionIds[4],
                'defect_category' => $category,
                'defect_code' => $code,
                'description' => $description,
                'quantity' => $qty,
                'unit_id' => $unitIds['PCS'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $ncId = DB::table('quality_nonconformances')->insertGetId([
            'tenant_id' => $tenantId,
            'factory_id' => $factoryId,
            'nc_number' => 'NC-2026-0001',
            'quality_inspection_id' => $inspectionIds[4],
            'status' => 'open',
            'description' => 'Final inspection failed on side seam strength and measurement tolerance.',
            'root_cause' => 'Needle size mismatch on Line A and unchecked marker shrinkage.',
            'corrective_action' => 'Re-cut 12 pieces, re-stitch the affected bundle and recalibrate the line.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('quality_rework_orders')->insert([
            'tenant_id' => $tenantId,
            'factory_id' => $factoryId,
            'quality_nonconformance_id' => $ncId,
            'rework_number' => 'RW-2026-0001',
            'status' => 'pending',
            'instructions' => 'Re-stitch side seams, re-measure body length and re-submit for final inspection.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Accounting foundation
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

        $financialYearId = DB::table('financial_years')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'FY 2026-27',
            'start_date' => $today->copy()->startOfYear()->setDate(2026, 7, 1),
            'end_date' => $today->copy()->setDate(2027, 6, 30),
            'is_closed' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $periodId = DB::table('accounting_periods')->insertGetId([
            'tenant_id' => $tenantId,
            'financial_year_id' => $financialYearId,
            'name' => 'Nov 2026',
            'start_date' => $today->copy()->startOfMonth(),
            'end_date' => $today->copy()->endOfMonth(),
            'status' => 'open',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $accounts = [];
        foreach ([
            ['ACC-1000', 'Cash & Bank', 'asset'],
            ['ACC-1100', 'Accounts Receivable', 'asset'],
            ['ACC-1200', 'Raw Materials Inventory', 'asset'],
            ['ACC-1300', 'Work in Progress', 'asset'],
            ['ACC-1400', 'Finished Goods Inventory', 'asset'],
            ['ACC-2000', 'Accounts Payable', 'liability'],
            ['ACC-2100', 'Goods Received Not Invoiced', 'liability'],
            ['ACC-2200', 'Tax Payable', 'liability'],
            ['ACC-4000', 'Sales Revenue', 'revenue'],
            ['ACC-5000', 'Cost of Goods Sold', 'expense'],
            ['ACC-6000', 'Production Overhead', 'expense'],
            ['ACC-6100', 'Scrap & Rework', 'expense'],
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

        $dimensionId = DB::table('accounting_dimensions')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'Cost Centre',
            'code' => 'CC',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        foreach (['CUT' => 'Cutting', 'STL' => 'Stitching', 'FIN' => 'Finishing', 'PAK' => 'Packing'] as $code => $name) {
            DB::table('dimension_values')->insert([
                'tenant_id' => $tenantId,
                'accounting_dimension_id' => $dimensionId,
                'name' => $name,
                'code' => $code,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $gst17 = DB::table('tax_codes')->insertGetId([
            'tenant_id' => $tenantId,
            'code' => 'GST-17',
            'name' => 'Pakistan GST 17%',
            'jurisdiction' => 'PK',
            'rate' => 17,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('tax_codes')->insert([
            'tenant_id' => $tenantId,
            'code' => 'GST-0',
            'name' => 'Zero-rated export',
            'jurisdiction' => 'PK',
            'rate' => 0,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Purchasing documents
        |--------------------------------------------------------------------------
        */
        foreach ($fabricSkus as $index => $sku) {
            DB::table('supplier_items')->insert([
                'tenant_id' => $tenantId,
                'supplier_id' => $supplierIds[$index % 2],
                'item_id' => $itemIds[$sku],
                'supplier_item_code' => 'SUP-' . strtoupper(substr($sku, -4)),
                'last_price' => $prices[$sku],
                'lead_time_days' => 12 + $index,
                'min_order_quantity' => 100,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $poIds = [];
        $poLineIds = [];
        foreach ([[0, 0, 'sent', -10], [1, 2, 'received', -18], [2, 0, 'received', -24], [3, 4, 'draft', -2]] as $index => [$supplierIndex, $skuIndex, $status, $dayOffset]) {
            $sku = $fabricSkus[$skuIndex % count($fabricSkus)];
            $qty = 300;
            $poId = DB::table('purchase_orders')->insertGetId([
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'factory_id' => $factoryId,
                'supplier_id' => $supplierIds[$supplierIndex],
                'po_number' => 'PO-2026-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'order_date' => $today->copy()->addDays($dayOffset),
                'expected_delivery_date' => $today->copy()->addDays($dayOffset + 14),
                'status' => $status,
                'notes' => 'Fabric purchase for garment production.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $poIds[] = $poId;

            $poLineIds[$poId] = DB::table('purchase_order_lines')->insertGetId([
                'purchase_order_id' => $poId,
                'tenant_id' => $tenantId,
                'item_id' => $itemIds[$sku],
                'quantity' => $qty,
                'unit_id' => $unitIds[$itemUnits[$sku]],
                'unit_price' => $prices[$sku],
                'total_price' => $qty * $prices[$sku],
                'received_quantity' => $status === 'received' ? $qty : 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if ($status === 'received') {
                DB::table('goods_receipts')->insert([
                    'tenant_id' => $tenantId,
                    'company_id' => $companyId,
                    'factory_id' => $factoryId,
                    'warehouse_id' => $warehouseIds['FAB-01'],
                    'purchase_order_id' => $poId,
                    'grn_number' => 'GRN-2026-' . str_pad((string) ($index + 10), 4, '0', STR_PAD_LEFT),
                    'receipt_date' => $today->copy()->addDays($dayOffset + 7),
                    'status' => 'posted',
                    'received_by' => $storeKeeperId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $requisitionId = DB::table('purchase_requisitions')->insertGetId([
            'tenant_id' => $tenantId,
            'factory_id' => $factoryId,
            'requisition_number' => 'PR-2026-0001',
            'requested_by' => $productionManagerId,
            'required_date' => $today->copy()->addDays(7),
            'status' => 'approved',
            'notes' => 'Replenishment for fleece hoodie programme.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('purchase_requisition_lines')->insert([
            'tenant_id' => $tenantId,
            'purchase_requisition_id' => $requisitionId,
            'item_id' => $itemIds['FAB-1002'],
            'quantity' => 500,
            'unit_id' => $unitIds['KG'],
            'required_date' => $today->copy()->addDays(7),
            'status' => 'approved',
            'notes' => 'Fleece for winter hoodie orders.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $quotationId = DB::table('supplier_quotations')->insertGetId([
            'tenant_id' => $tenantId,
            'supplier_id' => $supplierIds[1],
            'quotation_number' => 'SQ-2026-0001',
            'quotation_date' => $today->copy()->subDays(4),
            'valid_until' => $today->copy()->addDays(26),
            'status' => 'received',
            'total_amount' => 500 * $prices['FAB-1002'],
            'notes' => 'Fleece quote for Q1 replenishment.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('supplier_quotation_lines')->insert([
            'tenant_id' => $tenantId,
            'supplier_quotation_id' => $quotationId,
            'item_id' => $itemIds['FAB-1002'],
            'quantity' => 500,
            'unit_id' => $unitIds['KG'],
            'unit_price' => $prices['FAB-1002'],
            'total_price' => 500 * $prices['FAB-1002'],
            'lead_time_days' => 14,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $supplierInvoiceId = DB::table('supplier_invoices')->insertGetId([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'supplier_id' => $supplierIds[1],
            'invoice_number' => 'SI-2026-0001',
            'invoice_date' => $today->copy()->subDays(10),
            'due_date' => $today->copy()->addDays(20),
            'total_amount' => 500 * $prices['FAB-1002'],
            'tax_amount' => round(500 * $prices['FAB-1002'] * 0.17, 2),
            'status' => 'posted',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('supplier_invoice_lines')->insert([
            'tenant_id' => $tenantId,
            'supplier_invoice_id' => $supplierInvoiceId,
            'item_id' => $itemIds['FAB-1002'],
            'description' => 'Brushed Fleece 320 GSM — lot B-2026-0004',
            'quantity' => 500,
            'unit_id' => $unitIds['KG'],
            'unit_price' => $prices['FAB-1002'],
            'tax_code_id' => $gst17,
            'tax_amount' => round(500 * $prices['FAB-1002'] * 0.17, 2),
            'total_amount' => 500 * $prices['FAB-1002'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Sales quotations, invoices and delivery
        |--------------------------------------------------------------------------
        */
        $quotationId = DB::table('sales_quotations')->insertGetId([
            'tenant_id' => $tenantId,
            'customer_id' => $customerIds[0],
            'quotation_number' => 'QT-2026-0001',
            'quotation_date' => $today->copy()->subDays(5),
            'valid_until' => $today->copy()->addDays(25),
            'status' => 'sent',
            'total_amount' => 1450 * 600,
            'notes' => 'Summer crew tee programme quotation.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('sales_quotation_lines')->insert([
            'tenant_id' => $tenantId,
            'sales_quotation_id' => $quotationId,
            'item_id' => $itemIds['FG-1001'],
            'item_variant_id' => $variantIds['FG-1001']['Navy']['M'],
            'quantity' => 600,
            'unit_id' => $unitIds['PCS'],
            'unit_price' => 1450,
            'total_price' => 1450 * 600,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $invoiceSpecs = [
            ['INV-2026-0001', 0, 'FG-1001', 428, 1450, 'posted', -8],
            ['INV-2026-0002', 1, 'FG-1002', 92, 4200, 'posted', -6],
            ['INV-2026-0003', 2, 'FG-1004', 240, 1820, 'posted', -4],
            ['INV-2026-0004', 3, 'FG-1005', 60, 2650, 'paid', -10],
            ['INV-2026-0005', 4, 'FG-1003', 74, 4850, 'posted', -2],
        ];

        foreach ($invoiceSpecs as [$number, $customerIndex, $sku, $qty, $unitPrice, $status, $dayOffset]) {
            $amount = $qty * $unitPrice;
            $invoiceId = DB::table('customer_invoices')->insertGetId([
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
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

            DB::table('customer_invoice_lines')->insert([
                'customer_invoice_id' => $invoiceId,
                'tenant_id' => $tenantId,
                'item_id' => $itemIds[$sku],
                'item_variant_id' => $variantIds[$sku][$styleColours[$sku][0]]['M'] ?? null,
                'description' => $itemNames[$sku] . ' — ' . $styleColours[$sku][0] . ' / M',
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'total_amount' => $amount,
                'tax_code_id' => $gst17,
                'tax_amount' => round($amount * 0.17, 2),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $deliveryOrderId = DB::table('delivery_orders')->insertGetId([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'factory_id' => $factoryId,
            'sales_order_id' => $salesOrderIds[0],
            'delivery_number' => 'DO-2026-0001',
            'delivery_date' => $today->copy()->addDays(2),
            'status' => 'scheduled',
            'notes' => 'Partial delivery of crew tees.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('delivery_order_lines')->insert([
            'delivery_order_id' => $deliveryOrderId,
            'tenant_id' => $tenantId,
            'item_id' => $itemIds['FG-1001'],
            'item_variant_id' => $variantIds['FG-1001']['Navy']['M'],
            'quantity' => 300,
            'unit_id' => $unitIds['PCS'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Supplier bills, journals and costing
        |--------------------------------------------------------------------------
        */
        $billSpecs = [
            ['BILL-2026-0001', 0, 'FAB-1001', 620, 'posted', -12],
            ['BILL-2026-0002', 1, 'FAB-1002', 500, 'posted', -9],
            ['BILL-2026-0003', 2, 'TRM-2001', 200, 'paid', -7],
            ['BILL-2026-0004', 4, 'PKG-3001', 8000, 'posted', -3],
        ];

        foreach ($billSpecs as [$number, $supplierIndex, $sku, $qty, $status, $dayOffset]) {
            $amount = $qty * $prices[$sku];
            $billId = DB::table('supplier_bills')->insertGetId([
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
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

            DB::table('supplier_bill_lines')->insert([
                'supplier_bill_id' => $billId,
                'tenant_id' => $tenantId,
                'item_id' => $itemIds[$sku],
                'description' => $itemNames[$sku],
                'quantity' => $qty,
                'unit_price' => $prices[$sku],
                'total_amount' => $amount,
                'tax_code_id' => $gst17,
                'tax_amount' => round($amount * 0.17, 2),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $journalSpecs = [
            ['JE-2026-0001', 'Fabric receipt GRN-2026-0001 — single jersey', 'posted', -12, 420000, 'ACC-1200', 'ACC-2100', 'goods_receipt'],
            ['JE-2026-0002', 'Fabric issued to cutting WO-2026-0101', 'posted', -11, 310000, 'ACC-1300', 'ACC-1200', 'material_issue'],
            ['JE-2026-0003', 'Stitching labour absorption WO-2026-0101', 'posted', -10, 185000, 'ACC-1300', 'ACC-2000', 'production_order'],
            ['JE-2026-0004', 'Sales invoice INV-2026-0001 — Khaadi Retail', 'posted', -8, 560000, 'ACC-1100', 'ACC-4000', 'customer_invoice'],
            ['JE-2026-0005', 'Supplier bill BILL-2026-0001 — Karachi Cotton Mills', 'posted', -7, 290000, 'ACC-1200', 'ACC-2000', 'supplier_bill'],
            ['JE-2026-0006', 'Finishing overhead absorption WO-2026-0102', 'submitted', -5, 120000, 'ACC-6000', 'ACC-2000', 'production_order'],
            ['JE-2026-0007', 'Stock adjustment ADJ-2026-0001 — fabric shrinkage', 'posted', -4, 65000, 'ACC-6100', 'ACC-1200', 'stock_adjustment'],
            ['JE-2026-0008', 'Customer receipt INV-2026-0004 — Outfitters', 'posted', -2, 240000, 'ACC-1000', 'ACC-1100', 'customer_payment'],
            ['JE-2026-0009', 'Finished goods completed WO-2026-0103', 'draft', -1, 150000, 'ACC-1400', 'ACC-1300', 'production_order'],
            ['JE-2026-0010', 'Monthly depreciation — cutting and sewing plant', 'draft', 0, 90000, 'ACC-6000', 'ACC-1000', 'depreciation'],
        ];

        foreach ($journalSpecs as [$reference, $description, $status, $dayOffset, $amount, $debitAccount, $creditAccount, $sourceType]) {
            $entryId = DB::table('journal_entries')->insertGetId([
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'accounting_book_id' => $bookId,
                'financial_year_id' => $financialYearId,
                'fiscal_period_id' => $periodId,
                'journal_number' => $reference,
                'document_date' => $today->copy()->addDays($dayOffset),
                'posting_date' => $today->copy()->addDays($dayOffset),
                'currency' => 'PKR',
                'exchange_rate_to_base' => 1,
                'reference' => $reference,
                'description' => $description,
                'status' => $status,
                'source_transaction_type' => $sourceType,
                'created_by' => $accountantId,
                'posted_by' => $status === 'posted' ? $accountantId : null,
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

        // Standard costs and components.
        foreach (['FG-1001' => 620, 'FG-1002' => 1850, 'FG-1003' => 2450, 'FG-1004' => 890, 'FG-1005' => 1120] as $sku => $standardCost) {
            $standardCostId = DB::table('standard_costs')->insertGetId([
                'tenant_id' => $tenantId,
                'item_id' => $itemIds[$sku],
                'effective_from' => $today->copy()->subMonths(6),
                'material_cost' => round($standardCost * 0.58, 2),
                'labour_cost' => round($standardCost * 0.22, 2),
                'machine_cost' => round($standardCost * 0.12, 2),
                'overhead_cost' => round($standardCost * 0.08, 2),
                'total_cost' => $standardCost,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ([
                ['material', round($standardCost * 0.58, 2)],
                ['labour', round($standardCost * 0.22, 2)],
                ['machine', round($standardCost * 0.12, 2)],
                ['overhead', round($standardCost * 0.08, 2)],
            ] as [$component, $amount]) {
                DB::table('standard_cost_components')->insert([
                    'tenant_id' => $tenantId,
                    'standard_cost_id' => $standardCostId,
                    'component_type' => $component,
                    'amount' => $amount,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

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
                'company_id' => $companyId,
                'production_order_id' => $orderIds[$orderIndex],
                'standard_cost' => $standard,
                'actual_cost' => $actual,
                'variance_amount' => $variance,
                'variance_percentage' => round(($variance / $standard) * 100, 2),
                'material_variance' => round($variance * 0.55, 2),
                'labour_variance' => round($variance * 0.2, 2),
                'machine_variance' => round($variance * 0.15, 2),
                'overhead_variance' => round($variance * 0.1, 2),
                'variance_explanation' => $variance > 0 ? 'Fabric price increase and overtime absorbed.' : 'Improved marker efficiency and lower machine downtime.',
                'calculated_at' => $now->copy()->subDays(2),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('production_costs')->insert([
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'factory_id' => $factoryId,
                'production_order_id' => $orderIds[$orderIndex],
                'material_cost' => round($actual * 0.58, 2),
                'labour_cost' => round($actual * 0.22, 2),
                'machine_cost' => round($actual * 0.12, 2),
                'overhead_cost' => round($actual * 0.08, 2),
                'wastage_cost' => 0,
                'rework_cost' => 0,
                'total_cost' => $actual,
                'unit_cost' => round($actual / $orderQuantities[$orderIndex], 2),
                'quantity' => $orderQuantities[$orderIndex],
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
            ['DSP-2026-0001', 0, 'FG-1001', 350, 'Rashid Mehmood', -10, 'delivered'],
            ['DSP-2026-0002', 1, 'FG-1002', 60, 'Naveed Akhtar', -8, 'delivered'],
            ['DSP-2026-0003', 2, 'FG-1001', 420, 'Shahid Iqbal', -6, 'delivered'],
            ['DSP-2026-0004', 3, 'FG-1004', 180, 'Rashid Mehmood', -4, 'in_transit'],
            ['DSP-2026-0005', 4, 'FG-1005', 90, 'Waqas Ali', -2, 'in_transit'],
            ['DSP-2026-0006', 5, 'FG-1001', 150, 'Naveed Akhtar', -1, 'pending'],
            ['DSP-2026-0007', 6, 'FG-1002', 120, 'Shahid Iqbal', 0, 'pending'],
            ['DSP-2026-0008', null, 'FG-1003', 40, 'Waqas Ali', 0, 'approved'],
        ];

        foreach ($dispatchSpecs as [$number, $soIndex, $sku, $qty, $driver, $dayOffset, $status]) {
            $dispatchId = DB::table('dispatches')->insertGetId([
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'factory_id' => $factoryId,
                'sales_order_id' => $soIndex === null ? null : ($salesOrderIds[$soIndex] ?? null),
                'dispatch_number' => $number,
                'dispatch_date' => $today->copy()->addDays($dayOffset),
                'vehicle_details' => json_encode(['type' => 'Truck', 'plate' => 'LEB-' . random_int(1000, 9999)]),
                'driver_name' => $driver,
                'status' => $status,
                'notes' => 'Dispatch against ' . ($soIndex === null ? 'stock transfer' : $salesOrderNumbers[$soIndex]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('dispatch_lines')->insert([
                'dispatch_id' => $dispatchId,
                'tenant_id' => $tenantId,
                'item_id' => $itemIds[$sku],
                'item_variant_id' => $variantIds[$sku][$styleColours[$sku][0]]['M'] ?? null,
                'quantity' => $qty,
                'unit_id' => $unitIds['PCS'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Fixed assets, numbering and outbox
        |--------------------------------------------------------------------------
        */
        $assetIds = [];
        foreach ([
            ['FA-0001', 'Automatic Cutting Machine', 'Plant & machinery', 8500000, 10],
            ['FA-0002', 'Stitching Line A — 32 machines', 'Plant & machinery', 12500000, 8],
            ['FA-0003', 'Steam Boiler & Finishing Plant', 'Plant & machinery', 3200000, 12],
        ] as [$code, $name, $category, $cost, $life]) {
            $assetIds[] = DB::table('fixed_assets')->insertGetId([
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'factory_id' => $factoryId,
                'asset_code' => $code,
                'name' => $name,
                'category' => $category,
                'acquisition_date' => $today->copy()->subYears(3),
                'acquisition_cost' => $cost,
                'salvage_value' => round($cost * 0.05, 2),
                'useful_life_years' => $life,
                'accumulated_depreciation' => round($cost * 0.3, 2),
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('fixed_asset_depreciation')->insert([
            'tenant_id' => $tenantId,
            'fixed_asset_id' => $assetIds[0],
            'period_start' => $today->copy()->startOfMonth(),
            'period_end' => $today->copy()->endOfMonth(),
            'depreciation_amount' => 70833,
            'accumulated_depreciation' => 2570833,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ([
            ['sales_order', 'SO', 6],
            ['purchase_order', 'PO', 4],
            ['production_order', 'WO', 6],
            ['goods_receipt', 'GRN', 6],
            ['material_issue', 'MI', 4],
            ['journal_entry', 'JE', 6],
            ['customer_invoice', 'INV', 6],
        ] as [$name, $prefix, $padding]) {
            DB::table('number_sequences')->insert([
                'tenant_id' => $tenantId,
                'sequence_name' => $name,
                'prefix' => $prefix,
                'padding' => $padding,
                'last_number' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            ['GoodsReceiptPosted', 'goods_receipt', 1],
            ['MaterialIssuedToProduction', 'production_order', 1],
            ['FinishedGoodsReleased', 'production_order', 1],
            ['CustomerInvoicePosted', 'customer_invoice', 1],
        ] as [$event, $aggregate, $aggregateId]) {
            DB::table('outbox_events')->insert([
                'tenant_id' => $tenantId,
                'event_type' => $event,
                'aggregate_type' => $aggregate,
                'aggregate_id' => $aggregateId,
                'payload' => json_encode(['tenant_id' => $tenantId, 'company_id' => $companyId]),
                'status' => 'processed',
                'available_at' => $now,
                'processed_at' => $now,
                'attempts' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * v1.1 schema gaps — tables present in the FactoryOS table catalogue but
 * missing from the original 107-table baseline.
 *
 * Grouped by domain but kept in one migration so the foundation can be
 * verified in a single forward step. Every block is guarded so the migration
 * is safe on databases that already received part of the schema.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->platformAndTenancy();
        $this->productAndGarment();
        $this->purchasingAndSales();
        $this->manufacturing();
        $this->accounting();
        $this->platformServices();
    }

    private function platformAndTenancy(): void
    {
        if (! Schema::hasTable('company_settings')) {
            Schema::create('company_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('setting_key');
                $table->text('setting_value')->nullable();
                $table->timestamps();
                $table->unique(['company_id', 'setting_key']);
            });
        }

        if (! Schema::hasTable('user_company_access')) {
            Schema::create('user_company_access', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->json('permissions')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['user_id', 'company_id']);
            });
        }

        if (! Schema::hasTable('working_calendars')) {
            Schema::create('working_calendars', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('factory_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name');
                $table->json('working_days')->nullable();
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->json('holidays')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    private function productAndGarment(): void
    {
        if (! Schema::hasTable('item_attributes')) {
            Schema::create('item_attributes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('code');
                $table->string('input_type')->default('select');
                $table->string('applies_to')->default('variant');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['tenant_id', 'code']);
            });
        }

        if (! Schema::hasTable('item_attribute_values')) {
            Schema::create('item_attribute_values', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('item_attribute_id')->constrained()->cascadeOnDelete();
                $table->string('value');
                $table->string('code')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['item_attribute_id', 'value']);
            });
        }

        if (! Schema::hasTable('item_variant_attribute_values')) {
            Schema::create('item_variant_attribute_values', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('item_variant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('item_attribute_id')->constrained()->cascadeOnDelete();
                $table->foreignId('item_attribute_value_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['item_variant_id', 'item_attribute_id']);
            });
        }

        if (! Schema::hasTable('size_sets')) {
            Schema::create('size_sets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('code');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['tenant_id', 'code']);
            });
        }

        if (! Schema::hasTable('size_set_values')) {
            Schema::create('size_set_values', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('size_set_id')->constrained()->cascadeOnDelete();
                $table->string('value');
                $table->string('code')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['size_set_id', 'value']);
            });
        }

        if (! Schema::hasTable('style_revisions')) {
            Schema::create('style_revisions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('item_id')->constrained()->cascadeOnDelete();
                $table->string('revision');
                $table->string('status')->default('draft');
                $table->text('technical_pack')->nullable();
                $table->date('effective_from')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
                $table->unique(['tenant_id', 'item_id', 'revision']);
            });
        }

        if (! Schema::hasTable('product_documents')) {
            Schema::create('product_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('item_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('document_type')->default('technical_pack');
                $table->string('file_path');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('file_size')->default(0);
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('stock_rolls')) {
            Schema::create('stock_rolls', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('factory_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('item_id')->constrained()->cascadeOnDelete();
                $table->foreignId('batch_id')->nullable()->constrained('stock_batches')->nullOnDelete();
                $table->string('roll_number');
                $table->decimal('length', 20, 6)->default(0);
                $table->decimal('remaining_length', 20, 6)->default(0);
                $table->decimal('weight', 20, 6)->default(0);
                $table->decimal('width', 12, 2)->nullable();
                $table->decimal('gsm', 12, 2)->nullable();
                $table->string('shade_code')->nullable();
                $table->string('status')->default('available');
                $table->timestamp('received_at')->nullable();
                $table->timestamps();
                $table->unique(['tenant_id', 'roll_number']);
                $table->index(['tenant_id', 'item_id', 'status']);
            });
        }

        if (! Schema::hasTable('supplier_items')) {
            Schema::create('supplier_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
                $table->foreignId('item_id')->constrained()->cascadeOnDelete();
                $table->string('supplier_item_code')->nullable();
                $table->decimal('last_price', 20, 6)->default(0);
                $table->unsignedInteger('lead_time_days')->default(0);
                $table->decimal('min_order_quantity', 20, 6)->default(0);
                $table->timestamps();
                $table->unique(['supplier_id', 'item_id']);
            });
        }
    }

    private function purchasingAndSales(): void
    {
        if (! Schema::hasTable('purchase_requisition_lines')) {
            Schema::create('purchase_requisition_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('purchase_requisition_id')->constrained()->cascadeOnDelete();
                $table->foreignId('item_id')->constrained()->cascadeOnDelete();
                $table->foreignId('item_variant_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('quantity', 20, 6);
                $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
                $table->date('required_date')->nullable();
                $table->string('status')->default('pending');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('supplier_quotations')) {
            Schema::create('supplier_quotations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
                $table->string('quotation_number');
                $table->date('quotation_date');
                $table->date('valid_until')->nullable();
                $table->string('status')->default('draft');
                $table->decimal('total_amount', 20, 6)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->unique(['tenant_id', 'quotation_number']);
            });
        }

        if (! Schema::hasTable('supplier_quotation_lines')) {
            Schema::create('supplier_quotation_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('supplier_quotation_id')->constrained()->cascadeOnDelete();
                $table->foreignId('item_id')->constrained()->cascadeOnDelete();
                $table->decimal('quantity', 20, 6);
                $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('unit_price', 20, 6)->default(0);
                $table->decimal('total_price', 20, 6)->default(0);
                $table->unsignedInteger('lead_time_days')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('purchase_returns')) {
            Schema::create('purchase_returns', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('factory_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
                $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
                $table->string('return_number');
                $table->date('return_date');
                $table->string('status')->default('draft');
                $table->text('reason')->nullable();
                $table->decimal('total_amount', 20, 6)->default(0);
                $table->timestamps();
                $table->unique(['tenant_id', 'return_number']);
            });
        }

        if (! Schema::hasTable('purchase_return_lines')) {
            Schema::create('purchase_return_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('purchase_return_id')->constrained()->cascadeOnDelete();
                $table->foreignId('item_id')->constrained()->cascadeOnDelete();
                $table->decimal('quantity', 20, 6);
                $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('unit_cost', 20, 6)->default(0);
                $table->decimal('total_cost', 20, 6)->default(0);
                $table->foreignId('batch_id')->nullable()->constrained('stock_batches')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('supplier_invoice_lines')) {
            Schema::create('supplier_invoice_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('supplier_invoice_id')->constrained()->cascadeOnDelete();
                $table->foreignId('item_id')->nullable()->constrained()->nullOnDelete();
                $table->string('description');
                $table->decimal('quantity', 20, 6)->default(1);
                $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('unit_price', 20, 6)->default(0);
                $table->foreignId('tax_code_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('tax_amount', 20, 6)->default(0);
                $table->decimal('total_amount', 20, 6)->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sales_quotations')) {
            Schema::create('sales_quotations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->string('quotation_number');
                $table->date('quotation_date');
                $table->date('valid_until')->nullable();
                $table->string('status')->default('draft');
                $table->decimal('total_amount', 20, 6)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->unique(['tenant_id', 'quotation_number']);
            });
        }

        if (! Schema::hasTable('sales_quotation_lines')) {
            Schema::create('sales_quotation_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sales_quotation_id')->constrained()->cascadeOnDelete();
                $table->foreignId('item_id')->constrained()->cascadeOnDelete();
                $table->foreignId('item_variant_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('quantity', 20, 6);
                $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('unit_price', 20, 6)->default(0);
                $table->decimal('total_price', 20, 6)->default(0);
                $table->timestamps();
            });
        }
    }

    private function manufacturing(): void
    {
        if (! Schema::hasTable('bom_size_consumptions')) {
            Schema::create('bom_size_consumptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('bom_component_id')->constrained()->cascadeOnDelete();
                $table->foreignId('size_set_value_id')->nullable()->constrained()->nullOnDelete();
                $table->string('size_label');
                $table->decimal('quantity', 20, 6);
                $table->decimal('wastage_percentage', 8, 4)->default(0);
                $table->timestamps();
                $table->unique(['bom_component_id', 'size_label']);
            });
        }

        if (! Schema::hasTable('work_order_assignments')) {
            Schema::create('work_order_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('work_centre_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
                $table->string('team_name')->nullable();
                $table->decimal('planned_hours', 10, 2)->default(0);
                $table->decimal('actual_hours', 10, 2)->default(0);
                $table->timestamp('assigned_at')->nullable();
                $table->timestamps();
            });
        }
    }

    private function accounting(): void
    {
        if (! Schema::hasTable('accounting_dimensions')) {
            Schema::create('accounting_dimensions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('code');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['tenant_id', 'code']);
            });
        }

        if (! Schema::hasTable('dimension_values')) {
            Schema::create('dimension_values', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('accounting_dimension_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('code');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['accounting_dimension_id', 'code']);
            });
        }

        if (! Schema::hasTable('tax_transactions')) {
            Schema::create('tax_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('tax_code_id')->constrained()->cascadeOnDelete();
                $table->string('source_type');
                $table->unsignedBigInteger('source_id');
                $table->decimal('taxable_amount', 20, 6)->default(0);
                $table->decimal('tax_amount', 20, 6)->default(0);
                $table->string('currency', 3)->default('PKR');
                $table->date('transaction_date');
                $table->timestamps();
                $table->index(['source_type', 'source_id']);
            });
        }

        if (! Schema::hasTable('recurring_journals')) {
            Schema::create('recurring_journals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('accounting_book_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('frequency')->default('monthly');
                $table->date('next_run_date')->nullable();
                $table->json('template_lines')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('standard_cost_components')) {
            Schema::create('standard_cost_components', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('standard_cost_id')->constrained()->cascadeOnDelete();
                $table->string('component_type');
                $table->decimal('amount', 20, 6)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('fixed_assets')) {
            Schema::create('fixed_assets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('factory_id')->nullable()->constrained()->nullOnDelete();
                $table->string('asset_code');
                $table->string('name');
                $table->string('category')->nullable();
                $table->date('acquisition_date');
                $table->decimal('acquisition_cost', 20, 6)->default(0);
                $table->decimal('salvage_value', 20, 6)->default(0);
                $table->unsignedInteger('useful_life_years')->default(5);
                $table->decimal('accumulated_depreciation', 20, 6)->default(0);
                $table->string('status')->default('active');
                $table->timestamps();
                $table->unique(['tenant_id', 'asset_code']);
            });
        }

        if (! Schema::hasTable('fixed_asset_depreciation')) {
            Schema::create('fixed_asset_depreciation', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
                $table->date('period_start');
                $table->date('period_end');
                $table->decimal('depreciation_amount', 20, 6)->default(0);
                $table->decimal('accumulated_depreciation', 20, 6)->default(0);
                $table->foreignId('journal_entry_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    private function platformServices(): void
    {
        if (! Schema::hasTable('outbox_events')) {
            Schema::create('outbox_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
                $table->string('event_type');
                $table->string('aggregate_type');
                $table->unsignedBigInteger('aggregate_id');
                $table->json('payload')->nullable();
                $table->string('status')->default('pending');
                $table->timestamp('available_at')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->unsignedInteger('attempts')->default(0);
                $table->text('last_error')->nullable();
                $table->timestamps();
                $table->index(['status', 'available_at']);
            });
        }
    }

    public function down(): void
    {
        foreach ([
            'outbox_events',
            'fixed_asset_depreciation',
            'fixed_assets',
            'standard_cost_components',
            'recurring_journals',
            'tax_transactions',
            'dimension_values',
            'accounting_dimensions',
            'work_order_assignments',
            'bom_size_consumptions',
            'sales_quotation_lines',
            'sales_quotations',
            'supplier_invoice_lines',
            'purchase_return_lines',
            'purchase_returns',
            'supplier_quotation_lines',
            'supplier_quotations',
            'purchase_requisition_lines',
            'supplier_items',
            'stock_rolls',
            'product_documents',
            'style_revisions',
            'size_set_values',
            'size_sets',
            'item_variant_attribute_values',
            'item_attribute_values',
            'item_attributes',
            'working_calendars',
            'user_company_access',
            'company_settings',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};

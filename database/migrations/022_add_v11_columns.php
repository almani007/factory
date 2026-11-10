<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * v1.1 column gaps — non-breaking additions to the existing core tables.
 *
 * The original schema already carries most of the operational fields. These
 * additions bring the core records up to the v1.1 catalogue without renaming
 * or removing existing columns (which would break the current module models).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (! Schema::hasColumn('items', 'item_type')) {
                $table->string('item_type')->default('raw_material')->after('name');
            }
            if (! Schema::hasColumn('items', 'tracking_method')) {
                $table->string('tracking_method')->default('none')->after('track_serial');
            }
            if (! Schema::hasColumn('items', 'is_purchasable')) {
                $table->boolean('is_purchasable')->default(true)->after('tracking_method');
            }
            if (! Schema::hasColumn('items', 'is_manufacturable')) {
                $table->boolean('is_manufacturable')->default(false)->after('is_purchasable');
            }
            if (! Schema::hasColumn('items', 'is_sellable')) {
                $table->boolean('is_sellable')->default(false)->after('is_manufacturable');
            }
        });

        Schema::table('item_variants', function (Blueprint $table) {
            if (! Schema::hasColumn('item_variants', 'barcode')) {
                $table->string('barcode')->nullable()->after('variant_sku');
            }
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_movements', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('tenant_id')->constrained()->nullOnDelete();
            }
        });

        Schema::table('stock_movement_lines', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_movement_lines', 'roll_id')) {
                $table->foreignId('roll_id')->nullable()->after('batch_id')->constrained('stock_rolls')->nullOnDelete();
            }
            if (! Schema::hasColumn('stock_movement_lines', 'source_type')) {
                $table->string('source_type')->nullable()->after('total_cost');
            }
            if (! Schema::hasColumn('stock_movement_lines', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            }
            if (! Schema::hasColumn('stock_movement_lines', 'source_line_id')) {
                $table->unsignedBigInteger('source_line_id')->nullable()->after('source_id');
            }
            if (! Schema::hasColumn('stock_movement_lines', 'reversal_of_line_id')) {
                $table->unsignedBigInteger('reversal_of_line_id')->nullable()->after('source_line_id');
            }
        });

        Schema::table('bom_versions', function (Blueprint $table) {
            if (! Schema::hasColumn('bom_versions', 'base_quantity')) {
                $table->decimal('base_quantity', 20, 6)->default(1)->after('version');
            }
            if (! Schema::hasColumn('bom_versions', 'base_unit_id')) {
                $table->foreignId('base_unit_id')->nullable()->after('base_quantity')->constrained('units')->nullOnDelete();
            }
            if (! Schema::hasColumn('bom_versions', 'approval_status')) {
                $table->string('approval_status')->default('draft')->after('is_active');
            }
        });

        Schema::table('bom_components', function (Blueprint $table) {
            if (! Schema::hasColumn('bom_components', 'consumption_type')) {
                $table->string('consumption_type')->default('fixed')->after('quantity');
            }
        });

        Schema::table('production_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('production_orders', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('tenant_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('production_orders', 'production_type')) {
                $table->string('production_type')->default('mts')->after('order_number');
            }
            if (! Schema::hasColumn('production_orders', 'source_type')) {
                $table->string('source_type')->nullable()->after('production_type');
            }
            if (! Schema::hasColumn('production_orders', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            }
            if (! Schema::hasColumn('production_orders', 'released_at')) {
                $table->timestamp('released_at')->nullable()->after('approved_at');
            }
            if (! Schema::hasColumn('production_orders', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('released_at');
            }
            if (! Schema::hasColumn('production_orders', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('completed_at')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('production_order_lines', function (Blueprint $table) {
            if (! Schema::hasColumn('production_order_lines', 'item_variant_id')) {
                $table->foreignId('item_variant_id')->nullable()->after('item_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('production_order_lines', 'size_label')) {
                $table->string('size_label')->nullable()->after('item_variant_id');
            }
            if (! Schema::hasColumn('production_order_lines', 'colour')) {
                $table->string('colour')->nullable()->after('size_label');
            }
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('journal_entries', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('tenant_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('journal_entries', 'financial_year_id')) {
                $table->foreignId('financial_year_id')->nullable()->after('accounting_book_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('journal_entries', 'journal_number')) {
                $table->string('journal_number')->nullable()->after('reference');
            }
            if (! Schema::hasColumn('journal_entries', 'posted_by')) {
                $table->foreignId('posted_by')->nullable()->after('approved_by')->constrained('users')->nullOnDelete();
            }
        });

        // Company scoping on commercial/production document headers.
        foreach ([
            'sales_orders',
            'purchase_orders',
            'goods_receipts',
            'customer_invoices',
            'supplier_bills',
            'supplier_invoices',
            'dispatches',
            'delivery_orders',
            'quality_inspections',
            'material_issues',
            'work_orders',
            'production_entries',
            'production_costs',
            'production_cost_variances',
        ] as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'company_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('company_id')->nullable()->after('tenant_id')->constrained()->nullOnDelete();
                });
            }
        }

        // Variant breakdown on document lines.
        foreach ([
            'sales_order_lines',
            'purchase_order_lines',
            'goods_receipt_lines',
            'dispatch_lines',
            'delivery_order_lines',
            'customer_invoice_lines',
            'customer_return_lines',
        ] as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'item_variant_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('item_variant_id')->nullable()->after('item_id')->constrained()->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ([
            'sales_order_lines',
            'purchase_order_lines',
            'goods_receipt_lines',
            'dispatch_lines',
            'delivery_order_lines',
            'customer_invoice_lines',
            'customer_return_lines',
        ] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'item_variant_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropConstrainedForeignId('item_variant_id');
                });
            }
        }

        foreach ([
            'sales_orders',
            'purchase_orders',
            'goods_receipts',
            'customer_invoices',
            'supplier_bills',
            'supplier_invoices',
            'dispatches',
            'delivery_orders',
            'quality_inspections',
            'material_issues',
            'work_orders',
            'production_entries',
            'production_costs',
            'production_cost_variances',
        ] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'company_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropConstrainedForeignId('company_id');
                });
            }
        }

        Schema::table('journal_entries', function (Blueprint $table) {
            foreach (['posted_by', 'financial_year_id', 'company_id'] as $column) {
                if (Schema::hasColumn('journal_entries', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }
            if (Schema::hasColumn('journal_entries', 'journal_number')) {
                $table->dropColumn('journal_number');
            }
        });

        Schema::table('production_order_lines', function (Blueprint $table) {
            if (Schema::hasColumn('production_order_lines', 'item_variant_id')) {
                $table->dropConstrainedForeignId('item_variant_id');
            }
            foreach (['colour', 'size_label'] as $column) {
                if (Schema::hasColumn('production_order_lines', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('production_orders', function (Blueprint $table) {
            foreach (['created_by', 'company_id'] as $column) {
                if (Schema::hasColumn('production_orders', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }
            foreach (['completed_at', 'released_at', 'source_id', 'source_type', 'production_type'] as $column) {
                if (Schema::hasColumn('production_orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('bom_components', function (Blueprint $table) {
            if (Schema::hasColumn('bom_components', 'consumption_type')) {
                $table->dropColumn('consumption_type');
            }
        });

        Schema::table('bom_versions', function (Blueprint $table) {
            foreach (['base_unit_id'] as $column) {
                if (Schema::hasColumn('bom_versions', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }
            foreach (['approval_status', 'base_quantity'] as $column) {
                if (Schema::hasColumn('bom_versions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('stock_movement_lines', function (Blueprint $table) {
            if (Schema::hasColumn('stock_movement_lines', 'roll_id')) {
                $table->dropConstrainedForeignId('roll_id');
            }
            foreach (['reversal_of_line_id', 'source_line_id', 'source_id', 'source_type'] as $column) {
                if (Schema::hasColumn('stock_movement_lines', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            if (Schema::hasColumn('stock_movements', 'company_id')) {
                $table->dropConstrainedForeignId('company_id');
            }
        });

        Schema::table('item_variants', function (Blueprint $table) {
            if (Schema::hasColumn('item_variants', 'barcode')) {
                $table->dropColumn('barcode');
            }
        });

        Schema::table('items', function (Blueprint $table) {
            foreach (['is_sellable', 'is_manufacturable', 'is_purchasable', 'tracking_method', 'item_type'] as $column) {
                if (Schema::hasColumn('items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

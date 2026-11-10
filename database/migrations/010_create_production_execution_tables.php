<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_variant_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('production_order_id')->nullable()->constrained()->onDelete('cascade');
            $table->decimal('quantity', 12, 4);
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamp('reserved_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'production_order_id']);
            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('material_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('production_order_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('work_order_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('reference');
            $table->timestamp('issue_date');
            $table->foreignId('issued_by')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'production_order_id']);
        });

        Schema::create('material_issue_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_issue_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_variant_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('stock_movement_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('quantity', 12, 4);
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('bin_id')->nullable()->constrained('warehouse_bins')->onDelete('set null');
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->decimal('total_cost', 15, 4)->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'material_issue_id']);
        });

        Schema::create('material_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('production_order_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('work_order_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('reference');
            $table->timestamp('return_date');
            $table->foreignId('returned_by')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'production_order_id']);
        });

        Schema::create('material_return_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_return_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->foreignId('stock_movement_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('quantity', 12, 4);
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('bin_id')->nullable()->constrained('warehouse_bins')->onDelete('set null');
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->decimal('total_cost', 15, 4)->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'material_return_id']);
        });

        Schema::create('production_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('production_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('work_order_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamp('entry_date');
            $table->decimal('good_quantity', 12, 4)->default(0);
            $table->decimal('rejected_quantity', 12, 4)->default(0);
            $table->decimal('rework_quantity', 12, 4)->default(0);
            $table->decimal('scrap_quantity', 12, 4)->default(0);
            $table->foreignId('operator_id')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedBigInteger('machine_id')->nullable();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'production_order_id']);
            $table->index(['tenant_id', 'entry_date']);
        });

        Schema::create('production_entry_outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_entry_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 12, 4);
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('bin_id')->nullable()->constrained('warehouse_bins')->onDelete('set null');
            $table->timestamps();

            $table->index(['tenant_id', 'production_entry_id']);
        });

        Schema::create('production_scrap', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('production_order_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('production_entry_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 12, 4);
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('reason');
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->decimal('total_cost', 15, 4)->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'production_order_id']);
        });

        Schema::create('production_downtime', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('work_order_id')->nullable()->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('machine_id')->nullable();
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->integer('duration_minutes');
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'work_order_id']);
        });

        Schema::create('finished_goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('production_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->string('receipt_number')->unique();
            $table->timestamp('receipt_date');
            $table->decimal('quantity', 12, 4);
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('stock_movement_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('received_by')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'production_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finished_goods_receipts');
        Schema::dropIfExists('production_downtime');
        Schema::dropIfExists('production_scrap');
        Schema::dropIfExists('production_entry_outputs');
        Schema::dropIfExists('production_entries');
        Schema::dropIfExists('material_return_lines');
        Schema::dropIfExists('material_returns');
        Schema::dropIfExists('material_issue_lines');
        Schema::dropIfExists('material_issues');
        Schema::dropIfExists('material_reservations');
    }
};
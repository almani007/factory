<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('plan_number')->unique();
            $table->date('plan_date');
            $table->string('planning_type'); // make_to_stock, make_to_order
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('production_plan_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->decimal('planned_quantity', 12, 4);
            $table->date('required_date');
            $table->integer('priority')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'production_plan_id']);
        });

        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('sales_order_id')->nullable();
            $table->foreignId('bom_header_id')->constrained()->onDelete('cascade');
            $table->foreignId('bom_version_id')->constrained()->onDelete('cascade');
            $table->foreignId('routing_version_id')->nullable()->constrained()->onDelete('set null');
            $table->string('order_number')->unique();
            $table->decimal('planned_quantity', 12, 4);
            $table->decimal('actual_quantity', 12, 4)->default(0);
            $table->decimal('good_quantity', 12, 4)->default(0);
            $table->decimal('rejected_quantity', 12, 4)->default(0);
            $table->decimal('rework_quantity', 12, 4)->default(0);
            $table->decimal('scrap_quantity', 12, 4)->default(0);
            $table->timestamp('planned_start_date')->nullable();
            $table->timestamp('planned_end_date')->nullable();
            $table->timestamp('actual_start_date')->nullable();
            $table->timestamp('actual_end_date')->nullable();
            $table->string('status')->default('draft');
            $table->string('priority')->default('normal');
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'factory_id', 'status']);
            $table->index(['tenant_id', 'bom_version_id']);
        });

        Schema::create('production_order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 12, 4);
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index(['tenant_id', 'production_order_id']);
        });

        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('production_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('work_centre_id')->nullable()->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('machine_id')->nullable();
            $table->foreignId('operator_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('operation_name');
            $table->decimal('planned_quantity', 12, 4);
            $table->decimal('actual_quantity', 12, 4)->default(0);
            $table->decimal('good_quantity', 12, 4)->default(0);
            $table->decimal('rejected_quantity', 12, 4)->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('downtime_minutes')->default(0);
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'factory_id', 'status']);
            $table->index(['tenant_id', 'production_order_id']);
        });

        Schema::create('work_order_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('routing_operation_id')->nullable()->constrained()->onDelete('set null');
            $table->string('operation_name');
            $table->integer('sequence');
            $table->timestamps();

            $table->index(['tenant_id', 'work_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_operations');
        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('production_order_lines');
        Schema::dropIfExists('production_orders');
        Schema::dropIfExists('production_plan_lines');
        Schema::dropIfExists('production_plans');
    }
};
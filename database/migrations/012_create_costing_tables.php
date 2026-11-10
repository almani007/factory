<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('costing_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('standard_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->decimal('material_cost', 15, 4)->default(0);
            $table->decimal('labour_cost', 15, 4)->default(0);
            $table->decimal('machine_cost', 15, 4)->default(0);
            $table->decimal('overhead_cost', 15, 4)->default(0);
            $table->decimal('total_cost', 15, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'item_id', 'is_active']);
        });

        Schema::create('production_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('production_order_id')->constrained()->onDelete('cascade');
            $table->decimal('material_cost', 15, 4)->default(0);
            $table->decimal('labour_cost', 15, 4)->default(0);
            $table->decimal('machine_cost', 15, 4)->default(0);
            $table->decimal('overhead_cost', 15, 4)->default(0);
            $table->decimal('wastage_cost', 15, 4)->default(0);
            $table->decimal('rework_cost', 15, 4)->default(0);
            $table->decimal('total_cost', 15, 4)->default(0);
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->decimal('quantity', 12, 4);
            $table->timestamp('costed_at');
            $table->timestamps();

            $table->index(['tenant_id', 'production_order_id']);
        });

        Schema::create('production_cost_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_cost_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('cost_component'); // material, labour, machine, overhead, etc.
            $table->decimal('amount', 15, 4);
            $table->string('source_type');
            $table->unsignedBigInteger('source_id');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'production_cost_id']);
        });

        Schema::create('labour_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('production_order_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('work_order_id')->nullable()->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->decimal('hours', 10, 4);
            $table->decimal('rate_per_hour', 15, 4);
            $table->decimal('total_cost', 15, 4);
            $table->timestamp('worked_at');
            $table->timestamps();

            $table->index(['tenant_id', 'worked_at']);
        });

        Schema::create('machine_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('production_order_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('work_order_id')->nullable()->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('machine_id')->nullable();
            $table->decimal('hours', 10, 4);
            $table->decimal('rate_per_hour', 15, 4);
            $table->decimal('total_cost', 15, 4);
            $table->timestamp('operated_at');
            $table->timestamps();

            $table->index(['tenant_id', 'operated_at']);
        });

        Schema::create('overhead_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('production_order_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('cost_type');
            $table->decimal('amount', 15, 4);
            $table->text('description')->nullable();
            $table->timestamp('incurred_at');
            $table->timestamps();

            $table->index(['tenant_id', 'incurred_at']);
        });

        Schema::create('cost_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('production_order_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('allocation_type');
            $table->decimal('amount', 15, 4);
            $table->string('source_type');
            $table->unsignedBigInteger('source_id');
            $table->timestamps();

            $table->index(['tenant_id', 'allocation_type']);
        });

        Schema::create('production_cost_variances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('production_order_id')->constrained()->onDelete('cascade');
            $table->decimal('standard_cost', 15, 4);
            $table->decimal('actual_cost', 15, 4);
            $table->decimal('variance_amount', 15, 4);
            $table->decimal('variance_percentage', 8, 4);
            $table->decimal('material_variance', 15, 4)->default(0);
            $table->decimal('labour_variance', 15, 4)->default(0);
            $table->decimal('machine_variance', 15, 4)->default(0);
            $table->decimal('overhead_variance', 15, 4)->default(0);
            $table->text('variance_explanation')->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->index(['tenant_id', 'production_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_cost_variances');
        Schema::dropIfExists('cost_allocations');
        Schema::dropIfExists('overhead_costs');
        Schema::dropIfExists('machine_costs');
        Schema::dropIfExists('labour_costs');
        Schema::dropIfExists('production_cost_lines');
        Schema::dropIfExists('production_costs');
        Schema::dropIfExists('standard_costs');
        Schema::dropIfExists('costing_methods');
    }
};
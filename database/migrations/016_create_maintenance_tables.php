<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('machine_code')->unique();
            $table->string('name');
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->foreignId('work_centre_id')->nullable()->constrained()->onDelete('set null');
            $table->date('installation_date')->nullable();
            $table->decimal('purchase_cost', 15, 4)->default(0);
            $table->decimal('expected_capacity', 12, 4)->default(0);
            $table->string('operating_status')->default('operational');
            $table->json('warranty_info')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'factory_id']);
        });

        Schema::create('machine_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->unique();
            $table->timestamps();

            $table->index(['tenant_id']);
        });

        Schema::create('machine_meter_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');
            $table->decimal('reading', 12, 4);
            $table->timestamp('reading_at');
            $table->timestamps();

            $table->index(['tenant_id', 'machine_id']);
        });

        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('maintenance_type'); // preventive, breakdown
            $table->integer('frequency_days');
            $table->date('next_due_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'next_due_date']);
        });

        Schema::create('maintenance_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->string('status')->default('open');
            $table->date('scheduled_date');
            $table->date('completed_date')->nullable();
            $table->text('description');
            $table->decimal('cost', 15, 4)->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('maintenance_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('task_name');
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'maintenance_order_id']);
        });

        Schema::create('machine_breakdowns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');
            $table->timestamp('breakdown_at');
            $table->timestamp('repaired_at')->nullable();
            $table->string('reason');
            $table->text('description');
            $table->decimal('downtime_hours', 10, 4)->default(0);
            $table->decimal('repair_cost', 15, 4)->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'machine_id']);
        });

        Schema::create('machine_spare_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('maintenance_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 12, 4);
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index(['tenant_id', 'maintenance_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machine_spare_parts');
        Schema::dropIfExists('machine_breakdowns');
        Schema::dropIfExists('maintenance_tasks');
        Schema::dropIfExists('maintenance_orders');
        Schema::dropIfExists('maintenance_schedules');
        Schema::dropIfExists('machine_meter_readings');
        Schema::dropIfExists('machine_categories');
        Schema::dropIfExists('machines');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('employee_code')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->string('designation')->nullable();
            $table->date('hire_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'factory_id']);
        });

        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('contract_type');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('salary', 15, 4)->default(0);
            $table->string('currency', 3)->default('PKR');
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id']);
        });

        Schema::create('employee_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('shift_id')->constrained()->onDelete('cascade');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id']);
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('attendance_date');
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->decimal('hours_worked', 10, 4)->default(0);
            $table->string('status')->default('present');
            $table->timestamps();

            $table->unique(['tenant_id', 'employee_id', 'attendance_date']);
            $table->index(['tenant_id', 'attendance_date']);
        });

        Schema::create('labour_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('production_order_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('work_order_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->decimal('hours', 10, 4);
            $table->decimal('rate_per_hour', 15, 4);
            $table->decimal('total_cost', 15, 4);
            $table->timestamp('worked_at');
            $table->timestamps();

            $table->index(['tenant_id', 'worked_at']);
        });

        Schema::create('overtime_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('overtime_date');
            $table->decimal('hours', 10, 4);
            $table->decimal('rate_per_hour', 15, 4);
            $table->decimal('total_cost', 15, 4);
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'overtime_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_records');
        Schema::dropIfExists('labour_entries');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('employee_shifts');
        Schema::dropIfExists('employee_contracts');
        Schema::dropIfExists('employees');
    }
};
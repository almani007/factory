<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // HR Leave Requests
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('leave_number');
            $table->string('leave_type'); // annual, sick, personal, maternity, paternity, unpaid
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('pending'); // pending, approved, rejected, cancelled
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });

        // HR Time Entries
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('entry_date');
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->decimal('hours_worked', 6, 2)->default(0);
            $table->string('status')->default('present'); // present, absent, late, half_day
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'entry_date']);
        });

        // HR Shifts
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('shift_name');
            $table->date('shift_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('status')->default('scheduled'); // scheduled, completed, missed, cancelled
            $table->string('location')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'shift_date']);
        });

        // HR Payroll Runs
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('run_name');
            $table->date('pay_period_start');
            $table->date('pay_period_end');
            $table->string('status')->default('draft'); // draft, processing, completed, paid
            $table->decimal('total_gross', 15, 2)->default(0);
            $table->decimal('total_net', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });

        // HR Performance Reviews
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('review_title');
            $table->string('review_period');
            $table->date('review_date');
            $table->decimal('overall_rating', 3, 2); // 1-5 scale
            $table->string('status')->default('pending'); // pending, in_progress, completed, approved
            $table->text('goals')->nullable();
            $table->text('achievements')->nullable();
            $table->text('improvement_areas')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });

        // HR Training Programs
        Schema::create('training_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('program_name');
            $table->string('training_type');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('cost', 15, 2)->default(0);
            $table->string('status')->default('scheduled'); // scheduled, in_progress, completed, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });

        // HR Training Records
        Schema::create('training_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('training_program_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('status')->default('enrolled'); // enrolled, in_progress, completed, failed
            $table->date('completion_date')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'training_program_id']);
        });

        // Training Matrix
        Schema::create('training_matrix', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('skill_name');
            $table->string('skill_level'); // beginner, intermediate, advanced, expert
            $table->date('certification_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('active'); // active, expired, expiring_soon, not_started
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_matrix');
        Schema::dropIfExists('training_records');
        Schema::dropIfExists('training_programs');
        Schema::dropIfExists('performance_reviews');
        Schema::dropIfExists('payroll_runs');
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('time_entries');
        Schema::dropIfExists('leave_requests');
    }
};

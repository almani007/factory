<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('body');
            $table->string('type')->default('info'); // info, warning, error, success
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'read_at']);
            $table->index(['user_id', 'read_at']);
        });

        // Workflow Approvals
        Schema::create('workflow_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('workflow_type');
            $table->string('reference_number');
            $table->string('status')->default('pending'); // pending, approved, rejected, cancelled
            $table->date('requested_date');
            $table->date('approved_date')->nullable();
            $table->text('comments')->nullable();
            $table->foreignId('approver_id')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });

        // Workflow Rules
        Schema::create('workflow_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('rule_name');
            $table->string('workflow_type');
            $table->text('condition');
            $table->string('action');
            $table->string('status')->default('active'); // active, inactive, draft, testing
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });

        // Integrations Webhooks
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('url');
            $table->string('events'); // JSON array of event types
            $table->string('status')->default('active'); // active, inactive, error, testing
            $table->timestamp('last_triggered')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('webhook_id')->constrained()->onDelete('cascade');
            $table->string('event_type');
            $table->text('payload');
            $table->integer('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->index(['webhook_id', 'delivered_at']);
        });

        // Engineering Change Orders
        Schema::create('engineering_change_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->constrained()->onDelete('cascade');
            $table->string('change_number');
            $table->string('title');
            $table->string('change_type'); // minor, major, emergency, standard
            $table->text('description');
            $table->string('status')->default('draft'); // draft, submitted, under_review, approved, implemented, closed, rejected
            $table->date('requested_date');
            $table->date('approved_date')->nullable();
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });

        // HR Training Matrix
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

        // Production Downtime
        Schema::create('production_downtime', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->constrained()->onDelete('cascade');
            $table->foreignId('machine_id')->constrained('machines')->onDelete('cascade');
            $table->string('downtime_number');
            $table->string('downtime_type');
            $table->datetime('start_time');
            $table->datetime('end_time')->nullable();
            $table->decimal('duration_minutes', 10, 2)->default(0);
            $table->string('reason');
            $table->string('status')->default('scheduled'); // scheduled, in_progress, completed, cancelled
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });

        // Production Scrap
        Schema::create('production_scrap', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->string('scrap_number');
            $table->decimal('quantity', 15, 4);
            $table->date('scrap_date');
            $table->string('reason');
            $table->string('status')->default('draft'); // draft, submitted, approved, disposed, cancelled
            $table->decimal('cost', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });

        // Maintenance Tools
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->constrained()->onDelete('cascade');
            $table->string('tool_code');
            $table->string('tool_name');
            $table->string('tool_type');
            $table->string('status')->default('active'); // active, inactive, maintenance, retired
            $table->date('purchase_date')->nullable();
            $table->date('last_calibration')->nullable();
            $table->date('next_calibration')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });

        // Tool Calibrations
        Schema::create('tool_calibrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('tool_id')->constrained('tools')->onDelete('cascade');
            $table->date('calibration_date');
            $table->date('next_calibration_date');
            $table->string('calibrated_by');
            $table->string('status')->default('scheduled'); // scheduled, completed, overdue, cancelled
            $table->string('certificate_number')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tool_calibrations');
        Schema::dropIfExists('tools');
        Schema::dropIfExists('production_scrap');
        Schema::dropIfExists('production_downtime');
        Schema::dropIfExists('training_matrix');
        Schema::dropIfExists('engineering_change_orders');
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhooks');
        Schema::dropIfExists('workflow_rules');
        Schema::dropIfExists('workflow_approvals');
        Schema::dropIfExists('notifications');
    }
};

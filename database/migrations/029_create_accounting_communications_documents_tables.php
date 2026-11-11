<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Costing Activity Centers
        Schema::create('activity_centers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->constrained()->onDelete('cascade');
            $table->string('activity_name');
            $table->string('activity_type');
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->string('allocation_base');
            $table->decimal('allocation_rate', 15, 4)->default(0);
            $table->string('status')->default('active'); // active, inactive, under_review
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('activity_center_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('activity_center_id')->constrained()->onDelete('cascade');
            $table->date('cost_date');
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['activity_center_id', 'cost_date']);
        });

        // Accounting Fixed Assets
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained('chart_of_accounts')->onDelete('cascade');
            $table->string('asset_code');
            $table->string('asset_name');
            $table->string('asset_category');
            $table->date('purchase_date');
            $table->decimal('purchase_cost', 15, 2)->default(0);
            $table->decimal('salvage_value', 15, 2)->default(0);
            $table->integer('useful_life_years');
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);
            $table->string('status')->default('active'); // active, disposed, retired
            $table->string('location')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('fixed_asset_depreciation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('fixed_asset_id')->constrained()->onDelete('cascade');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('depreciation_amount', 15, 2)->default(0);
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);
            $table->timestamps();
            $table->unique(['fixed_asset_id', 'period_start']);
        });

        // Accounting Bank Reconciliations
        Schema::create('bank_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained('chart_of_accounts')->onDelete('cascade');
            $table->string('reference');
            $table->date('statement_date');
            $table->decimal('book_balance', 15, 2)->default(0);
            $table->decimal('bank_balance', 15, 2)->default(0);
            $table->string('status')->default('open'); // open, pending, reconciled
            $table->date('reconciled_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });

        // Accounting Bank Reconciliation Lines
        Schema::create('bank_reconciliation_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('bank_reconciliation_id')->constrained()->onDelete('cascade');
            $table->string('transaction_type'); // bank_only, book_only, matched
            $table->string('transaction_reference');
            $table->date('transaction_date');
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Documents
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('document_number');
            $table->string('title');
            $table->string('category'); // procedure, work_instruction, policy, specification, record, form
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_size')->nullable();
            $table->string('version')->default('1.0');
            $table->string('status')->default('draft'); // draft, active, archived, expired
            $table->date('upload_date');
            $table->date('expiry_date')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });

        // Documents Versions
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->string('version');
            $table->string('file_path');
            $table->string('file_size')->nullable();
            $table->date('upload_date');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->text('change_description')->nullable();
            $table->timestamps();
        });

        // Communications Email Templates
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('template_name');
            $table->string('template_type');
            $table->string('subject');
            $table->text('body');
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });

        // Communications Email Logs
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('template_name')->nullable();
            $table->string('recipient');
            $table->string('subject');
            $table->text('body')->nullable();
            $table->string('status'); // sent, failed, pending
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'sent_at']);
        });

        // Communications SMS Templates
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('template_name');
            $table->text('message');
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });

        // Communications SMS Logs
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('recipient');
            $table->text('message');
            $table->string('status'); // sent, failed, pending
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
        Schema::dropIfExists('sms_templates');
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('bank_reconciliation_lines');
        Schema::dropIfExists('bank_reconciliations');
        Schema::dropIfExists('fixed_asset_depreciation');
        Schema::dropIfExists('fixed_assets');
        Schema::dropIfExists('activity_center_costs');
        Schema::dropIfExists('activity_centers');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('subscription_plans')->onDelete('cascade');
            $table->string('status')->default('active');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('monthly_price', 12, 2);
            $table->integer('max_users');
            $table->integer('max_factories');
            $table->integer('max_warehouses');
            $table->integer('monthly_transactions');
            $table->integer('storage_gb');
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('subscription_features', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('tenant_licences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('licence_key')->unique();
            $table->string('licence_type');
            $table->timestamp('valid_from');
            $table->timestamp('valid_until');
            $table->text('licence_file_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->string('batch_number')->unique();
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('received_quantity', 12, 4)->default(0);
            $table->decimal('remaining_quantity', 12, 4)->default(0);
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index(['tenant_id', 'item_id']);
        });

        Schema::create('stock_serial_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained()->onDelete('set null');
            $table->string('serial_number')->unique();
            $table->string('status')->default('available');
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();

            $table->index(['tenant_id', 'item_id']);
        });

        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->string('adjustment_number')->unique();
            $table->date('adjustment_date');
            $table->string('reason');
            $table->foreignId('adjusted_by')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'adjustment_date']);
        });

        Schema::create('stock_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->string('count_number')->unique();
            $table->date('count_date');
            $table->string('status')->default('draft');
            $table->foreignId('counted_by')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('stock_count_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_count_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->decimal('counted_quantity', 12, 4);
            $table->decimal('system_quantity', 12, 4);
            $table->decimal('variance_quantity', 12, 4);
            $table->timestamps();

            $table->index(['tenant_id', 'stock_count_id']);
        });

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('source_warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('destination_warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->string('transfer_number')->unique();
            $table->date('transfer_date');
            $table->string('status')->default('pending');
            $table->foreignId('transferred_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('stock_transfer_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 12, 4);
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index(['tenant_id', 'stock_transfer_id']);
        });

        Schema::create('stock_valuation_layers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('quantity', 12, 4);
            $table->decimal('unit_cost', 15, 4);
            $table->decimal('total_cost', 15, 4);
            $table->string('valuation_method');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'item_id']);
        });

        Schema::create('inventory_cost_layers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('stock_movement_line_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity_remaining', 12, 4);
            $table->decimal('unit_cost', 15, 4);
            $table->timestamps();

            $table->index(['tenant_id', 'stock_movement_line_id']);
        });

        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('model_type');
            $table->string('field_name');
            $table->string('field_type');
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'model_type']);
        });

        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_field_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->text('field_value')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'entity_type', 'entity_id']);
        });

        Schema::create('number_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('sequence_name')->unique();
            $table->string('prefix');
            $table->integer('padding')->default(6);
            $table->integer('last_number')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'sequence_name']);
        });

        Schema::create('industry_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->json('default_modules')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('industry_template_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('industry_template_id')->constrained()->onDelete('cascade');
            $table->string('module_name');
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->index(['industry_template_id']);
        });

        Schema::create('industry_template_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('industry_template_id')->constrained()->onDelete('cascade');
            $table->string('setting_key');
            $table->text('setting_value')->nullable();
            $table->timestamps();

            $table->index(['industry_template_id']);
        });

        Schema::create('reporting_read_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('report_name');
            $table->json('data');
            $table->timestamp('refreshed_at');
            $table->timestamps();

            $table->index(['tenant_id', 'report_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporting_read_models');
        Schema::dropIfExists('industry_template_settings');
        Schema::dropIfExists('industry_template_modules');
        Schema::dropIfExists('industry_templates');
        Schema::dropIfExists('number_sequences');
        Schema::dropIfExists('custom_field_values');
        Schema::dropIfExists('custom_fields');
        Schema::dropIfExists('inventory_cost_layers');
        Schema::dropIfExists('stock_valuation_layers');
        Schema::dropIfExists('stock_transfer_lines');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('stock_count_lines');
        Schema::dropIfExists('stock_counts');
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('stock_serial_numbers');
        Schema::dropIfExists('stock_batches');
        Schema::dropIfExists('tenant_licences');
        Schema::dropIfExists('subscription_features');
        Schema::dropIfExists('subscription_plans');
        Schema::dropIfExists('subscriptions');
    }
};
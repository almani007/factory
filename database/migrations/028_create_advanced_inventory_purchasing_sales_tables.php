<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Purchasing RFQs
        Schema::create('rfqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->string('rfq_number');
            $table->string('title');
            $table->string('status')->default('draft'); // draft, sent, submitted, awarded, rejected, closed
            $table->date('request_date');
            $table->date('response_due_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('rfq_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('rfq_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_price', 15, 4)->nullable();
            $table->decimal('total_price', 15, 4)->default(0);
            $table->timestamps();
        });

        // Supplier Contracts
        Schema::create('supplier_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->string('contract_number');
            $table->string('title');
            $table->string('contract_type');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('draft'); // draft, active, expired, renewed, terminated
            $table->decimal('total_value', 15, 2)->default(0);
            $table->text('terms')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });

        // Sales Quotes (CPQ)
        Schema::create('cpq_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('quote_number');
            $table->string('status')->default('draft'); // draft, sent, accepted, rejected, expired
            $table->string('approval_status')->default('not_required'); // not_required, pending, approved, rejected
            $table->date('quote_date');
            $table->date('valid_until');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('cpq_quote_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('cpq_quote_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_price', 15, 4);
            $table->decimal('total_price', 15, 4)->default(0);
            $table->timestamps();
        });

        // Sales Commissions
        Schema::create('sales_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('commission_period');
            $table->string('commission_type'); // flat_rate, percentage, tiered
            $table->decimal('base_amount', 15, 2)->default(0);
            $table->decimal('commission_rate', 6, 2)->default(0);
            $table->decimal('commission_amount', 15, 2)->default(0);
            $table->string('status')->default('pending'); // pending, approved, paid, disputed, cancelled
            $table->date('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });

        // Inventory Stock Transfers
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('to_warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->string('transfer_number');
            $table->string('status')->default('pending'); // pending, in_transit, completed, cancelled
            $table->date('transfer_date');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('stock_transfer_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('stock_transfer_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->decimal('quantity', 15, 4);
            $table->decimal('received_quantity', 15, 4)->default(0);
            $table->timestamps();
        });

        // Inventory Stock Batches
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->string('batch_number');
            $table->decimal('quantity', 15, 4);
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('active'); // active, expired, consumed, quarantined
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'batch_number']);
            $table->index(['item_id']);
        });

        // Inventory Stock Serials
        Schema::create('stock_serials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->string('serial_number')->unique();
            $table->string('status')->default('in_stock'); // in_stock, sold, in_repair, scrapped
            $table->date('warranty_expiry')->nullable();
            $table->string('current_location')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'serial_number']);
        });

        // Inventory Cross Docking
        Schema::create('stock_transfer_lines', function (Blueprint $table) {
            // Already created above
        });

        Schema::create('cross_docking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->string('reference');
            $table->decimal('quantity', 15, 4);
            $table->date('received_date');
            $table->date('ship_date');
            $table->string('status')->default('received'); // received, in_transit, shipped, completed, cancelled
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });

        // Inventory Kitting
        Schema::create('kitting', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->string('kit_number');
            $table->decimal('quantity', 15, 4);
            $table->string('status')->default('draft'); // draft, picking, assembled, quality_check, completed, cancelled
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('kit_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('kit_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->decimal('quantity', 15, 4);
            $table->timestamps();
        });

        // Inventory Consignment
        Schema::create('consignment_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->string('reference');
            $table->decimal('quantity_on_hand', 15, 4)->default(0);
            $table->decimal('quantity_consumed', 15, 4)->default(0);
            $table->string('status')->default('active'); // active, pending_reconciliation, reconciled, closed
            $table->date('last_reconciled')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kit_lines');
        Schema::dropIfExists('kitting');
        Schema::dropIfExists('cross_docking');
        Schema::dropIfExists('stock_transfer_lines');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('stock_serials');
        Schema::dropIfExists('stock_batches');
        Schema::dropIfExists('supplier_contracts');
        Schema::dropIfExists('cpq_quote_lines');
        Schema::dropIfExists('cpq_quotes');
        Schema::dropIfExists('rfq_lines');
        Schema::dropIfExists('rfqs');
        Schema::dropIfExists('consignment_inventory');
    }
};

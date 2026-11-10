<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('movement_type');
            $table->string('reference');
            $table->string('source_document_type')->nullable();
            $table->foreignId('source_document_id')->nullable();
            $table->string('source_document_line')->nullable();
            $table->timestamp('transaction_date');
            $table->timestamp('posting_date');
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->decimal('total_cost', 15, 4)->default(0);
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->text('reason')->nullable();
            $table->foreignId('reversal_reference_id')->nullable()->constrained('stock_movements')->onDelete('set null');
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'factory_id', 'warehouse_id']);
            $table->index(['tenant_id', 'movement_type']);
            $table->index(['tenant_id', 'transaction_date']);
            $table->index(['tenant_id', 'reference']);
        });

        Schema::create('stock_movement_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movement_id')->constrained('stock_movements')->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_variant_id')->nullable()->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->unsignedBigInteger('serial_number_id')->nullable();
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('bin_id')->nullable()->constrained('warehouse_bins')->onDelete('set null');
            $table->decimal('quantity', 12, 4);
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->decimal('total_cost', 15, 4)->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'movement_id']);
            $table->index(['tenant_id', 'item_id']);
        });

        Schema::create('stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_variant_id')->nullable()->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->decimal('on_hand', 12, 4)->default(0);
            $table->decimal('reserved', 12, 4)->default(0);
            $table->decimal('in_transit', 12, 4)->default(0);
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamp('last_movement_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'warehouse_id', 'item_id', 'item_variant_id', 'batch_id']);
            $table->index(['tenant_id', 'factory_id', 'warehouse_id', 'item_id']);
        });

        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_variant_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('reservation_type');
            $table->foreignId('reference_document_type')->nullable();
            $table->foreignId('reference_document_id')->nullable();
            $table->decimal('quantity', 12, 4);
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamp('reserved_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'warehouse_id', 'item_id']);
            $table->index(['tenant_id', 'reference_document_type', 'reference_document_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_reservations');
        Schema::dropIfExists('stock_balances');
        Schema::dropIfExists('stock_movement_lines');
        Schema::dropIfExists('stock_movements');
    }
};
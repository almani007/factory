<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('sales_order_id')->constrained()->onDelete('cascade');
            $table->string('delivery_number')->unique();
            $table->date('delivery_date');
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('delivery_order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 12, 4);
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index(['tenant_id', 'delivery_order_id']);
        });

        Schema::create('dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('sales_order_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('dispatch_number')->unique();
            $table->date('dispatch_date');
            $table->json('vehicle_details')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('dispatch_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispatch_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 12, 4);
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index(['tenant_id', 'dispatch_id']);
        });

        Schema::create('delivery_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('dispatch_id')->constrained()->onDelete('cascade');
            $table->string('note_number')->unique();
            $table->date('note_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'dispatch_id']);
        });

        Schema::create('customer_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('return_number')->unique();
            $table->date('return_date');
            $table->string('status')->default('pending');
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('customer_return_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_return_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 12, 4);
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index(['tenant_id', 'customer_return_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_return_lines');
        Schema::dropIfExists('customer_returns');
        Schema::dropIfExists('delivery_notes');
        Schema::dropIfExists('dispatch_lines');
        Schema::dropIfExists('dispatches');
        Schema::dropIfExists('delivery_order_lines');
        Schema::dropIfExists('delivery_orders');
    }
};
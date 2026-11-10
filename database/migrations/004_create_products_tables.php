<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->unique();
            $table->string('symbol')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('unit_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_unit_id')->constrained('units')->onDelete('cascade');
            $table->foreignId('to_unit_id')->constrained('units')->onDelete('cascade');
            $table->decimal('conversion_factor', 12, 6);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['from_unit_id', 'to_unit_id']);
        });

        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('sku')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('brand')->nullable();
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('barcode')->nullable();
            $table->string('image')->nullable();
            $table->json('specifications')->nullable();
            $table->decimal('min_stock', 12, 4)->default(0);
            $table->decimal('reorder_level', 12, 4)->default(0);
            $table->foreignId('preferred_supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->decimal('default_purchase_price', 15, 4)->default(0);
            $table->decimal('standard_production_cost', 15, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('track_batch')->default(false);
            $table->boolean('track_serial')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'factory_id']);
            $table->index(['tenant_id', 'sku']);
            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('item_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->string('variant_sku')->unique();
            $table->string('name');
            $table->json('attributes')->nullable();
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->decimal('min_stock', 12, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'item_id']);
        });

        Schema::create('product_barcodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('item_variant_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('barcode')->unique();
            $table->string('type')->default('item');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'barcode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_barcodes');
        Schema::dropIfExists('item_variants');
        Schema::dropIfExists('items');
        Schema::dropIfExists('unit_conversions');
        Schema::dropIfExists('units');
        Schema::dropIfExists('item_categories');
    }
};
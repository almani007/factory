<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_headers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'factory_id', 'item_id']);
            $table->index(['tenant_id', 'is_default']);
        });

        Schema::create('bom_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_header_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('version');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(false);
            $table->decimal('standard_material_cost', 15, 4)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'bom_header_id', 'is_active']);
            $table->unique(['bom_header_id', 'version']);
        });

        Schema::create('bom_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_version_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 12, 4);
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->decimal('expected_wastage_percentage', 6, 2)->default(0);
            $table->boolean('is_alternative')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'bom_version_id']);
        });

        Schema::create('bom_alternatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_component_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('alternative_item_id')->constrained('items')->onDelete('cascade');
            $table->decimal('quantity', 12, 4);
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index(['tenant_id', 'bom_component_id']);
        });

        Schema::create('bom_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_version_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->integer('sequence');
            $table->string('operation_name');
            $table->foreignId('work_centre_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('labour_required_minutes')->default(0);
            $table->integer('machine_required_minutes')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'bom_version_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_operations');
        Schema::dropIfExists('bom_alternatives');
        Schema::dropIfExists('bom_components');
        Schema::dropIfExists('bom_versions');
        Schema::dropIfExists('bom_headers');
    }
};
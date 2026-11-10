<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('inspection_type'); // incoming, in_process, final
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'inspection_type']);
        });

        Schema::create('inspection_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_template_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('check_name');
            $table->string('check_type'); // measurement, pass_fail, checklist
            $table->decimal('min_value', 12, 4)->nullable();
            $table->decimal('max_value', 12, 4)->nullable();
            $table->decimal('tolerance', 12, 4)->nullable();
            $table->boolean('is_required')->default(true);
            $table->integer('sequence')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'inspection_template_id']);
        });

        Schema::create('quality_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('inspection_template_id')->constrained()->onDelete('cascade');
            $table->string('inspection_type');
            $table->string('reference_type');
            $table->unsignedBigInteger('reference_id');
            $table->string('status')->default('pending');
            $table->foreignId('inspected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('inspected_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'reference_type', 'reference_id']);
        });

        Schema::create('quality_inspection_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quality_inspection_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('template_item_id')->constrained()->onDelete('cascade');
            $table->decimal('measured_value', 12, 4)->nullable();
            $table->string('result'); // pass, fail
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'quality_inspection_id']);
        });

        Schema::create('quality_defects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('quality_inspection_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('defect_category');
            $table->string('defect_code');
            $table->text('description');
            $table->decimal('quantity', 12, 4);
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index(['tenant_id', 'defect_category']);
        });

        Schema::create('quality_nonconformances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('nc_number')->unique();
            $table->foreignId('quality_inspection_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('status')->default('open');
            $table->text('description');
            $table->text('root_cause')->nullable();
            $table->text('corrective_action')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('quality_rework_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('quality_nonconformance_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('rework_number')->unique();
            $table->string('status')->default('pending');
            $table->text('instructions');
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('quality_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('reference_type');
            $table->unsignedBigInteger('reference_id');
            $table->foreignId('approved_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('approved_at');
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'reference_type', 'reference_id']);
            $table->index(['tenant_id', 'approved_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_approvals');
        Schema::dropIfExists('quality_rework_orders');
        Schema::dropIfExists('quality_nonconformances');
        Schema::dropIfExists('quality_defects');
        Schema::dropIfExists('quality_inspection_results');
        Schema::dropIfExists('quality_inspections');
        Schema::dropIfExists('inspection_template_items');
        Schema::dropIfExists('inspection_templates');
    }
};
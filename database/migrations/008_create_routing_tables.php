<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routings', function (Blueprint $table) {
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
        });

        Schema::create('routing_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routing_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('version');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'routing_id', 'is_active']);
            $table->unique(['routing_id', 'version']);
        });

        Schema::create('routing_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routing_version_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->integer('sequence');
            $table->string('operation_name');
            $table->foreignId('work_centre_id')->nullable()->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('machine_id')->nullable();
            $table->integer('setup_time_minutes')->default(0);
            $table->decimal('run_time_per_unit_minutes', 10, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'routing_version_id']);
        });

        Schema::create('operation_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routing_operation_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('parameter_name');
            $table->string('parameter_value');
            $table->string('unit')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'routing_operation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operation_parameters');
        Schema::dropIfExists('routing_operations');
        Schema::dropIfExists('routing_versions');
        Schema::dropIfExists('routings');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_control_charts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('factory_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->string('chart_name');
            $table->string('chart_type'); // xbar_r, p_chart, c_chart, u_chart
            $table->decimal('upper_control_limit', 15, 4)->nullable();
            $table->decimal('lower_control_limit', 15, 4)->nullable();
            $table->decimal('target_value', 15, 4)->nullable();
            $table->string('status')->default('active'); // active, out_of_control, warning, inactive
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('quality_control_chart_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('control_chart_id')->constrained()->onDelete('cascade');
            $table->date('measured_at');
            $table->decimal('value', 15, 4);
            $table->string('sample_id')->nullable();
            $table->timestamps();
            $table->index(['control_chart_id', 'measured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_control_chart_points');
        Schema::dropIfExists('quality_control_charts');
    }
};

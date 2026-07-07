<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->decimal('battery_capacity_kwh', 6, 2);
            $table->decimal('usable_battery_kwh', 6, 2)->nullable();
            $table->decimal('current_soc_percent', 5, 2)->default(100);
            $table->decimal('efficiency_wh_per_km', 6, 2);
            $table->string('connector_type');
            $table->decimal('max_charge_rate_kw', 6, 2)->nullable();
            $table->boolean('is_default')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};

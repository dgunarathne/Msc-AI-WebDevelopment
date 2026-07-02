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
       Schema::create('ev_chargers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('station_name');
    $table->text('location');
    $table->string('charger_type');
    $table->string('power_type');
    $table->string('connector_type');
$table->boolean('availability')->default(0);
    $table->decimal('power_output', 8, 2);
    $table->decimal('price_per_kwh', 8, 2);
    $table->string('promo_code')->nullable();
    $table->string('active_from')->nullable();
    $table->string('active_until')->nullable();
    $table->string('latitude');
    $table->string('longitude');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ev_chargers');
    }
};

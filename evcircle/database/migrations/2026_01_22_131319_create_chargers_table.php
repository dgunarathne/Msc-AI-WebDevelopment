<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chargers', function (Blueprint $table) {
            $table->id();
            $table->string('charger_id')->unique();
            $table->enum('owner_type', ['COMMERCIAL', 'PRIVATE']);
            $table->string('station_name');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('address');
            $table->enum('charger_type', ['AC', 'DC']);
            $table->integer('power_kw');
            $table->string('connector_type');
            $table->integer('number_of_connectors');
            $table->decimal('price_per_kwh', 8, 2);
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chargers');
    }
};

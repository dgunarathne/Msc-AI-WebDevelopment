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
       Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
           $table->string('charger_id');
            $table->string('seller_id');
            $table->string('cancel_note')->nullable();
            $table->string('amount')->nullable();
            $table->time('from_time');
            $table->date('date');
            $table->time('to_time');
            $table->enum('status', ['pending', 'confirmed', 'cancelled','charged','charging_started'])->default('pending');
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

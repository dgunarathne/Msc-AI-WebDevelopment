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
        // charger_id intentionally has no FK constraint, matching the existing
        // loose-coupling convention used by bookings.charger_id (references
        // ev_chargers.id without Schema::constrained()).
        Schema::create('charging_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('set null');
            $table->unsignedBigInteger('charger_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('queue_wait_seconds')->nullable();
            $table->timestamp('session_start_at')->nullable();
            $table->timestamp('session_end_at')->nullable();
            $table->decimal('energy_delivered_kwh', 8, 3)->nullable();
            $table->decimal('soc_start_percent', 5, 2)->nullable();
            $table->decimal('soc_end_percent', 5, 2)->nullable();
            $table->enum('status', ['queued', 'charging', 'completed', 'aborted'])->default('queued');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charging_sessions');
    }
};

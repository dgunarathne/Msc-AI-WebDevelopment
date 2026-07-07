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
        // Write-through cache of live queue state per charger, kept fresh by
        // QueueStateService on each wait-time/recommendation request. charger_id
        // references ev_chargers.id (no FK constraint, see charging_sessions).
        Schema::create('queue_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('charger_id')->unique();
            $table->unsignedInteger('current_queue_length')->default(0);
            $table->unsignedInteger('active_sessions')->default(0);
            $table->unsignedInteger('number_of_connectors')->default(1);
            $table->decimal('avg_session_minutes', 6, 2)->nullable();
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_records');
    }
};

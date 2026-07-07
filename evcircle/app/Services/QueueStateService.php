<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\EvCharger;
use App\Models\QueueRecord;
use Carbon\Carbon;

class QueueStateService
{
    /**
     * ev_chargers has no number_of_connectors column (unlike the newer
     * chargers/TKTEV table), so we assume a single connector per station
     * until that data is captured — see plan notes on the two charger tables.
     */
    private const DEFAULT_NUMBER_OF_CONNECTORS = 1;
    private const DEFAULT_AVG_SESSION_MINUTES = 45.0;

    private const ACTIVE_STATUSES = ['pending', 'confirmed', 'charging_started'];

    /**
     * Recomputes live queue state for a charger from today's bookings and
     * upserts it into queue_records, returning the payload the ML service
     * expects for wait-time/recommendation requests.
     */
    public function buildQueuePayload(EvCharger $charger): array
    {
        $today = Carbon::today()->toDateString();

        $activeBookings = Booking::where('charger_id', $charger->id)
            ->where('date', $today)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->get();

        $activeSessions = $activeBookings->where('status', 'charging_started')->count();
        $currentQueueLength = $activeBookings->count() - $activeSessions;

        $numberOfConnectors = self::DEFAULT_NUMBER_OF_CONNECTORS;

        $payload = [
            'charger_id' => $charger->id,
            'connector_type' => $charger->connector_type,
            'number_of_connectors' => $numberOfConnectors,
            'current_queue_length' => max(0, $currentQueueLength),
            'active_sessions' => $activeSessions,
            'avg_session_minutes' => self::DEFAULT_AVG_SESSION_MINUTES,
            'hour_of_day' => (int) now()->format('G'),
            'day_of_week' => (int) now()->dayOfWeekIso - 1,
            'is_dc_fast' => (float) $charger->power_output >= 22,
        ];

        QueueRecord::updateOrCreate(
            ['charger_id' => $charger->id],
            [
                'current_queue_length' => $payload['current_queue_length'],
                'active_sessions' => $payload['active_sessions'],
                'number_of_connectors' => $numberOfConnectors,
                'avg_session_minutes' => self::DEFAULT_AVG_SESSION_MINUTES,
                'last_updated_at' => now(),
            ]
        );

        return $payload;
    }
}

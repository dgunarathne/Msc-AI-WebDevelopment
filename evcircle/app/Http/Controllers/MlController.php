<?php

namespace App\Http\Controllers;

use App\Models\EvCharger;
use App\Models\Vehicle;
use App\Services\MlServiceClient;
use App\Services\QueueStateService;
use Illuminate\Http\Request;

class MlController extends Controller
{
    public function __construct(
        private MlServiceClient $mlServiceClient,
        private QueueStateService $queueStateService,
    ) {
    }

    /**
     * Feature 1: real-time waiting-time / queue forecasting for a charger.
     */
    public function waitTime(Request $request)
    {
        $request->validate([
            'charger_id' => 'required|exists:ev_chargers,id',
        ]);

        $charger = EvCharger::findOrFail($request->charger_id);
        $payload = $this->queueStateService->buildQueuePayload($charger);

        $result = $this->mlServiceClient->predictWaitTime($payload);

        if ($result === null) {
            return response()->json([
                'message' => 'Wait-time prediction unavailable, showing raw queue length instead.',
                'data' => [
                    'charger_id' => $charger->id,
                    'predicted_wait_minutes' => null,
                    'current_queue_length' => $payload['current_queue_length'],
                ],
            ], 200);
        }

        return response()->json([
            'message' => 'Wait time predicted successfully.',
            'data' => $result,
        ]);
    }

    /**
     * Feature 3: EV range / consumption prediction.
     */
    public function rangePrediction(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'battery_capacity_kwh' => 'required_without:vehicle_id|numeric|min:1',
            'current_soc_percent' => 'required_without:vehicle_id|numeric|min:0|max:100',
            'efficiency_wh_per_km' => 'required_without:vehicle_id|numeric|min:1',
            'trip_distance_km' => 'required|numeric|min:0',
            'avg_speed_kmh' => 'nullable|numeric|min:1',
            'temperature_c' => 'nullable|numeric',
            'elevation_gain_m' => 'nullable|numeric',
            'driving_style' => 'nullable|in:eco,normal,aggressive',
        ]);

        if (!empty($data['vehicle_id'])) {
            $vehicle = Vehicle::where('user_id', $request->user()->id)->findOrFail($data['vehicle_id']);
            $data['battery_capacity_kwh'] = $data['battery_capacity_kwh'] ?? $vehicle->battery_capacity_kwh;
            $data['current_soc_percent'] = $data['current_soc_percent'] ?? $vehicle->current_soc_percent;
            $data['efficiency_wh_per_km'] = $data['efficiency_wh_per_km'] ?? $vehicle->efficiency_wh_per_km;
        }
        unset($data['vehicle_id']);

        $result = $this->mlServiceClient->predictRange($data);

        if ($result === null) {
            return response()->json([
                'message' => 'Range prediction service unavailable.',
                'data' => null,
            ], 200);
        }

        return response()->json([
            'message' => 'Range predicted successfully.',
            'data' => $result,
        ]);
    }

    /**
     * Feature 2: context-aware station recommendation. Reuses the same
     * nearby-charger geo-filter as UserController::get_chargers, then
     * re-ranks the candidates via the ML service's weighted scoring.
     */
    public function recommendStations(Request $request)
    {
        $data = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'connector_type' => 'nullable|string',
            'battery_capacity_kwh' => 'nullable|numeric',
            'current_soc_percent' => 'nullable|numeric',
            'efficiency_wh_per_km' => 'nullable|numeric',
            'max_price_per_kwh' => 'nullable|numeric',
            'prefer_fast_charging' => 'nullable|boolean',
            'max_distance_km' => 'nullable|numeric',
        ]);

        $user = $request->user();

        $candidates = EvCharger::nearbyQuery($user->id, null, (float) $data['latitude'], (float) $data['longitude'])
            ->limit(20)
            ->get();

        $candidateStations = $candidates->map(function (EvCharger $charger) {
            $queue = $this->queueStateService->buildQueuePayload($charger);

            return [
                'charger_id' => $charger->id,
                'latitude' => (float) $charger->latitude,
                'longitude' => (float) $charger->longitude,
                'connector_type' => $charger->connector_type,
                'price_per_kwh' => (float) $charger->price_per_kwh,
                'power_output' => (float) $charger->power_output,
                'current_queue_length' => $queue['current_queue_length'],
                'number_of_connectors' => $queue['number_of_connectors'],
                'active_sessions' => $queue['active_sessions'],
                'avg_session_minutes' => $queue['avg_session_minutes'],
            ];
        })->values()->all();

        $payload = [
            'latitude' => (float) $data['latitude'],
            'longitude' => (float) $data['longitude'],
            'vehicle' => [
                'connector_type' => $data['connector_type'] ?? 'Type2',
                'battery_capacity_kwh' => $data['battery_capacity_kwh'] ?? 60,
                'current_soc_percent' => $data['current_soc_percent'] ?? 60,
                'efficiency_wh_per_km' => $data['efficiency_wh_per_km'] ?? 160,
            ],
            'preferences' => [
                'max_price_per_kwh' => $data['max_price_per_kwh'] ?? null,
                'prefer_fast_charging' => $data['prefer_fast_charging'] ?? false,
                'max_distance_km' => $data['max_distance_km'] ?? 25,
            ],
            'candidate_stations' => $candidateStations,
        ];

        $result = $this->mlServiceClient->recommendStations($payload);

        if ($result === null) {
            return response()->json([
                'message' => 'Recommendation service unavailable, showing unranked nearby chargers.',
                'data' => ['recommendations' => $candidateStations],
            ], 200);
        }

        return response()->json([
            'message' => 'Stations recommended successfully.',
            'data' => $result,
        ]);
    }

    /**
     * Feature 4: energy-efficient route planning with charging-stop insertion.
     */
    public function routePlan(Request $request)
    {
        $data = $request->validate([
            'origin_latitude' => 'required|numeric',
            'origin_longitude' => 'required|numeric',
            'destination_latitude' => 'required|numeric',
            'destination_longitude' => 'required|numeric',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'connector_type' => 'nullable|string',
            'battery_capacity_kwh' => 'nullable|numeric',
            'current_soc_percent' => 'nullable|numeric',
            'efficiency_wh_per_km' => 'nullable|numeric',
        ]);

        $vehiclePayload = [
            'connector_type' => $data['connector_type'] ?? 'Type2',
            'battery_capacity_kwh' => $data['battery_capacity_kwh'] ?? 60,
            'current_soc_percent' => $data['current_soc_percent'] ?? 80,
            'efficiency_wh_per_km' => $data['efficiency_wh_per_km'] ?? 160,
        ];

        if (!empty($data['vehicle_id'])) {
            $vehicle = Vehicle::where('user_id', $request->user()->id)->findOrFail($data['vehicle_id']);
            $vehiclePayload = [
                'connector_type' => $vehicle->connector_type,
                'battery_capacity_kwh' => (float) $vehicle->battery_capacity_kwh,
                'current_soc_percent' => (float) $vehicle->current_soc_percent,
                'efficiency_wh_per_km' => (float) $vehicle->efficiency_wh_per_km,
            ];
        }

        // MVP: candidate stations for a route are drawn from all registered
        // chargers (Sri Lanka-scale network is small); revisit with a
        // bounding-box/corridor filter if the charger network grows.
        $user = $request->user();
        $candidates = EvCharger::where('user_id', '!=', $user->id)->limit(100)->get();

        $candidateStations = $candidates->map(function (EvCharger $charger) {
            $queue = $this->queueStateService->buildQueuePayload($charger);

            return [
                'charger_id' => $charger->id,
                'latitude' => (float) $charger->latitude,
                'longitude' => (float) $charger->longitude,
                'connector_type' => $charger->connector_type,
                'price_per_kwh' => (float) $charger->price_per_kwh,
                'power_output' => (float) $charger->power_output,
                'current_queue_length' => $queue['current_queue_length'],
                'number_of_connectors' => $queue['number_of_connectors'],
                'active_sessions' => $queue['active_sessions'],
                'avg_session_minutes' => $queue['avg_session_minutes'],
            ];
        })->values()->all();

        $payload = [
            'origin' => ['latitude' => (float) $data['origin_latitude'], 'longitude' => (float) $data['origin_longitude']],
            'destination' => ['latitude' => (float) $data['destination_latitude'], 'longitude' => (float) $data['destination_longitude']],
            'vehicle' => $vehiclePayload,
            'candidate_stations' => $candidateStations,
        ];

        $result = $this->mlServiceClient->planRoute($payload);

        if ($result === null) {
            return response()->json([
                'message' => 'Route planning service unavailable.',
                'data' => null,
            ], 200);
        }

        return response()->json([
            'message' => 'Route planned successfully.',
            'data' => $result,
        ]);
    }
}

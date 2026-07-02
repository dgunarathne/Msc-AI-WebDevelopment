<?php

namespace App\Http\Controllers;

use App\Models\Charger;
use App\Services\TktevService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChargerController extends Controller
{
    /**
     * Display a listing of all chargers.
     */
    public function index()
    {
        return response()->json(Charger::all(), 200);
    }

   public function store(Request $request, TktevService $tktev)
{
    $validated = $request->validate([
        'charger_id'           => 'required|unique:chargers,charger_id',
        'owner_type'           => 'required|in:COMMERCIAL,PRIVATE',
        'station_name'         => 'required|string',
        'latitude'             => 'required|numeric',
        'longitude'            => 'required|numeric',
        'address'              => 'required|string',
        'charger_type'         => 'required|in:AC,DC',
        'power_kw'             => 'required|integer',
        'connector_type'       => 'required|string',
        'number_of_connectors' => 'required|integer|min:1',
        'price_per_kwh'        => 'required|numeric',
        'is_public'            => 'sometimes|boolean',

        // BEST PRACTICE: send real serial/device number separately
        // 'device_no'          => 'required|string',
    ]);

    // 1) Save locally first
    $charger = Charger::create($validated);

    try {
        // 2) Ensure TKTEV config exists (otherwise TKTEV will 500)
        $modelKey     = config('services.tktev.model_key');
        $productKey   = config('services.tktev.product_key');
        $modelType    = config('services.tktev.model_type');
        $protocolType = config('services.tktev.protocol_type');
        $deviceType   = config('services.tktev.device_type');
        $signalMode   = config('services.tktev.signal_mode');

        if (!$modelKey || !$productKey || $modelType === null || $protocolType === null || $deviceType === null || $signalMode === null) {
            return response()->json([
                'message' => 'Saved locally, but TKTEV registration is not configured. Missing MODEL/PRODUCT/ENUM values.',
                'need' => [
                    'TKTEV_MODEL_KEY', 'TKTEV_PRODUCT_KEY',
                    'TKTEV_MODEL_TYPE', 'TKTEV_PROTOCOL_TYPE',
                    'TKTEV_DEVICE_TYPE', 'TKTEV_SIGNAL_MODE'
                ],
                'charger' => $charger,
            ], 422);
        }

        // 3) Login TKTEV
        $token = $tktev->login();

        // IMPORTANT: deviceNo must be the REAL device serial number TKTEV accepts
        // If charger_id is not the real serial, add device_no column and use it.
        $deviceNo = (string) $charger->charger_id; // better: $charger->device_no

        // 4) Pre-save payload (minimal correct fields)
        $preSavePayload = [
            'deviceNo'     => $deviceNo,
            'gunNum'       => (int) $charger->number_of_connectors,

            'modelKey'     => (string) $modelKey,
            'productKey'   => (string) $productKey,

            'modelType'    => (int) $modelType,
            'protocolType' => (int) $protocolType,
            'deviceType'   => (int) $deviceType,
            'signalMode'   => (int) $signalMode,
        ];

        $preSaveRes = $tktev->manualAddPreSave($preSavePayload, $token);

        if (!$preSaveRes->successful()) {
            Log::error('TKTEV pre-save failed', [
                'status' => $preSaveRes->status(),
                'body'   => $preSaveRes->body(),
                'sent'   => $preSavePayload,
            ]);

            return response()->json([
                'message' => 'Saved locally but TKTEV pre-save failed',
                'tktev'   => $preSaveRes->json(),
                'charger' => $charger,
            ], 502);
        }

        $preSaveKey = data_get($preSaveRes->json(), 'result.preSaveKey')
            ?? data_get($preSaveRes->json(), 'preSaveKey');

        if (!$preSaveKey) {
            Log::error('TKTEV preSaveKey missing', ['json' => $preSaveRes->json()]);

            return response()->json([
                'message' => 'Saved locally but TKTEV did not return preSaveKey',
                'tktev'   => $preSaveRes->json(),
                'charger' => $charger,
            ], 502);
        }

        // 5) Commit
        $commitRes = $tktev->manualAddCommit((string) $preSaveKey, $token);

        if (!$commitRes->successful()) {
            Log::error('TKTEV commit failed', [
                'status' => $commitRes->status(),
                'body'   => $commitRes->body(),
            ]);

            return response()->json([
                'message' => 'Saved locally but TKTEV commit failed',
                'tktev'   => $commitRes->json(),
                'charger' => $charger,
            ], 502);
        }

        $commitJson = $commitRes->json();

        $charger->update([
            'vendor_charger_id' => data_get($commitJson, 'result.id')
                ?? data_get($commitJson, 'result.deviceNo')
                ?? null,
        ]);

        return response()->json($charger->fresh(), 201);

    } catch (\Throwable $e) {
        Log::error('TKTEV integration error', ['error' => $e->getMessage()]);

        return response()->json([
            'message' => 'Saved locally but TKTEV integration crashed',
            'error'   => $e->getMessage(),
            'charger' => $charger,
        ], 500);
    }
}


    /**
     * Display the specified charger.
     */
    public function show($id)
    {
        $charger = Charger::findOrFail($id);

        return response()->json($charger, 200);
    }

    /**
     * Update the specified charger in storage.
     */
    public function update(Request $request, $id)
    {
        $charger = Charger::findOrFail($id);

        $validated = $request->validate([
            'owner_type' => 'sometimes|in:COMMERCIAL,PRIVATE',
            'station_name' => 'sometimes|string',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'address' => 'sometimes|string',
            'charger_type' => 'sometimes|in:AC,DC',
            'power_kw' => 'sometimes|integer',
            'connector_type' => 'sometimes|string',
            'number_of_connectors' => 'sometimes|integer',
            'price_per_kwh' => 'sometimes|numeric',
            'is_public' => 'sometimes|boolean',
        ]);

        $charger->update($validated);

        return response()->json($charger, 200);
    }

    /**
     * Remove the specified charger from storage.
     */
    public function destroy($id)
    {
        Charger::destroy($id);

        return response()->json(null, 204);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $vehicles = Vehicle::where('user_id', $request->user()->id)->get();

        return response()->json([
            'message' => 'Vehicles fetched successfully.',
            'vehicles' => $vehicles,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'make' => 'nullable|string',
            'model' => 'nullable|string',
            'battery_capacity_kwh' => 'required|numeric|min:1',
            'usable_battery_kwh' => 'nullable|numeric|min:0',
            'current_soc_percent' => 'nullable|numeric|min:0|max:100',
            'efficiency_wh_per_km' => 'required|numeric|min:1',
            'connector_type' => 'required|string',
            'max_charge_rate_kw' => 'nullable|numeric|min:0',
            'is_default' => 'nullable|boolean',
        ]);

        $data['user_id'] = $request->user()->id;

        $vehicle = Vehicle::create($data);

        return response()->json([
            'message' => 'Vehicle added successfully.',
            'vehicle' => $vehicle,
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $vehicle = Vehicle::where('user_id', $request->user()->id)->findOrFail($id);

        $data = $request->validate([
            'make' => 'nullable|string',
            'model' => 'nullable|string',
            'battery_capacity_kwh' => 'sometimes|numeric|min:1',
            'usable_battery_kwh' => 'nullable|numeric|min:0',
            'current_soc_percent' => 'sometimes|numeric|min:0|max:100',
            'efficiency_wh_per_km' => 'sometimes|numeric|min:1',
            'connector_type' => 'sometimes|string',
            'max_charge_rate_kw' => 'nullable|numeric|min:0',
            'is_default' => 'nullable|boolean',
        ]);

        $vehicle->update($data);

        return response()->json([
            'message' => 'Vehicle updated successfully.',
            'vehicle' => $vehicle,
        ]);
    }
}

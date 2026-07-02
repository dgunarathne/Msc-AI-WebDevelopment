<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeviceToken; 

class DeviceTokenController extends Controller
{
    // Save or update device token for the authenticated user
    public function store(Request $request)
    {
        $user = $request->user();
        // Validate the incoming request
        $request->validate([
            'device_token' => 'required',
            'platform' => 'required|string|in:ios,android',
        ]);

        // Save or update the device token for this user
        DeviceToken::updateOrCreate(
            ['device_token' => $request->device_token],
            [
                'user_id' => $user->id,
                'platform' => $request->platform,
            ]
        );

        return response()->json(['message' => 'Device token saved successfully']);
    }
}

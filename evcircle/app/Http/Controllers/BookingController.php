<?php

namespace App\Http\Controllers;
use App\Models\SubscriptionPlan;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Station;
use Illuminate\Support\Facades\Http;
use App\Models\DeviceToken;
use App\Events\BookingStatusUpdated;
use Google\Auth\Credentials\ServiceAccountCredentials;
use App\Models\EvCharger;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\Review;
use App\Models\Notification;
use App\Mail\ConfirmBooking;
use App\Mail\BookingReceived;
use App\Mail\CancelBooking;

class BookingController extends Controller
{

     public function update_earnings(Request $request){
        $request->validate([
        'booking_id' => 'required|integer',
        'amount' => 'required|numeric',
    ]);

    $bookingId = $request->booking_id;
    $amount = $request->amount;

    $booking = Booking::find($bookingId);

    if (!$booking) {
        return response()->json(['message' => 'Booking not found'], 404);
    }

    $sellerId = $booking->seller_id;

    $user = User::find($sellerId);

    if (!$user) {
        return response()->json(['message' => 'Seller not found'], 404);
    }

    // increment income and count safely
    $user->income = ($user->income ?? 0) + $amount;
    $user->count = ($user->count ?? 0) + 1;
    $user->save();

    return response()->json(['message' => 'Earnings updated successfully'], 200);
     }
     public function checkAvailability(Request $request)
    {
        $request->validate([
            'charger_id' => 'required',
            'date' => 'required|date',
        ]);

        $bookedSlots = Booking::where('charger_id', $request->charger_id)
            ->where('date', $request->date)
            ->where('status','confirmed')
            ->get(['from_time', 'to_time']);

        return response()->json([
            'success' => true,
            'booked_times' => $bookedSlots,
        ]);
    }

    /**
     * Same as checkAvailability, plus an ML-predicted wait time for the
     * charger right now (feature 1: waiting-time prediction). Additive —
     * checkAvailability's existing contract is untouched for API compatibility.
     */
    public function checkAvailabilityWithWait(
        Request $request,
        \App\Services\MlServiceClient $mlServiceClient,
        \App\Services\QueueStateService $queueStateService,
    ) {
        $request->validate([
            'charger_id' => 'required|exists:ev_chargers,id',
            'date' => 'required|date',
        ]);

        $bookedSlots = Booking::where('charger_id', $request->charger_id)
            ->where('date', $request->date)
            ->where('status', 'confirmed')
            ->get(['from_time', 'to_time']);

        $charger = EvCharger::findOrFail($request->charger_id);
        $queuePayload = $queueStateService->buildQueuePayload($charger);
        $waitPrediction = $mlServiceClient->predictWaitTime($queuePayload);

        return response()->json([
            'success' => true,
            'booked_times' => $bookedSlots,
            'predicted_wait_minutes' => $waitPrediction['predicted_wait_minutes'] ?? null,
        ]);
    }

    public function book(Request $request)
{
    $request->validate([
        'charger_id' => 'required|exists:ev_chargers,id',
        'date' => 'required|date',
        'from_time' => 'required|date_format:H:i',
        'to_time' => 'required|date_format:H:i|after:from_time',
    ]);
    // Check for overlapping bookings
    $exists = Booking::where('charger_id', $request->charger_id)
        ->where('date', $request->date)
        ->where('status','confirmed')
        ->where(function ($query) use ($request) {
            $query->where('from_time', '<', $request->to_time)
                  ->where('to_time', '>', $request->from_time);
        })
        ->exists();

    if ($exists) {
        return response()->json([
            'success' => false,
            'message' => 'This time slot is already booked.',
        ], 409);
    }

    $charger = EvCharger::findOrFail($request->charger_id);
    $seller_id = $charger->user_id;
    $seller = User::find($seller_id);
    $email = $seller?->email;

    $booking = Booking::create([
        'user_id' => Auth::id(),
        'charger_id' => $request->charger_id,
        'seller_id' => $seller_id,
        'date' => $request->date,
        'from_time' => $request->from_time,
        'to_time' => $request->to_time,
    ]);
    $deviceTokenEntry = DeviceToken::where('user_id', $seller_id)->first();

    if (!$deviceTokenEntry) {
        \Log::warning('No device token found for user ID: ' . $user->id);
        return response()->json([
            'message' => 'Booking confirmed but no device token found for notification',
            'booking' => $booking,
            'notification_sent' => false
        ], 200);
    }

    $fcmToken = $deviceTokenEntry->device_token;

    // Firebase credentials and project details
    $serviceAccountPath = base_path('firebase/serviceAccountKey.json');
    $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

    $creds = new ServiceAccountCredentials($scopes, $serviceAccountPath);
    $authToken = $creds->fetchAuthToken();

    if (!isset($authToken['access_token'])) {
        \Log::error('Failed to fetch Firebase access token');
        return response()->json(['message' => 'Failed to authenticate with Firebase'], 500);
    }

    try {
        Mail::to($email)->queue(new BookingReceived());
        \Log::info('ConfirmBooking mail sent to:');
    } catch (\Exception $e) {
        \Log::error("Failed to send ConfirmBooking mail. Error: " . $e->getMessage());
    }

    $accessToken = $authToken['access_token'];
    $projectId = 'evcircle-9c7ec'; 
    Notification::create([
            'user_id' => $seller_id,
            'message' => "You've received a new booking request. Check your dashboard for details",
            'seen' => false,
            'screen' => 'Booking',
            'mode' => 'Seller'
        ]);
    $data = [
        "message" => [
            "token" => $fcmToken,
            "notification" => [
                "title" => "EVCircle",
                "body" => "You've received a new booking request. Check your dashboard for details."             
            ],
            "data" => [
                "screen" => "BookingDetails",
                "booking_id" => (string) $booking->id,

            ]
        ]
    ];

    // Send notification via FCM HTTP v1 endpoint
    $response = Http::withHeaders([
        "Authorization" => "Bearer $accessToken",
        "Content-Type" => "application/json",
    ])->post("https://fcm.googleapis.com/v1/projects/$projectId/messages:send", $data);

    if (!$response->successful()) {
        \Log::error('FCM send failed: ' . $response->body());
    }
    return response()->json([
        'success' => true,
        'message' => 'Booking successful.',
        'booking' => $booking,
    ], 201);
}
   public function bookinghistory(Request $request) {
    $user = $request->user();

    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    try {
        // Eager load evcharger relationship, selecting only needed columns
        $bookings = $user->bookings()
            ->with('evcharger:id,station_name,location,charger_type,connector_type,power_output,price_per_kwh')
            ->latest()
            ->paginate(10);

        // Transform bookings to include charger station name as 'name'
        $bookings->getCollection()->transform(function ($booking) {
    $charger = $booking->evcharger;

    return [
        'id'             => $booking->id,
        'booking_id'     => $booking->id, // or use another column if you have `booking_id` field
        'date'           => $booking->date,
        'time'           => $booking->from_time . ' - ' . $booking->to_time,
        'status'         => ucfirst($booking->status),
        'station_name'   => $charger->station_name ?? 'N/A',
        'address'        => $charger->location ?? 'N/A',          // make sure this column exists
        'charger_type'   => $charger->charger_type ?? 'N/A',     // ensure this is in the evcharger table
        'connector_type' => $charger->connector_type ?? 'N/A',   // ensure column exists
        'power'          => $charger->power_output ?? 'N/A',            // e.g., "22kW"
        'price'          => $charger->price_per_kwh ?? 'N/A',            // e.g., "Rs.100 per hour"
        'seller_name'    => $user->first_name ?? 'N/A',      // you can join `seller` model if needed
        'rating'         => $charger->rating ?? '0',             // average rating
        'reviews'        => $charger->reviews_count ?? '0',      // review count or text summary
    ];
});


        return response()->json([
            'message' => 'Booking history fetched successfully',
            'data'    => $bookings,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Server error. Could not fetch bookings.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}



public function getmybookings(Request $request) {
    $user = $request->user();

    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    try {
        // Eager load evcharger relationship, selecting only needed columns
      $bookings = Booking::where('seller_id', $user->id)
    ->with('evcharger:id,station_name,location,charger_type,connector_type,power_output,price_per_kwh')
    ->latest()
    ->paginate(10);

    // Transform bookings to include charger station name as 'name'
    $bookings->getCollection()->transform(function ($booking) {
    $charger = $booking->evcharger;

    $use =$booking->user_id;
        $mobile = User::find($use)?->mobile;
        $name = User::find($use)?->first_name;
        $count = User::find($use)?->changings;

    return [
        'id'             => $booking->id,
        'booking_id'     => $booking->id, // or use another column if you have `booking_id` field
        'date'           => $booking->date,
        'time'           => $booking->from_time,$booking->to_time,
        'from'           => $booking->from_time,
        'to'           => $booking->to_time,
        'status'         => ucfirst($booking->status),
        'station_name'   => $charger->station_name ?? 'N/A',
        'address'        => $charger->location ?? 'N/A',          // make sure this column exists
        'charger_type'   => $charger->charger_type ?? 'N/A',     // ensure this is in the evcharger table
        'connector_type' => $charger->connector_type ?? 'N/A',   // ensure column exists
        'power'          => $charger->power_output ?? 'N/A',            // e.g., "22kW"
        'price'          => $charger->price_per_kwh ?? 'N/A',            // e.g., "Rs.100 per hour"
        'seller_name'    => $name ?? 'N/A',      // you can join `seller` model if needed
        'rating'         => $charger->rating ?? '0',             // average rating
        'reviews'        => $charger->reviews_count ?? '0',      // review count or text summary
        ];
    });


    return response()->json([
        'message' => 'Booking history fetched successfully',
        'data'    => $bookings,
    ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Server error. Could not fetch bookings.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}
public function getmybookingsu(Request $request) {
    $user = $request->user();

    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    try {
        // Eager load evcharger relationship, selecting only needed columns
      $bookings = Booking::where('user_id', $user->id)
    ->with(['evcharger:id,station_name,location,charger_type,connector_type,power_output,user_id,price_per_kwh,latitude,longitude','user:id,mobile'])
    ->latest()
    ->paginate(10);




        // Transform bookings to include charger station name as 'name'
    $bookings->getCollection()->transform(function ($booking) {
    $charger = $booking->evcharger;
    
        $use =$booking->evcharger->user_id;
        $mobile = User::find($use)?->mobile;
        $name = User::find($use)?->first_name;
        $count = User::find($use)?->changings;

    return [
        'id'             => $booking->id,
        'booking_id'     => $booking->id, // or use another column if you have `booking_id` field
        'date'           => $booking->date,
        'time'           => $booking->from_time,$booking->to_time,
        'from'           => $booking->from_time,
        'changings'      => $count,
        'phone'           => $mobile,
        'to'           => $booking->to_time,
        'status'         => ucfirst($booking->status),
        'station_name'   => $charger->station_name ?? 'N/A',
        'address'        => $charger->location ?? 'N/A',          // make sure this column exists
        'latitude'        => $charger->latitude ?? 'N/A',          // make sure this column exists
        'longitude'        => $charger->longitude ?? 'N/A',          // make sure this column exists
        'charger_type'   => $charger->charger_type ?? 'N/A',     // ensure this is in the evcharger table
        'connector_type' => $charger->connector_type ?? 'N/A',   // ensure column exists
        'power'          => $charger->power_output ?? 'N/A',            // e.g., "22kW"
        'price'          => $charger->price_per_kwh ?? 'N/A',            // e.g., "Rs.100 per hour"
        'seller_name'    => $name ?? 'N/A',      // you can join `seller` model if needed
        'rating'         => $charger->rating ?? '0',             // average rating
        'reviews'        => $charger->reviews_count ?? '0',      // review count or text summary
        ];
    });


    return response()->json([
        'message' => 'Booking history fetched successfully',
        'data'    => $bookings,
    ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Server error. Could not fetch bookings.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}


public function bookingsaccept(Request $request)
{
      $user = $request->user();
    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $request->validate([
        'booking_id' => 'required|exists:bookings,id',
    ]);

    // Find the booking that belongs to the current seller
    $booking = Booking::where('id', $request->booking_id)
                      ->where('seller_id', $user->id)
                      ->first();

    if (!$booking) {
        return response()->json(['message' => 'Booking not found or does not belong to you'], 404);
    }

    // Update the booking status
    $booking->status = 'Confirmed';
    $booking->save();
    $email=$booking->usere->email;
    $idd=$booking->usere->id;

 try {
        Mail::to($email)->queue(new ConfirmBooking());
        \Log::info('ConfirmBooking mail sent to:');
    } catch (\Exception $e) {
        \Log::error("Failed to send ConfirmBooking mail. Error: " . $e->getMessage());
    }
    // Fetch the device token for this seller user (assuming one token per user here)
    $deviceTokenEntry = DeviceToken::where('user_id', $booking->user_id)->first();

    if (!$deviceTokenEntry) {
        \Log::warning('No device token found for user ID: ' . $user->id);
        return response()->json([
            'message' => 'Booking confirmed but no device token found for notification',
            'booking' => $booking,
            'notification_sent' => false
        ], 200);
    }

    $fcmToken = $deviceTokenEntry->device_token;

    // Firebase credentials and project details
    $serviceAccountPath = base_path('firebase/serviceAccountKey.json');
    $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

    $creds = new ServiceAccountCredentials($scopes, $serviceAccountPath);
    $authToken = $creds->fetchAuthToken();

    if (!isset($authToken['access_token'])) {
        \Log::error('Failed to fetch Firebase access token');
        return response()->json(['message' => 'Failed to authenticate with Firebase'], 500);
    }

    $accessToken = $authToken['access_token'];
    $projectId = 'evcircle-9c7ec'; 
    Notification::create([
            'user_id' => $idd,
            'message' => "Your EV charger booking has been confirmed. You can view the full details in your bookings",
            'seen' => false,
            'screen' => 'Booking',
            'mode' => 'Home'
        ]);
    $data = [
        "message" => [
            "token" => $fcmToken,
            "notification" => [
                "title" => "EVCircle",
                "body" => "Your EV charger booking has been confirmed. You can view the full details in your bookings."

            ],
            "data" => [
                "screen" => "BookingDetails",
                "booking_id" => (string) $booking->id,
            ]
        ]
    ];

    // Send notification via FCM HTTP v1 endpoint
    $response = Http::withHeaders([
        "Authorization" => "Bearer $accessToken",
        "Content-Type" => "application/json",
    ])->post("https://fcm.googleapis.com/v1/projects/$projectId/messages:send", $data);

    if (!$response->successful()) {
        \Log::error('FCM send failed: ' . $response->body());
    }

    return response()->json([
        'message' => 'Booking confirmed successfully',
        'booking' => $booking,
        'notification_sent' => $response->successful(),
    ], 200);
}

public function bookingsacancel(Request $request){
$user = $request->user();

    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // Validate incoming request
    $request->validate([
        'booking_id' => 'required|exists:bookings,id',
    ]);

    // Find the booking that belongs to the current seller
    $booking = Booking::where('id', $request->booking_id)
                      ->where('seller_id', $user->id)
                      ->first();

    if (!$booking) {
        return response()->json(['message' => 'Booking not found or does not belong to you'], 404);
    }

    $deviceTokenEntry = DeviceToken::where('user_id', $booking->user_id)->first();

    if (!$deviceTokenEntry) {
        \Log::warning('No device token found for user ID: ' . $user->id);
        return response()->json([
            'message' => 'Booking confirmed but no device token found for notification',
            'booking' => $booking,
            'notification_sent' => false
        ], 200);
    }
        $email=$booking->usere->email;
 try {
        Mail::to($email)->queue(new CancelBooking());
        \Log::info('ConfirmBooking mail sent to:');
    } catch (\Exception $e) {
        \Log::error("Failed to send ConfirmBooking mail. Error: " . $e->getMessage());
    }
    $fcmToken = $deviceTokenEntry->device_token;

    // Firebase credentials and project details
    $serviceAccountPath = base_path('firebase/serviceAccountKey.json');
    $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

    $creds = new ServiceAccountCredentials($scopes, $serviceAccountPath);
    $authToken = $creds->fetchAuthToken();

    if (!isset($authToken['access_token'])) {
        \Log::error('Failed to fetch Firebase access token');
        return response()->json(['message' => 'Failed to authenticate with Firebase'], 500);
    }

    $accessToken = $authToken['access_token'];
    $projectId = 'evcircle-9c7ec'; 
    Notification::create([
            'user_id' => $booking->user_id,
            'message' => "Your EV charger booking has been cancelled. Please check your bookings for more details",
            'seen' => false,
            'screen' => 'Booking',
            'mode' => 'Home'
        ]);
    $data = [
        "message" => [
            "token" => $fcmToken,
            "notification" => [
                "title" => "EVCircle",
                "body" => "Your EV charger booking has been cancelled. Please check your bookings for more details",
            ],
            "data" => [
                "screen" => "BookingDetails",
                "booking_id" => (string) $booking->id,
            ]
        ]
    ];

    // Send notification via FCM HTTP v1 endpoint
    $response = Http::withHeaders([
        "Authorization" => "Bearer $accessToken",
        "Content-Type" => "application/json",
    ])->post("https://fcm.googleapis.com/v1/projects/$projectId/messages:send", $data);

    if (!$response->successful()) {
        \Log::error('FCM send failed: ' . $response->body());
    }
    $booking->status = 'cancelled';
    $booking->save();

    return response()->json([
        'message' => 'Booking confirmed successfully',
        'booking' => $booking
    ], 200);
}
public function bookingsacancelu(Request $request){
$user = $request->user();

    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // Validate incoming request
    $request->validate([
        'booking_id' => 'required|exists:bookings,id',
    ]);

    // Find the booking that belongs to the current seller
    $booking = Booking::where('id', $request->booking_id)
                      ->where('user_id', $user->id)
                      ->first();

    if (!$booking) {
        return response()->json(['message' => 'Booking not found or does not belong to you'], 404);
    }

    $deviceTokenEntry = DeviceToken::where('user_id', $booking->seller_id)->first();
        $email=$booking->usere->email;
 try {
        Mail::to($email)->queue(new CancelBooking());
        \Log::info('ConfirmBooking mail sent to:');
    } catch (\Exception $e) {
        \Log::error("Failed to send ConfirmBooking mail. Error: " . $e->getMessage());
    }
    if (!$deviceTokenEntry) {
        \Log::warning('No device token found for user ID: ' . $user->id);
        return response()->json([
            'message' => 'Booking confirmed but no device token found for notification',
            'booking' => $booking,
            'notification_sent' => false
        ], 200);
    }

    $fcmToken = $deviceTokenEntry->device_token;

    // Firebase credentials and project details
    $serviceAccountPath = base_path('firebase/serviceAccountKey.json');
    $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

    $creds = new ServiceAccountCredentials($scopes, $serviceAccountPath);
    $authToken = $creds->fetchAuthToken();

    if (!isset($authToken['access_token'])) {
        \Log::error('Failed to fetch Firebase access token');
        return response()->json(['message' => 'Failed to authenticate with Firebase'], 500);
    }

    $accessToken = $authToken['access_token'];
    $projectId = 'evcircle-9c7ec'; 
   Notification::create([
            'user_id' => $booking->seller_id,
            'message' => "Your EV charger booking has been cancelled. Please check your dashboard for more details.",
            'seen' => false,
            'screen' => 'Booking',
            'mode' => 'Home'
        ]);
    $data = [
        "message" => [
            "token" => $fcmToken,
            "notification" => [
                "title" => "EVCircle",
                "body" => "Your EV charger booking has been cancelled. Please check your dashboard for more details.",
            ],
            "data" => [
                "screen" => "BookingDetails",
                "booking_id" => (string) $booking->id,
            ]
        ]
    ];

    // Send notification via FCM HTTP v1 endpoint
    $response = Http::withHeaders([
        "Authorization" => "Bearer $accessToken",
        "Content-Type" => "application/json",
    ])->post("https://fcm.googleapis.com/v1/projects/$projectId/messages:send", $data);

    if (!$response->successful()) {
        \Log::error('FCM send failed: ' . $response->body());
    }
    $booking->status = 'cancelled';
    $booking->save();

    return response()->json([
        'message' => 'Booking confirmed successfully',
        'booking' => $booking
    ], 200);
}
public function startcharging(Request $request)
{
    $user = $request->user();

    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $request->validate([
        'booking_id' => 'required|exists:bookings,id',
    ]);

    try {
        $booking = Booking::where('id', $request->booking_id)
                          ->where('user_id', $user->id)
                          ->with('evcharger')
                          ->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking not found or access denied'], 404);
        }

        return response()->json([
            'message' => 'Charging started successfully.',
            'data' => [
                'booking_id'      => $booking->id,
           'real_time' => $booking->updated_at->format('Y-m-d H:i:s'),
                'start_time'      => $booking->from_time,
                'end_time'        => $booking->to_time,
                'price_per_hour'  => $booking->evcharger->price_per_kwh ?? 0,
                'location'        => $booking->evcharger->location ?? 'N/A',
                'customer_name'   => $booking->user->first_name . ' ' . $booking->user->last_name,
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Server Error',
            'error'   => $e->getMessage(),
        ], 500);
    }
}
public function startchargingu(Request $request)
{
    $user = $request->user();

    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $request->validate([
        'booking_id' => 'required|exists:bookings,id',
    ]);

    try {
        $booking = Booking::where('id', $request->booking_id)
                          ->where('seller_id', $user->id)
                          ->with('evcharger')
                          ->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking not found or access denied'], 404);
        }

        return response()->json([
            'message' => 'Charging started successfully.',
            'data' => [
                'booking_id'      => $booking->id,
           'real_time' => $booking->updated_at->format('Y-m-d H:i:s'),
                'start_time'      => $booking->from_time,
                'end_time'        => $booking->to_time,
                'price_per_hour'  => $booking->evcharger->price_per_kwh ?? 0,
                'location'        => $booking->evcharger->location ?? 'N/A',
                'customer_name'   => $booking->user->first_name . ' ' . $booking->user->last_name,
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Server Error',
            'error'   => $e->getMessage(),
        ], 500);
    }
}
public function sendreviews(Request $request)
{
    $request->validate([
        'bookingId' => 'required|exists:bookings,id',
        'rating' => 'required|integer|min:1|max:5',
        'feedback' => 'nullable|string|max:1000',
    ]);

    $user = Auth::user();
    $booking = Booking::where('id', $request->bookingId)->where('user_id', $user->id)->first();

    if (!$booking) {
        return response()->json([
            'message' => 'Invalid booking ID or unauthorized access.'
        ], 403);
    }

    // Prevent multiple reviews for same booking
    $existing = Review::where('booking_id', $booking->id)->first();
    if ($existing) {
        return response()->json([
            'message' => 'Feedback for this booking has already been submitted.'
        ], 409);
    }

    $review = Review::create([
        'booking_id' => $booking->id,
        'user_id' => $user->id,
        'charger_id' => $booking->charger_id,
        'rating' => $request->rating,
        'feedback' => $request->feedback,
    ]);
$deviceTokenEntry = DeviceToken::where('user_id', $booking->seller_id)->first();

    if (!$deviceTokenEntry) {
        \Log::warning('No device token found for user ID: ' . $user->id);
        return response()->json([
            'message' => 'Booking confirmed but no device token found for notification',
            'booking' => $booking,
            'notification_sent' => false
        ], 200);
    }

    $fcmToken = $deviceTokenEntry->device_token;

    // Firebase credentials and project details
    $serviceAccountPath = base_path('firebase/serviceAccountKey.json');
    $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

    $creds = new ServiceAccountCredentials($scopes, $serviceAccountPath);
    $authToken = $creds->fetchAuthToken();

    if (!isset($authToken['access_token'])) {
        \Log::error('Failed to fetch Firebase access token');
        return response()->json(['message' => 'Failed to authenticate with Firebase'], 500);
    }

    $accessToken = $authToken['access_token'];
    $projectId = 'evcircle-9c7ec'; 
   Notification::create([
            'user_id' => $booking->seller_id,
            'message' => "You've received new feedback: \"" . $request->feedback . "\"",
            'seen' => false,
            'screen' => 'Booking',
            'mode' => 'Home'
        ]);
    $data = [
        "message" => [
            "token" => $fcmToken,
            "notification" => [
                "title" => "EVCircle",
               "body" => "You've received new feedback: \"" . $request->feedback . "\"",

            ],
            "data" => [
                "screen" => "BookingDetails",
                "booking_id" => (string) $booking->id,
            ]
        ]
    ];

    // Send notification via FCM HTTP v1 endpoint
    $response = Http::withHeaders([
        "Authorization" => "Bearer $accessToken",
        "Content-Type" => "application/json",
    ])->post("https://fcm.googleapis.com/v1/projects/$projectId/messages:send", $data);

    if (!$response->successful()) {
        \Log::error('FCM send failed: ' . $response->body());
    }
    return response()->json([
        'message' => 'Feedback submitted successfully.',
        'review' => $review
    ], 201);
}
public function earnings(Request $request)
{
    // Verify the user is authenticated
    $user = Auth::user();
    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized'
        ], 401);
    }

    try {
        // Calculate total earnings from bookings
        $totalEarnings = $user->income;
        $chargings = $user->count;
        $chargerId = $user->ev_chargers()->first()?->id;



        // Count total bookings
        $totalBookings = $user->changings;
        $totalBookings = $user->changings;


        // Get recent 5 transactions
       

        return response()->json([
            'status' => 'success',
            'review_id' => $chargerId,
            'total_earnings' => number_format($totalEarnings, 2),
            'total_bookings' => $totalBookings,
            'chargings' => $chargings,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to fetch earnings data',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function subscriptionplans(Request $request){
    return SubscriptionPlan::select('id', 'name', 'duration', 'days', 'price', 'popular','start_date')->get();
}
public function startqr(Request $request)
{
    $request->validate([
        'booking_id' => 'required|integer',
        'user_id' => 'required|integer',
    ]);

     $booking = Booking::where('id', $request->booking_id)->first();


    if (!$booking) {
        return response()->json([
            'success' => false,
            'message' => 'Booking not found for this user.',
        ], 404);
    }

    $booking->status = 'charging_started';
    $booking->save();

    return response()->json([
        'success' => true,
        'message' => 'Charging successfully started.',
        'booking' => $booking,
    ]);
}
public function check(Request $request){
    $request->validate([
        'booking_id' => 'required|integer',
    ]);

    $booking = Booking::find($request->booking_id);

    if (!$booking) {
        return response()->json([
            'success' => false,
            'message' => 'Booking not found',
        ], 404);
    }

    if ($booking->status === 'charging_started') {
        return response()->json([
            'success' => true,
            'status' => 'charging_started',
            'message' => 'Charging has started.',
        ]);
    }

    return response()->json([
        'success' => true,
        'status' => $booking->status,
        'message' => 'Charging not started yet.',
    ]);
}
public function check_hargingstatus(Request $request)
{
    $request->validate([
        'booking_id' => 'required|exists:bookings,id',
    ]);

    $booking = Booking::find($request->booking_id);

    if ($booking->status !== 'charged') {
        return response()->json([
            'message' => 'Please Contact Your Seller to stop Charging',
            'booking_status' => $booking->status
        ], 403);
    }

    // Calculate cost (you can replace this logic)

    return response()->json([
        'message' => 'Charging status confirmed.',
        'booking_status' => $booking->status,
    ]);
}
public function get_rating(Request $request){
    $user = auth()->user();

    if (!$user) {
        return response()->json(['message' => 'Unauthenticated'], 401);
    }

    // Get charger_id from request
    $chargerId = $request->query('charger_id');
    
    if (!$chargerId) {
        return response()->json(['message' => 'Charger ID is required'], 400);
    }

    $bookings = Booking::where('charger_id', $chargerId)
        ->latest()
        ->take(15)
        ->with(['user:id,first_name,last_name', 'reviews'])
        ->get();

    $result = $bookings->map(function ($booking) {
        $latestReview = $booking->reviews->sortByDesc('created_at')->first();
        
        return [
            'id' => $latestReview->id ?? null,
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'charger_id' => $booking->charger_id,
            'first_name' => $booking->user->first_name ?? '',
            'last_name' => $booking->user->last_name ?? '',
            'rating' => $latestReview?->rating ?? null,
            'feedback' => $latestReview?->feedback ?? null,
            'created_at' => $latestReview?->created_at ?? $booking->created_at,
            'updated_at' => $latestReview?->updated_at ?? $booking->updated_at,
        ];
    })->filter(function ($item) {
        // Only include bookings that have reviews
        return !is_null($item['rating']);
    })->values();

    return response()->json($result);
}
public function getBookingAmount(Request $request)
{
    $bookingId = $request->input('bookingId');

    $booking = Booking::find($bookingId);

    if (!$booking) {
        return response()->json(['error' => 'Booking not found'], 404);
    }

    return response()->json([
        'amount' => $booking->amount ?? '0.00',
        'status' => $booking->status,
        'date' => $booking->date,
        'from_time' => $booking->from_time,
        'to_time' => $booking->to_time,
    ]);
}

public function check_hargingstatuseller(Request $request)
{
   $request->validate([
        'booking_id' => 'required|exists:bookings,id',
        'amount' => 'required',
    ]);

    $user = auth()->user(); // Get the authenticated user

    $booking = Booking::find($request->booking_id);

    // Check if the user is authorized to update this booking
    if ($booking->seller_id != $user->id) {
        return response()->json([
            'message' => 'Unauthorized access to this booking.',
        ], 403);
    }

    // Update the booking status to "charged"
    $booking->status = 'charged';
    $booking->save();
try{
   $deviceTokenEntry = DeviceToken::where('user_id', $booking->user_id)->first();

    if (!$deviceTokenEntry) {
        \Log::warning('No device token found for user ID: ' . $user->id);
        return response()->json([
            'message' => 'Booking confirmed but no device token found for notification',
            'booking' => $booking,
            'notification_sent' => false
        ], 200);
    }

    $fcmToken = $deviceTokenEntry->device_token;

    // Firebase credentials and project details
    $serviceAccountPath = base_path('firebase/serviceAccountKey.json');
    $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

    $creds = new ServiceAccountCredentials($scopes, $serviceAccountPath);
    $authToken = $creds->fetchAuthToken();

    if (!isset($authToken['access_token'])) {
        \Log::error('Failed to fetch Firebase access token');
        return response()->json(['message' => 'Failed to authenticate with Firebase'], 500);
    }

    $accessToken = $authToken['access_token'];
    $projectId = 'evcircle-9c7ec'; 

    $data = [
        "message" => [
            "token" => $fcmToken,
            "notification" => [
                "title" => "EVCircle",
                "body" => "Your EV charger booking has been Ended!",
            ],
            "data" => [
                "screen" => "BookingDetails",
                "booking_id" => (string) $booking->id,
            ]
        ]
    ];

    // Send notification via FCM HTTP v1 endpoint
    $response = Http::withHeaders([
        "Authorization" => "Bearer $accessToken",
        "Content-Type" => "application/json",
    ])->post("https://fcm.googleapis.com/v1/projects/$projectId/messages:send", $data);

    if (!$response->successful()) {
        \Log::error('FCM send failed: ' . $response->body());
    }
        $booking->status = 'charged';
        $booking->amount = $request->amount;
        $booking->save();

        return response()->json([
            'message' => 'Seller notified successfully',
            'notification_type' => $request->type ?? 'end_session',
            'booking' => $booking,
            'notification_sent' => $response->successful(),
        ], 200);

    } catch (\Exception $e) {
        \Log::error('Notify seller error: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to notify seller'], 500);
    }


    return response()->json([
        'message' => 'Charging status updated to charged.',
        'booking_status' => $booking->status,
    ]);
}
public function notifySeller(Request $request)
{
    $request->validate([
        'notify_time' => 'required|date',
    ]);

    try {
        $user = Auth::user();

        $booking = Booking::where('user_id', $user->id)
                          ->where('status', 'charging_started')
                          ->latest()
                          ->first();

        if (!$booking) {
            return response()->json(['message' => 'Active booking not found'], 404);
        }

        $sellerId = $booking->seller_id;

        if (!$sellerId) {
            return response()->json(['message' => 'Seller not found'], 404);
        }

       $deviceTokenEntry = DeviceToken::where('user_id', $booking->seller_id)->first();

    if (!$deviceTokenEntry) {
        \Log::warning('No device token found for user ID: ' . $user->id);
        return response()->json([
            'message' => 'Booking confirmed but no device token found for notification',
            'booking' => $booking,
            'notification_sent' => false
        ], 200);
    }

    $fcmToken = $deviceTokenEntry->device_token;

    // Firebase credentials and project details
    $serviceAccountPath = base_path('firebase/serviceAccountKey.json');
    $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

    $creds = new ServiceAccountCredentials($scopes, $serviceAccountPath);
    $authToken = $creds->fetchAuthToken();

    if (!isset($authToken['access_token'])) {
        \Log::error('Failed to fetch Firebase access token');
        return response()->json(['message' => 'Failed to authenticate with Firebase'], 500);
    }

    $accessToken = $authToken['access_token'];
    $projectId = 'evcircle-9c7ec'; 

    $data = [
        "message" => [
            "token" => $fcmToken,
            "notification" => [
                "title" => "EVCircle",
                "body" => "Your EV charger booking has been Ended!",
            ],
            "data" => [
                "screen" => "BookingDetails",
                "booking_id" => (string) $booking->id,
            ]
        ]
    ];

    // Send notification via FCM HTTP v1 endpoint
    $response = Http::withHeaders([
        "Authorization" => "Bearer $accessToken",
        "Content-Type" => "application/json",
    ])->post("https://fcm.googleapis.com/v1/projects/$projectId/messages:send", $data);

    if (!$response->successful()) {
        \Log::error('FCM send failed: ' . $response->body());
    }
        $booking->status = 'charged';
        $booking->save();

        return response()->json([
            'message' => 'Seller notified successfully',
            'notification_type' => $request->type ?? 'end_session',
            'booking' => $booking,
            'notification_sent' => $response->successful(),
        ], 200);

    } catch (\Exception $e) {
        \Log::error('Notify seller error: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to notify seller'], 500);
    }
}
}
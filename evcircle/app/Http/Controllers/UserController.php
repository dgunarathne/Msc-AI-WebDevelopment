<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\EvCharger;
use App\Models\Booking;
use App\Models\Referral;
use App\Models\DeviceToken;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\ChargingHistory;
use App\Models\Notification;
use App\Models\Payment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendReceipt;
use App\Mail\WelcomeMailevc;


class UserController extends Controller
{
    
public function send_receipt(Request $request){
     try {
        $data = $request->only(['start_time', 'end_time', 'name', 'email', 'price']);

        Mail::to($data['email'])->send(new SendReceipt($data));

        \Log::info('Receipt sent to: ' . $data['email']);
        return response()->json(['message' => 'Receipt email sent.']);
    } catch (\Exception $e) {
        \Log::error("Receipt sending failed: " . $e->getMessage());
        return response()->json(['message' => 'Failed to send receipt.'], 500);
    }
}
public function uploadProfilePhoto(Request $request)
{
    $request->validate([
    'photo' => 'required|image|max:5120',
]);

$image = $request->file('photo');

$img = ImageManagerStatic::make($image->getRealPath())
    ->resize(200, null, function ($constraint) {
        $constraint->aspectRatio();
        $constraint->upsize();
    })
    ->encode($image->getClientOriginalExtension(), 80);

$filename = uniqid('profile_', true) . '.' . $image->getClientOriginalExtension();
$localPath = "profile_photos/{$filename}";

$user = $request->user();

if ($user->image_url) {
    try {
        // Remove public URL base if present, to get relative path in storage/app/public
        $oldPath = str_replace(asset('storage') . '/', '', $user->image_url);

        if (Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }
    } catch (\Exception $e) {
        Log::warning('Could not delete old profile photo from local storage', [
            'error' => $e->getMessage(),
            'user_id' => $user->id,
        ]);
    }
}

$ok = Storage::disk('public')->put($localPath, (string)$img);

if (! $ok) {
    return response()->json([
        'error' => 'Failed to save image locally',
    ], 500);
}

$user->image_url = asset('storage/' . $localPath);
$user->save();

return response()->json([
    'message' => 'Profile photo uploaded successfully.',
    'photo_url' => $user->image_url,
]);
}

    public function loadtype(Request $request){
          $user = Auth::user();
    if (!$user) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
$chargerTypesString = env('CHARGER_TYPES', '');

$chargerTypes = [];

if ($chargerTypesString) {
    $items = explode(',', $chargerTypesString);
    foreach ($items as $item) {
        [$value, $label] = explode(':', $item);
        $chargerTypes[] = [
            'value' => $value,
            'label' => $label,
        ];
    }
}

    return response()->json(['charger_types' => $chargerTypes]);
}

public function check_confirmed(Request $request){
    $request->validate([
        'charger_id' => 'required',
    ]);

    $user = Auth::user();

    $confirmedBooking = Booking::where([
        'user_id' => $user->id,
        'charger_id' => $request->charger_id,
        'status' => 'confirmed'
    ])->first();

    return response()->json([
        'has_confirmed_booking' => !is_null($confirmedBooking),
        'booking' => $confirmedBooking
    ]);
}
    public function get_chargers(Request $request)
{
    $user = Auth::user();
    if (!$user) {
        return response()->json([
            'message' => 'Unauthorized. Please login to view chargers.',
        ], 401);
    }

    try {
        // Get all chargers except user's own
        $query = EvCharger::query()
            ->where('user_id', '!=', $user->id);

        // Add search filtering
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('station_name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('location', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('charger_type', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        // Location-based sorting if coordinates provided
        if ($request->has('latitude') && $request->has('longitude')) {
            $lat = $request->latitude;
            $lng = $request->longitude;

            // Calculate distance using Haversine formula
            $query->selectRaw(
                "*, 
                (6371 * acos(cos(radians(?)) 
                * cos(radians(latitude)) 
                * cos(radians(longitude) - radians(?)) 
                + sin(radians(?)) 
                * sin(radians(latitude)))) AS distance",
                [$lat, $lng, $lat])
            ->having('distance', '<=', 5000000)  
            ->orderBy('distance');
        }

        // Get pagination parameters
        $page = $request->input('page', 1);
        $limit = min($request->input('limit', 10), 30); // Limit max 30 per page

        // Paginate the results
        $chargers = $query->paginate($limit, ['*'], 'page', $page);

        // Transform the data
        $transformedChargers = $chargers->map(function ($charger) {
            $availabilityDate = new \DateTime($charger->active_until);
            $isActive = $availabilityDate > now();

            return [
                'id'            => $charger->id,
                'name'          => $charger->station_name,
                'rating'        => 0.0,
                'chargerType'   => $charger->charger_type,
                'port'          => $charger->connector_type,
                'power'         => $charger->power_output . ' kW',
                'pricePerHour'  => number_format($charger->price_per_kwh, 2) . ' LKR/kWh',
                'active_until'  => $charger->active_until,
                'active_from'  => $charger->active_from,
                'isActive'      => $isActive,
                'address'       => $charger->location,
                'latitude'      => $charger->latitude,
                'longitude'     => $charger->longitude,
                'distance'      => isset($charger->distance) ? round($charger->distance, 2) . ' km' : null,
            ];
        });

        return response()->json([
            'message' => 'EV Chargers fetched successfully.',
            'chargers' => $transformedChargers,
            'pagination' => [
                'current_page' => $chargers->currentPage(),
                'last_page' => $chargers->lastPage(),
                'per_page' => $chargers->perPage(),
                'total' => $chargers->total(),
                'has_more' => $chargers->hasMorePages(),
            ],
        ], 200);

    } catch (\Exception $e) {
        \Log::error('Charger fetch error: ' . $e->getMessage());
        return response()->json([
            'message' => 'Server error. Could not fetch chargers.',
            'error'   => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}

    public function get_chargersg(Request $request)
{
    $user = Auth::user();
    if (!$user) {
        return response()->json([
            'message' => 'Unauthorized. Please login to view chargers.',
        ], 401);
    }

    try {
        // Get all chargers except user's own
        $query = EvCharger::query()
            ->where('user_id', '!=', $user->id);

        // Add search filtering
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('station_name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('location', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('charger_type', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        // Location-based sorting if coordinates provided
        if ($request->has('latitude') && $request->has('longitude')) {
            $lat = $request->latitude;
            $lng = $request->longitude;

            // Calculate distance using Haversine formula
            $query->selectRaw(
                "*, 
                (6371 * acos(cos(radians(?)) 
                * cos(radians(latitude)) 
                * cos(radians(longitude) - radians(?)) 
                + sin(radians(?)) 
                * sin(radians(latitude)))) AS distance",
                [$lat, $lng, $lat])
            ->having('distance', '<=', 5000000)  
            ->orderBy('distance');
        }

        // Get pagination parameters
        $page = $request->input('page', 1);
        $limit = min($request->input('limit', 10), 30); // Limit max 30 per page

        // Paginate the results
        $chargers = $query->paginate($limit, ['*'], 'page', $page);

        // Transform the data
        $transformedChargers = $chargers->map(function ($charger) {
            $availabilityDate = new \DateTime($charger->active_until);
            $isActive = $availabilityDate > now();

            return [
                'id'            => $charger->id,
                'name'          => $charger->station_name,
                'rating'        => 0.0,
                'chargerType'   => $charger->charger_type,
                'port'          => $charger->connector_type,
                'power'         => $charger->power_output . ' kW',
                'pricePerHour'  => number_format($charger->price_per_kwh, 2) . ' LKR/kWh',
                'active_until'  => $charger->active_until,
                'active_from'  => $charger->active_from,
                'isActive'      => $isActive,
                'address'       => $charger->location,
                'latitude'      => $charger->latitude,
                'longitude'     => $charger->longitude,
                'distance'      => isset($charger->distance) ? round($charger->distance, 2) . ' km' : null,
            ];
        });

        return response()->json([
            'message' => 'EV Chargers fetched successfully.',
            'chargers' => $transformedChargers,
            'pagination' => [
                'current_page' => $chargers->currentPage(),
                'last_page' => $chargers->lastPage(),
                'per_page' => $chargers->perPage(),
                'total' => $chargers->total(),
                'has_more' => $chargers->hasMorePages(),
            ],
        ], 200);

    } catch (\Exception $e) {
        \Log::error('Charger fetch error: ' . $e->getMessage());
        return response()->json([
            'message' => 'Server error. Could not fetch chargers.',
            'error'   => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}
public function googleLogin(Request $request)
{
$request->validate([
'token' => 'required|string',
'device_token' => 'nullable|string',
'platform' => 'nullable|string',
]);


$client = new Client();
$client->setClientId('133369886278-cibrl08curm58ihgta9ephda1b7hsuha.apps.googleusercontent.com');

$payload = $client->verifyIdToken($request->token);

if (!$payload) {
    return response()->json([
        'message' => 'Invalid Google token'
    ], 401);
}

$email = $payload['email'] ?? null;

if (!$email) {
    return response()->json([
        'message' => 'Email not found in Google account'
    ], 400);
}

$user = User::where('email', $email)->first();

if (!$user) {
    return response()->json([
        'message' => 'No account found with this email'
    ], 404);
}

$token = $user->createToken('auth_token')->plainTextToken;

if ($request->device_token || $request->platform) {

    DeviceToken::where('device_token', $request->device_token)->delete();

    DeviceToken::where('user_id', $user->id)->delete();

    DeviceToken::create([
        'user_id' => $user->id,
        'device_token' => $request->device_token,
        'platform' => $request->platform,
    ]);
}

return response()->json([
    'message' => 'Login successful!',
    'user' => $user->only([
        'id',
        'first_name',
        'last_name',
        'email',
        'image_url',
        'profile_status',
        'subscription',
        'mobile',
        'changings'
    ]),
    'access_token' => $token,
    'token_type' => 'Bearer',
], 200);

}

public function activate_referral(Request $request){
    $user = Auth::user();
    if (!$user) {
        return response()->json([
            'message' => 'Unauthorized. Please login to view chargers.',
        ], 401);
    }

    // Validate the input
    $validated = $request->validate([
        'date_to' => 'required|date|after_or_equal:today',
        'amount' => 'required',
    ]);

    // Update the user's charger
    $charger = EvCharger::where('user_id', $user->id)->first();
    if (!$charger) {
        return response()->json([
            'message' => 'Charger not found or not owned by user.',
        ], 404);
    }

    $charger->active_until = $validated['date_to'];
    $charger->save();

    // --- Handle referral cleanup based on type ---
    $referralType = ucfirst(strtolower($request->type)); // e.g., "Day", "Week", "Month"
    $deleteCount = match (strtolower($request->type)) {
        'day' => 3,
        'week' => 5,
        'month' => 10,
        default => 0,
    };

    \App\Models\Referral::where('type', $referralType)
        ->where('referred_by', $user->id)
        ->orderBy('created_at')
        ->limit($deleteCount)
        ->delete();

    return response()->json([
        'message' => 'Subscription updated successfully.',
        'charger' => $charger,
        'deleted_referrals' => $deleteCount,
    ]);
}

public function referrals(Request $request)
{
    $user = Auth::user();

    $referral = Referral::where('referred_by', $user->id)->first();

    if (!$referral) {
        return response()->json([
            'message' => 'No referral data found',
            'data' => [
                'day_referrals' => 0,
                'week_referrals' => 0,
                'month_referrals' => 0,
                'referral_code' => null,
            ]
        ], 200);
    }

    // Fetch all referrals referred by the user
    $referrals = Referral::where('referred_by', $user->id)->get();

    // Initialize counters
    $dayCount = 0;
    $weekCount = 0;
    $monthCount = 0;

    foreach ($referrals as $ref) {
        $duration = is_numeric($ref->duration) ? (int) $ref->duration : 0;

        if (strtolower($ref->type) === 'day') {
            $dayCount += $duration/1;
        } elseif (strtolower($ref->type) === 'week') {
            $weekCount += $duration/7;
        } elseif (strtolower($ref->type) === 'month') {
            $monthCount += $duration/30;
        }
    }

    return response()->json([
        'message' => 'Referral data retrieved successfully',
        'data' => [
            'day_referrals' => $dayCount,
            'week_referrals' => $weekCount,
            'month_referrals' => $monthCount,
            'referral_code' => $referral->referral_code,
        ]
    ]);
}



public function subscribed(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        Log::warning('Unauthorized access attempt to subscription.');
        return response()->json([
            'message' => 'Unauthorized. Please login to view chargers.',
        ], 401);
    }

    Log::info("User {$user->id} is attempting to subscribe.", ['user' => $user->toArray()]);

    // Validate only duration and price now
    $validated = $request->validate([
        'duration' => 'required|integer|min:1|max:365',
        'price' => 'required|numeric',
        'start_date' => 'nullable|date',
    ]);

    Log::info('Validation passed.', $validated);

    // Create Payment
    $payment = Payment::create([
        'user_id' => $user->id,
        'duration' => $validated['duration'],
        'price' => $validated['price'],
    ]);

    Log::info("Payment created for user {$user->id}.", ['payment_id' => $payment->id]);

    // Update referral type
    $validDurations = [1, 7, 30];
    $ref = Referral::where('user_id', $user->id)->first();

if (in_array($validated['duration'], $validDurations)) {
    $type = match ($validated['duration']) {
        1 => 'Day',
        7 => 'Week',
        30 => 'Month',
        default => null,
    };

    if ($ref) {
        // ✅ Only update if referral_code is NOT NULL and type is NULL
        if (!is_null($ref->referral_code) && is_null($ref->type)) {
            $ref->type = $type;
            $ref->duration = $validated['duration'];
            $ref->save();

            Log::info("Referral updated for user {$user->id}.", ['type' => $ref->type]);
        } else {
            Log::info("Referral not updated for user {$user->id} — conditions not met.");
        }
    } else {
        // ✅ Create new referral (no restrictions)
        $ref = new Referral();
        $ref->user_id = $user->id;
        $ref->type = $type;
        $ref->save();

        Log::info("Referral created for user {$user->id}.", ['type' => $ref->type]);
    }
}
 else {
    Log::info("Referral skipped for user {$user->id} due to invalid duration.", [
        'duration' => $validated['duration']
    ]);
}


    // Find user's charger
    $charger = EvCharger::where('user_id', $user->id)->first();

    if (!$charger) {
        Log::error("Charger not found for user {$user->id}.");
        return response()->json([
            'message' => 'Charger not found or not owned by user.',
        ], 404);
    }

    // Set active_from = now, active_until = active_from + duration days
    $activeFrom = $request->has('start_date') 
        ? Carbon::parse($request->start_date) 
        : Carbon::now();
    $activeUntil = $activeFrom->copy()->addDays($validated['duration']);

    $charger->active_from = $activeFrom;
    $charger->active_until = $activeUntil;
    $charger->save();

    Log::info("Charger updated for user {$user->id}.", [
        'active_from' => $activeFrom->toDateTimeString(),
        'active_until' => $activeUntil->toDateTimeString()
    ]);

    return response()->json([
        'message' => 'Subscription updated successfully.',
        'charger' => $charger,
        'referral_type' => $ref?->type ?? null,
    ]);
}


protected function calculateAvailability($charger)
{
    $isAvailable = Booking::where('charger_id', $charger->id)
        ->where('from_time', '<=', now())
        ->where('to_time', '>=', now())
        ->doesntExist();
        
    return $isAvailable ? 'Available' : 'Occupied';
}
public function send(Request $request)
{
    $request->validate([
        'phone' => 'required|digits:10',
        'message' => 'required|string|max:320',
    ]);

    try {
        require_once base_path('vendor/autoload.php');

        $api_instance = new \NotifyLk\Api\SmsApi();

        $userId = env('NOTIFYLK_USER_ID');
        $apiKey = env('NOTIFYLK_API_KEY');
        $senderId = env('NOTIFYLK_SENDER_ID', 'NotifyDemo');

        $phone = $request->phone;
        if (substr($phone, 0, 1) === '0') {
            $phone = '94' . substr($phone, 1);
        }

        $message = $request->message;

        $api_instance->sendSMS($userId, $apiKey, $message, $phone, $senderId);

        return response()->json([
            'success' => true,
            'phone' => $phone,
            'message' => 'SMS sent successfully',
        ], 200);

    } catch (\Exception $e) {
        \Log::error('NotifyLK send error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Failed to send SMS',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function sendfogot(Request $request)
{
    $request->validate([
        'phone' => 'required|digits:10',
    ]);

    $phone = $request->input('phone');
    $user = User::where('mobile', $phone)->first();

    if (!$user) {
        return response()->json([
            'message' => 'Mobile Number Is Not Registerd',
        ], 404);
    }

    try {
        require_once base_path('vendor/autoload.php');

        $api_instance = new \NotifyLk\Api\SmsApi();

        $userId = env('NOTIFYLK_USER_ID');
        $apiKey = env('NOTIFYLK_API_KEY');
        $senderId = env('NOTIFYLK_SENDER_ID', 'NotifyDemo');

        $smsPhone = $phone;
        if (substr($smsPhone, 0, 1) === '0') {
            $smsPhone = '94' . substr($smsPhone, 1);
        }

        $message = 'Your password reset request was received.';

        $api_instance->sendSMS($userId, $apiKey, $message, $smsPhone, $senderId);

        return response()->json([
            'success' => true,
            'phone' => $smsPhone,
            'user_id' => $user->id,
            'message' => 'Forgot password SMS sent successfully',
        ], 200);

    } catch (\Exception $e) {
        \Log::error('NotifyLK forgot SMS error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Failed to send forgot password SMS',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    public function register_charger(Request $request){
         $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthorized. Please login to view chargers.',
        ], 401);
    }
            $validator = Validator::make($request->all(), [
                'latitude'       => 'required|max:255',
                'longitude'      => 'required|max:255',
                'station_name'   => 'required|string|max:255',
                'location'       => 'required|string|max:500',
                'charger_type'   => 'required|string|max:100',
                'power_type'     => 'required|string|max:100',
                'connector_type' => 'required|string|max:100',
                'power_output'   => 'required|numeric|min:0.1',
                'price_per_kwh'  => 'required|numeric|min:0',
                'promo_code'     => 'nullable|string|max:50',
            ]);
            
    $promoCode = $request->promo_code;

    $referredByUserId = null;
    $referralCode = null;

if ($promoCode) {
    $chargerOwner = EvCharger::where('promo_code', $promoCode)->first();

    if (!$chargerOwner) {
        return response()->json(['error' => 'Invalid promo code.'], 422);
    }

    // who created the promo code
    $referredByUserId = $chargerOwner->user_id;
    $referralCode = $promoCode;
}
if ($promoCode && $referredByUserId) {
    DB::table('referrals')->insert([
        'user_id'       => $user->id,
        'referral_code' => $referralCode,
        'referred_by'   => $referredByUserId,
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);
}
if ($validator->fails()) {
    return response()->json([
        'message' => 'Validation failed.',
        'errors'  => $validator->errors()
    ], 422);
}

$user = Auth::user();

if (!$user) {
    return response()->json([
        'message' => 'Unauthorized. Please login first.',
    ], 401);
}

// ✅ Check if user already has a charger registered
$existing = EvCharger::where('user_id', $user->id)->exists();

if ($existing) {
    return response()->json([
        'message' => 'You have already registered a charger.',
    ], 409); // 409 Conflict
}
function generateUniquePromoCode($length = 6) {
    do {
        // Generate random uppercase alphanumeric string
        $randomPart = strtoupper(Str::random($length));

        // Check if it contains at least one letter and one digit
        if (preg_match('/[A-Z]/', $randomPart) && preg_match('/\d/', $randomPart)) {
            $promoCode = 'PROMO' . $randomPart;

            // Check if promo code already exists
            $exists = EvCharger::where('promo_code', $promoCode)->exists();
        } else {
            // If criteria not met, regenerate
            $exists = true;
        }
    } while ($exists);

    return $promoCode;
}
$promoCode = generateUniquePromoCode(6);
try {
    $charger = EvCharger::create([
        'user_id'        => $user->id,
        'station_name'   => $request->station_name,
        'location'       => $request->location,
        'charger_type'   => $request->charger_type,
        'power_type'     => $request->power_type,
        'connector_type' => $request->connector_type,
        'availability'   => 'Not Verified',
        'power_output'   => $request->power_output,
        'price_per_kwh'  => $request->price_per_kwh,
        'latitude'       => $request->latitude,
        'longitude'      => $request->longitude,
        'promo_code'     => $promoCode,
    ]);
    if($charger->charger_type=='Commeciaral'){
        
    }
    return response()->json([
        'message' => 'EV Charger registered successfully.',
        'data'    => $charger
    ], 201);
} catch (\Exception $e) {
    return response()->json([
        'message' => 'Server error. Could not register charger.',
        'error'   => $e->getMessage()
    ], 500);
}
    }

    public function welcome(Request $request)
{
    $user = $request->user(); // Authenticated user

    if (!$user) {
        Log::warning('Welcome email failed: Unauthenticated request.');
        return response()->json(['error' => 'Unauthenticated'], 401);
    }

    Log::info('Queuing welcome email for user: ' . $user->email);

    // Queue welcome email
    try {
        Mail::to($user->email)->queue(new WelcomeMailevc($user->first_name . ' ' . $user->last_name));
        Log::info('Welcome email successfully queued to: ' . $user->email);
    } catch (\Exception $e) {
        Log::error('Failed to queue welcome email for ' . $user->email . '. Error: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to send welcome email.'], 500);
    }

    return response()->json(['message' => 'Welcome email queued successfully.']);
}
     public function register(Request $request)
{
    try {
        // Check if there is an existing user with same email or mobile
        $existingUser = User::where(function ($q) use ($request) {
            $q->where('email', $request->email)
              ->orWhere('mobile', $request->mobile);
        })->first();

        if ($existingUser) {
            if ($existingUser->profile_status == 0) {
                // delete user with profile_status = 0
                $existingUser->delete();
            } else {
                // existing user is active, reject registration
                return response()->json([
                    'message' => 'Email or mobile already in use.',
                ], 422);
            }
        }

        // now validate
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:20'], // removed unique
            'email' => ['required', 'string', 'email', 'max:255'], // removed unique
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'fcm' => ['required'],
            'platform' => ['required'],
        ]);

        // insert new user
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'profile_status' => 0,
            'subscription' => 0,
        ]);
        DeviceToken::where('device_token', $request->fcm)->delete();

        DeviceToken::where('user_id', $user->id)->delete();

        DeviceToken::create([
            'user_id' => $user->id,
            'device_token' => $request->fcm,
            'platform' => $request->platform,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        $email = $request->email;
        $name = $request->first_name . ' ' . $request->last_name;

        // Log::info("Attempting to send WelcomeMail to: {$email}");
        return response()->json([
            'message' => 'Registration successful!',
            'user' => $user->only(['id', 'first_name','last_name','email', 'profile_status', 'subscription']),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);

    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'Validation Failed',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'An error occurred during registration. Please try again.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function login(Request $request)
{
    $request->validate([
        'email' => ['required', 'string'],
        'password' => ['required', 'string'],
        'device_token' => ['nullable'],
        'platform' => ['nullable'],
    ]);

    $loginField = filter_var($request->input('email'), FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';
    $identifier = $request->input('email');
    $attemptsKey = 'login_attempts_' . $identifier;
    $blockKey = 'login_blocked_' . $identifier;
    $blockLevelKey = 'login_block_level_' . $identifier;

    $blockDurations = [1, 2, 5, 60, 600, 1440];

    if (cache()->has($blockKey)) {
        $blockUntilTimestamp = cache()->get($blockKey);
        if ($blockUntilTimestamp) {
            $blockUntil = Carbon::createFromTimestamp($blockUntilTimestamp);
            $now = Carbon::now();

            if ($blockUntil->gt($now)) {
                $remainingSeconds = $blockUntil->diffInSeconds($now);
                $remainingMinutes = ceil($remainingSeconds / 60);
                return response()->json([
                    'message' => "Too many failed attempts. Try again after $remainingMinutes minute(s).",
                    'retry_after_seconds' => $remainingSeconds,
                ], 429);
            } else {
                cache()->forget($blockKey);
                cache()->forget($blockLevelKey);
            }
        } else {
            cache()->forget($blockKey);
            cache()->forget($blockLevelKey);
        }
    }

    $credentials = [
        $loginField => $identifier,
        'password' => $request->password,
    ];

    if (!Auth::attempt($credentials)) {
        $attempts = cache()->increment($attemptsKey);
        if ($attempts === 1) {
            cache()->put($attemptsKey, 1, now()->addMinutes(5));
        }

        if ($attempts >= 3) {
            $blockLevel = cache()->get($blockLevelKey, 0) + 1;
            cache()->put($blockLevelKey, $blockLevel);

            $duration = $blockDurations[min($blockLevel - 1, count($blockDurations) - 1)];

            $blockUntil = now()->addMinutes($duration);
            cache()->put($blockKey, $blockUntil->timestamp, $duration * 60);
            cache()->forget($attemptsKey);

            return response()->json([
                'message' => "Account temporarily locked after 3 failed attempts. Try again after $duration minute(s).",
                'blocked_for_minutes' => $duration,
            ], 429);
        }

        return response()->json([
            'message' => 'Invalid credentials.',
            'attempts' => $attempts,
        ], 401);
    }

    // Success: clear attempts and blocks
    cache()->forget($attemptsKey);
    cache()->forget($blockKey);
    cache()->forget($blockLevelKey);

    $user = Auth::user();
    $token = $user->createToken('auth_token')->plainTextToken;

if ($request->device_token || $request->platform) {
    // This will run if either device_token OR platform is NOT null
    DeviceToken::where('device_token', $request->device_token)->delete();

    DeviceToken::where('user_id', $user->id)->delete();

    DeviceToken::create([
        'user_id' => $user->id,
        'device_token' => $request->device_token,
        'platform' => $request->platform,
    ]);
}
    return response()->json([
        'message' => 'Login successful!',
        'user' => $user->only([
            'id','first_name','last_name','email','image_url',
            'profile_status','subscription','mobile','changings'
        ]),
        'access_token' => $token,
        'token_type' => 'Bearer',
    ], 200);
}

  public function verify(Request $request)
    {
         $user = $request->user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthorized',
        ], 401);
    }

    $user->profile_status = 1;
    $user->save();

    return response()->json([
        'message' => 'Status updated successfully',
        'status' => 1,
    ], 200);
    }

   public function verifyfogot(Request $request)
{
    $request->validate([
        'phone' => 'required|digits:10',
    ]);

    $user = User::where('mobile', $request->phone)->first();

    if (!$user) {
        return response()->json([
            'message' => 'User not found.',
        ], 404);
    }

    if ($user->profile_status == 1) {
        return response()->json([
            'message' => 'User already verified.',
            'status' => 1,
        ], 200);
    }

    return response()->json([
        'message' => 'User not verified.',
        'status' => 0,
    ], 200);
}

    public function getUserChargers()
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthorized. Please login first.',
        ], 401);
    }

    try {
        $chargers = EvCharger::where('user_id', $user->id)->get();

        return response()->json([
            'message' => 'EV Chargers fetchefd successfully.',
            'data'    => $chargers
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Server error. Could not fetch chargers.',
            'error'   => $e->getMessage()
        ], 500);
    }
}
public function chargingHistory(Request $request)
{
    $user = $request->user();

    if (!$user) {
        Log::warning('Charging history request failed: unauthorized access attempt.');
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    try {
        Log::info("Fetching charging history for user ID: {$user->id}");

        // Eager load associated chargers
        $history = Booking::with('evcharger')
            ->where('user_id', $user->id)
            ->where('status', 'charged')
            ->latest()
            ->paginate(10);

        Log::info("Found {$history->total()} charged bookings for user ID: {$user->id}");

        $transformed = $history->map(function ($booking) {
            $charger = $booking->evcharger;

            // Log each booking's charger ID and charger details
            Log::debug("Booking ID: {$booking->id}, Charger ID: {$booking->charger_id}");
            Log::debug('Charger Data:', [$charger]);

            return [
                'booking_id'   => $booking->id,
                'start_time'   => $booking->from_time,
                'end_time'     => $booking->to_time,
                'date'     => $booking->date,
                'amount'     => $booking->amount,
                'total_cost'   => $booking->total_cost,
                'status'       => $booking->status,
                'charger_name' => $charger->station_name ?? 'Unknown',
                'port'         => $charger->connector_type ?? 'N/A',
                'location'     => $charger->location ?? null,
                'power_output' => $charger->power_output ? $charger->power_output . ' kW' : null,
                'image_url'    => $charger->image_url ?? 'https://placehold.co/100x100',
            ];
        });

        return response()->json([
            'message' => 'Charging history fetched successfully',
            'data' => $transformed,
            'pagination' => [
                'current_page' => $history->currentPage(),
                'last_page' => $history->lastPage(),
                'per_page' => $history->perPage(),
                'total' => $history->total(),
                'has_more' => $history->hasMorePages(),
            ],
        ], 200);

    } catch (\Exception $e) {
        Log::error('Error fetching charging history: ' . $e->getMessage(), ['user_id' => $user->id]);
        return response()->json([
            'message' => 'Server error. Could not fetch history.',
            'error' => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}
public function chargingHistoryr(Request $request)
{
    $user = $request->user();

    if (!$user) {
        Log::warning('Charging history request failed: unauthorized access attempt.');
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    try {
        Log::info("Fetching charging history for user ID: {$user->id}");

        // Eager load associated chargers
        $history = Booking::with('evcharger')
            ->where('seller_id', $user->id)
            ->where('status', 'charged')
            ->latest()
            ->paginate(10);

        Log::info("Found {$history->total()} charged bookings for user ID: {$user->id}");
        $transformed = $history->map(function ($booking) {
            $charger = $booking->evcharger;
            $user = $booking->user;

            // Log each booking's charger ID and charger details
            Log::debug("Booking ID: {$booking->id}, Charger ID: {$booking->charger_id}");
            Log::debug('Charger Data:', [$charger]);

            return [
              'booking_id'   => $booking->id,
                'start_time'   => $booking->from_time,
                'end_time'     => $booking->to_time,
                'date'     => $booking->date,
                'amount'     => $booking->amount,
                'total_cost'   => $booking->total_cost,
                'status'       => $booking->status,
                'charger_name' => ($user->first_name ?? 'Unknown') . ' ' . ($user->last_name ?? ''),
                'port'         => $charger->connector_type ?? 'N/A',
                'location'     => $charger->location ?? null,
                'power_output' => $charger->power_output ? $charger->power_output . ' kW' : null,
                'image_url'    => $charger->image_url ?? 'https://placehold.co/100x100',
            ];
        });

        return response()->json([
            'message' => 'Charging history fetched successfully',
            'data' => $transformed,
            'pagination' => [
                'current_page' => $history->currentPage(),
                'last_page' => $history->lastPage(),
                'per_page' => $history->perPage(),
                'total' => $history->total(),
                'has_more' => $history->hasMorePages(),
            ],
        ], 200);

    } catch (\Exception $e) {
        Log::error('Error fetching charging history: ' . $e->getMessage(), ['user_id' => $user->id]);
        return response()->json([
            'message' => 'Server error. Could not fetch history.',
            'error' => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}
public function updatePassword(Request $request)
{
    $request->validate([
        'newPassword' => 'required|min:6',
    ]);

    // Ensure the user is authenticated (via Sanctum, Passport, or JWT)
    $user = auth()->user();

    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $user->password = bcrypt($request->newPassword);
    $user->save();

    return response()->json(['message' => 'Password updated successfully'], 200);
}

public function updatePasswordfogot(Request $request)
{
    $request->validate([
        'phone' => 'required|digits:10',
        'newPassword' => 'required|min:6',
    ]);

    $user = User::where('mobile', $request->phone)->first();

    if (!$user) {
        return response()->json(['message' => 'User not found with this phone number.'], 404);
    }

    $user->password = bcrypt($request->newPassword);
    $user->save();

    return response()->json(['message' => 'Password updated successfully.'], 200);
}
public function storenotifications(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $notification = Notification::create([
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);

        return response()->json($notification, 201);
    }
public function getnotifications(Request $request){
        $user = Auth::user();

        $notifications = $user->notifications()
            ->orderByDesc('created_at')
            ->take(13) 
            ->get()
            ->groupBy(function ($item) {
                $date = Carbon::parse($item->created_at);
                if ($date->isToday()) return 'Today';
                if ($date->isYesterday()) return 'Yesterday';
                return $date->format('d M Y');
            });

        // Auto mark unseen as seen
        $user->notifications()->where('seen', false)->update(['seen' => true]);

        return response()->json($notifications);
}
public function hasUnseenNotifications(Request $request)
{
    $user = Auth::user();

    $hasUnseen = $user->notifications()->where('seen', false)->exists();

    return response()->json([
        'has_unseen' => $hasUnseen,
    ]);
}


}

<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\Referral;
use App\Models\SellerPromotion;
use Carbon\Carbon;
use App\Models\EvCharger;
use Illuminate\Http\Request;

class AdminController extends Controller
{
public function c_count(Request $request)
{
    // Assuming "active_from" and "active_until" determine active status
    $now = now();

    $activeCount = EvCharger::where('active_from', '<=', $now)
        ->where('active_until', '>=', $now)
        ->count();

    $inactiveCount = EvCharger::where(function ($query) use ($now) {
        $query->where('active_from', '>', $now)
              ->orWhere('active_until', '<', $now)
              ->orWhereNull('active_from')
              ->orWhereNull('active_until');
    })->count();

    $totalCount = EvCharger::count();

    return response()->json([
        'active'   => $activeCount,
        'inactive' => $inactiveCount,
        'total'    => $totalCount
    ]);
}
   public function send_admin_otp(Request $request){
     $pathToFile = base_path('vendor/notifylk/notify-php/docs/Api/SmsApi.php');
        include_once($pathToFile);
        return response()->json([
            'number' => $status,
            'result' => $result
        ], 200);
   }
   public function login(Request $request)
    {
      $request->validate([
        'username' => 'required|string',
        'password' => 'required|string',
    ]);

    $admin = Admin::where('username', $request->username)->first();

    if (!$admin || !Hash::check($request->password, $admin->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid username or password',
        ], 401);
    }

    // Create Sanctum token
    $token = $admin->createToken('admin_auth_token')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Login successful',
        'token' => $token,
        'user' => $admin,
    ]);
    }
    public function get_promo()
    {
        return response()->json(SellerPromotion::orderBy('created_at', 'desc')->get());
    }

    // Add or update a promotion (upsert)
    public function upsert(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required',
            'status' => 'required',
        ]);

        $promotion = SellerPromotion::updateOrCreate(
            ['title' => $request->title],
            [
                'description' => $request->description,
                'type' => $request->type,
                'status' => $request->status,
            ]
        );

        return response()->json([
            'success' => true,
            'promotion' => $promotion
        ]);
    }
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 5);
        $search = $request->input('search');
        
        $query = User::query();
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                  ->orWhere('last_name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('mobile', 'like', "%$search%");
            });
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ]
        ]);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function updateStatus(Request $request, User $user)
    {
        $request->validate([
            'profile_status' => 'required|boolean'
        ]);
        
        $user->profile_status = $request->profile_status;
        $user->save();
        
        return response()->json([
            'message' => 'User status updated successfully',
            'profile_status' => $user->profile_status
        ]);
    }

    public function getUserStats()
    {
        $totalUsers = User::count();
        $activeToday = User::where('profile_status', 1)
            ->whereDate('updated_at', Carbon::today())
            ->count();
        $activeThisWeek = User::where('profile_status', 1)
            ->whereBetween('updated_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->count();

        return response()->json([
            'total_users' => $totalUsers,
            'active_today' => $activeToday,
            'active_this_week' => $activeThisWeek
        ]);
    }

    public function getUserGrowth()
    {
        $months = 6; // Last 6 months
        $data = [];
        
        for ($i = $months; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $month = $date->format('M');
            
            $newUsers = User::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
                
            $activeUsers = User::where('profile_status', 1)
                ->whereYear('updated_at', $date->year)
                ->whereMonth('updated_at', $date->month)
                ->count();
                
            $data[] = [
                'month' => $month,
                'new_users' => $newUsers,
                'active_users' => $activeUsers
            ];
        }
        
        return response()->json($data);
    }

  public function updateplan(Request $request){
      Log::info('Received update plan request', $request->all());

    // Validate request
    $validated = $request->validate([
        'id' => 'required',
        'name' => 'required|string',
        'duration' => 'required|string',
        'price' => 'required|numeric',
        'startDate' => 'nullable|date',
    ]);

    Log::debug('Validated data', $validated);

    // Extract number of days from duration string (e.g., "30 days")
    preg_match('/\d+/', $validated['duration'], $matches);
    $days = $matches[0] ?? 0;

    Log::debug('Extracted days from duration', ['duration' => $validated['duration'], 'days' => $days]);

    try {
        // Find the existing plan
        $plan = SubscriptionPlan::findOrFail($validated['id']);
        Log::info('Found plan', ['plan_id' => $plan->id]);

        // Update the plan
        $plan->update([
            'name' => $validated['name'],
            'duration' => $validated['duration'],
            'days' => $days,
            'price' => $validated['price'],
            'start_date' => $validated['startDate'] ?? null,
        ]);

        Log::info('Plan updated successfully', ['plan_id' => $plan->id]);

        return response()->json([
            'success' => true,
            'plan' => $plan,
            'message' => 'Plan updated successfully.',
        ]);
    } catch (\Exception $e) {
        Log::error('Failed to update plan', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to update plan',
        ], 500);
    }
  }
  public function add_plan(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string',
        'duration' => 'required|string',
        'price' => 'required|numeric',
        'startDate' => 'nullable|date', 
    ]);

    preg_match('/\d+/', $validated['duration'], $matches);
    $days = $matches[0] ?? 0;

    $plan = SubscriptionPlan::create([
        'name' => $validated['name'],
        'duration' => $validated['duration'],
        'days' => $days,
        'price' => $validated['price'],
        'start_date' => $validated['startDate'] ?? null,
        'popular' => 0,
    ]);

    return response()->json([
        'success' => true,
        'plan' => $plan,
    ]);
}
 public function getPlans()
    {
        $plans = SubscriptionPlan::all();
        return response()->json($plans);
    }

    // Create new subscription plan
    public function createPlan(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'duration' => 'required|string|max:255',
            'days' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'popular' => 'sometimes|boolean',
            'start_date' => 'nullable|date'
        ]);

   $data = [
    'name' => $validated['name'],
    'duration' => $validated['duration'],
    'days' => $validated['days'],
    'price' => $validated['price'],
    'popular' => $validated['popular'] ?? false,
];

if (!empty($validated['start_date'])) {
    $data['start_date'] = $validated['start_date'];
}

$plan = SubscriptionPlan::create($data);

        return response()->json($plan, 201);
    }

    // Delete subscription plan
    public function deletePlan($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $plan->delete();
        return response()->json(['message' => 'Plan deleted successfully']);
    }

    // Get all referrals
    public function getReferrals()
    {
        $referrals = Referral::orderBy('created_at', 'desc')->get();
        return response()->json($referrals);
    }

    // Create new referral
    public function createReferral(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|string|unique:referrals,user_id',
            'referral_code' => 'required|string|unique:referrals,referral_code',
            'type' => 'nullable|string',
            'referred_by' => 'required|string'
        ]);

        $referral = Referral::create($validated);
        return response()->json($referral, 201);
    }

    // Delete referral
    public function deleteReferral($id)
    {
        $referral = Referral::findOrFail($id);
        $referral->delete();
        return response()->json(['message' => 'Referral deleted successfully']);
    }


}

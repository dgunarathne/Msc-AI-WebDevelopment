<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DeviceTokenController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TransactionContraller;
use App\Http\Controllers\WalletContraller;
use App\Http\Controllers\ChargerController;
use App\Http\Controllers\MlController;
use App\Http\Controllers\VehicleController;


Route::get('/health', function () {
    return response('OK', 200);
});


Route::get('/test-tkt-login', function () {
    return app(\App\Services\TktAuthService::class)->getToken();
});

Route::get('/check', function () {
    return response('OK', 200);
});
Route::post('/login', [UserController::class, 'login']);


Route::middleware(['apikey'])->group(function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/send_otp_fogot', [UserController::class, 'sendfogot']);
    Route::post('/update_password_fogot', [UserController::class, 'updatePasswordfogot']);
    Route::post('/verify_fogot', [UserController::class, 'verifyfogot']);
    Route::post('admin_login', [AdminController::class, 'login']);

    Route::post('/add_plan', [AdminController::class, 'add_plan']);
    Route::post('/send_admin_otp', [AdminController::class, 'send_admin_otp']);

    Route::get('/users', [AdminController::class, 'index']);
    Route::get('/user-stats', [AdminController::class, 'getUserStats']);
    Route::get('/user-growth', [AdminController::class, 'getUserGrowth']);

    Route::put('/plans', [AdminController::class, 'updateplan']);
    Route::get('/plans', [AdminController::class, 'getPlans']);
    Route::post('/plans', [AdminController::class, 'createPlan']);
    Route::delete('/plans/{id}', [AdminController::class, 'deletePlan']);
        
    Route::get('/referralsadmin', [AdminController::class, 'getReferrals']);

    Route::post('/promotions/seller/upsert', [AdminController::class, 'upsert']);
    Route::post('/c_count', [AdminController::class, 'c_count']);



    Route::get('/chargerstkt', [ChargerController::class, 'index']);        // List all chargers
    Route::post('/chargerstkt', [ChargerController::class, 'store']);       // Add a new charger
    Route::get('/chargerstkt/{id}', [ChargerController::class, 'show']);    // Show single charger
    Route::put('/chargerstkt/{id}', [ChargerController::class, 'update']);  // Update charger
    Route::delete('/chargerstkt/{id}', [ChargerController::class, 'destroy']);
Route::post('/auth/google', [UserController::class, 'googleLogin']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/welcome', [UserController::class, 'welcome']);
        Route::post('/get_booking_amount', [BookingController::class, 'getBookingAmount']);
        Route::post('/photo', [UserController::class, 'uploadProfilePhoto']);
        Route::post('/resend-receipt', [UserController::class, 'send_receipt']);
        Route::get('/promotions/seller', [AdminController::class, 'get_promo']);
        Route::get('/loadtype', [UserController::class, 'loadtype']);
        Route::post('/register-charger', [UserController::class, 'register_charger']);
        Route::post('/chargers', [UserController::class, 'get_chargers']);
        Route::post('/send_otp', [UserController::class, 'send']);
        Route::post('/verify', [UserController::class, 'verify']);
        Route::get('/charging-history', [UserController::class, 'chargingHistory']);
        Route::get('/charging-historyr', [UserController::class, 'chargingHistoryr']);
        Route::post('/update_password', [UserController::class, 'updatePassword']);
        Route::get('/getUserChargers', [UserController::class, 'getUserChargers']);
        Route::post('/notifications', [UserController::class, 'storenotifications']);
        Route::get('/notifications', [UserController::class, 'getnotifications']);
        Route::post('/subscribed', [UserController::class, 'subscribed']);
        Route::post('/activate-referral', [UserController::class, 'activate_referral']);
        Route::get('/unseen', [UserController::class, 'hasUnseenNotifications']);
        Route::get('/referrals', [UserController::class, 'referrals']);
        Route::post('/bookings/check-confirmed', [UserController::class, 'check_confirmed']);

        
        Route::post('/check_availability', [BookingController::class, 'checkAvailability']);
        Route::post('/book_slot', [BookingController::class, 'book']);
        Route::get('/booking-history', [BookingController::class, 'bookinghistory']);
        Route::get('/getmybookings', [BookingController::class, 'getmybookings']);
        Route::get('/getmybookingsu', [BookingController::class, 'getmybookingsu']);
        Route::post('/bookings-accept', [BookingController::class, 'bookingsaccept']);
        Route::post('/bookings-cancel', [BookingController::class, 'bookingsacancel']);
        Route::post('/bookings-cancelu', [BookingController::class, 'bookingsacancelu']);
        Route::post('/start_charging', [BookingController::class, 'startcharging']);
        Route::post('/start_chargings', [BookingController::class, 'startchargingu']);
        Route::post('/feedback', [BookingController::class, 'feedback']);
        Route::get('/earnings', [BookingController::class, 'earnings']);
        Route::post('/send_reviews', [BookingController::class, 'sendreviews']);
        Route::get('/subscription-plans', [BookingController::class, 'subscriptionplans']);
        Route::post('/complete_charging', [BookingController::class, 'completecharging']);
        Route::post('/notify_seller', [BookingController::class, 'notifySeller']);
        Route::post('/check_charging_status', [BookingController::class, 'check_hargingstatus']);
        Route::post('/check_hargingstatuseller', [BookingController::class, 'check_hargingstatuseller']);
        Route::get('/get_rating', [BookingController::class, 'get_rating']);
        Route::post('/update_earnings', [BookingController::class, 'update_earnings']);
        Route::post('/device-tokens', [DeviceTokenController::class, 'store']);
        Route::post('/device-tokens', [DeviceTokenController::class, 'store']);
        Route::post('/start-qr', [BookingController::class, 'startqr']);
        Route::post('/check-booking-status', [BookingController::class, 'check']);


        // new charing Apis for wallet and chager managemt and chager contrall
        Route::post('/Rechrage_wallet', [WalletContraller::class, 'Rechrage_wallet']);
        Route::get('/transaction_history', [TransactionContraller::class, 'index']);

        // Rider EV profile (used by range prediction / recommendation / route planning)
        Route::get('/vehicles', [VehicleController::class, 'index']);
        Route::post('/vehicles', [VehicleController::class, 'store']);
        Route::put('/vehicles/{id}', [VehicleController::class, 'update']);

        // AI/ML features: waiting-time prediction, station recommendation,
        // EV range prediction, energy-efficient route planning.
        Route::post('/ml/wait-time', [MlController::class, 'waitTime']);
        Route::post('/ml/range-prediction', [MlController::class, 'rangePrediction']);
        Route::post('/ml/recommend-stations', [MlController::class, 'recommendStations']);
        Route::post('/ml/route-plan', [MlController::class, 'routePlan']);
        Route::post('/check_availability_with_wait', [BookingController::class, 'checkAvailabilityWithWait']);

    });
});

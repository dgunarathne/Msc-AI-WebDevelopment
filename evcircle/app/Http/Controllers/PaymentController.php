<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function initiate(Request $request)
{
    $baseUrl = 'http://evcircle.lk';
    $orderId = uniqid();

    $data = [
        'merchant_id' => '1230787', // Sandbox merchant ID
        'return_url' => $baseUrl . '/return',

        'cancel_url' => $baseUrl . '/cancel',

        'notify_url' => $baseUrl . '/notify',

        'order_id' => $orderId,
        'items' => $request->items ?? 'Test Product',
        'currency' => 'LKR',
        'amount' => $request->amount,
        'first_name' => $request->first_name,
        'last_name' => $request->last_name,
        'email' => $request->email,
        'phone' => $request->phone,
        'address' => $request->address,
        'city' => $request->city,
        'country' => $request->country ?? 'Sri Lanka',
    ];

    return response()->json([
        'checkout_url' => 'https://sandbox.payhere.lk/pay/checkout?' . http_build_query($data),
    ]);
}

}

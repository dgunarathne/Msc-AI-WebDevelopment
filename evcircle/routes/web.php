<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public Pages
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/register', function () {
    return view('register');
})->name('register');

Route::get('/add-charge', function () {
    return view('add-charge');
})->name('add-charge');

// Legal Pages
Route::get('/Privacy-Policy', function () {
    return view('privacy');
})->name('privacy.policy');

Route::get('/Terms-of-Service', function () {
    return view('terms');
})->name('terms.service');

Route::get('/Refund-Policy', function () {
    return view('refund');
})->name('refund.policy');

// Contact Form
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

// ============================================
// OTP VERIFICATION - Using Existing Mobile API
// ============================================

// Show OTP Verification Page
Route::get('/verify-otp', function () {
    return view('verify-otp');
})->name('otp.verify');

// Registration Success Page
Route::get('/register-success', function () {
    return view('register-success');
})->name('register.success');

// ============================================
// PROTECTED ROUTES (Requires Authentication)
// ============================================


// Fallback Route (404)
Route::fallback(function () {
    return view('errors.404');
});
<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
// ->middleware('throttle:forgot-password');

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');
Route::post('/verify-registration-otp', [AuthController::class, 'verifyRegistrationOtp'])->middleware('throttle:otp');
Route::post('/resend-registration-otp', [AuthController::class, 'resendRegistrationOtp'])->middleware('throttle:otp');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
// Route::post('/login', [AuthController::class, 'login']);

Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:password-reset');
Route::post('/verify-otp', [AuthController::class, 'verifyResetOtp'])->middleware('throttle:otp');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:password-reset');

Route::middleware(['jwt.auth', 'throttle:api'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);
    // Route::group(['middleware' => 'role:admin'], function () {
    // Route::post('/create-users',[UserController::class,'createUser'])->name('createUser');

    Route::middleware('role:admin')->group(function () {
        
        // Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    });
});

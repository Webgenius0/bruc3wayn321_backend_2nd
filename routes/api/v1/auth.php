<?php

use App\Http\Controllers\API\Auth\AuthApiController;
use App\Http\Controllers\API\Auth\SocialAuthController;
use App\Http\Controllers\API\Notification\FirebaseTokenController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::controller(AuthApiController::class)->prefix('auth')->middleware('throttle:auth-api')->group(function () {
    Route::post('register', 'register');
    Route::post('resend/register-otp', 'registerOtpResend');
    Route::post('verify/register', 'verifyRegister');
    Route::post('login', 'login');
    Route::post('logout', 'logout')->middleware('auth:sanctum');
    Route::post('forgetpass', 'forgotPassword');
    Route::post('verify-email', 'verifyForgetPass');
    Route::post('change-password', 'resetPassword');
});

Route::controller(FirebaseTokenController::class)->group(function () {
    Route::post('/firebase-token/add', 'updateFirebaseToken');
    Route::delete('/firebase-token/delete', 'deleteFirebaseToken');
});


Route::prefix('auth')->controller(SocialAuthController::class)->group(function () {
    Route::post('/social/login', 'socialLogin');
});

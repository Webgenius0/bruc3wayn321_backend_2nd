<?php

use App\Http\Controllers\API\SubscriptionApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/subscription', [SubscriptionApiController::class, 'show']);
    Route::post('/subscription/sync', [SubscriptionApiController::class, 'sync']);
    Route::post('/subscription/cancel', [SubscriptionApiController::class, 'cancel']);
});

Route::post('/webhooks/revenuecat', [SubscriptionApiController::class, 'webhook']);

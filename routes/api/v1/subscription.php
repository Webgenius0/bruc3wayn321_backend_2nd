<?php

use App\Http\Controllers\API\SubscriptionApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/subscription', [SubscriptionApiController::class, 'show']);
    Route::post('/subscription/sync', [SubscriptionApiController::class, 'sync']);
    Route::post('/subscription/cancel', [SubscriptionApiController::class, 'cancel']);

    // Free subscription endpoints
    Route::post('/subscription/activate-free', [SubscriptionApiController::class, 'activateFree']);
    Route::post('/subscription/activate-trial', [SubscriptionApiController::class, 'activateFreeTrial']);
    Route::get('/subscription/trial-status', [SubscriptionApiController::class, 'checkTrial']);
    Route::delete('/subscription/free', [SubscriptionApiController::class, 'deactivateFree']);

    // Admin: extend free trial (protected by admin middleware)
    Route::post('/subscription/extend-trial', [SubscriptionApiController::class, 'extendFreeTrial'])
        ->middleware('admin');

    // User usage limits
    Route::get('/subscription/usage-stats', [SubscriptionApiController::class, 'usageStats']);
    Route::post('/subscription/force-upgrade/{userId}', [SubscriptionApiController::class, 'forceUpgrade'])
        ->middleware('admin');
    Route::post('/subscription/force-downgrade/{userId}', [SubscriptionApiController::class, 'forceDowngrade'])
        ->middleware('admin');
});

Route::post('/webhooks/revenuecat', [SubscriptionApiController::class, 'webhook']);

// =========================================================================
// TEMPORARY TESTING ROUTES (Comment out or delete after frontend testing)
// =========================================================================
Route::post('/subscription/test-expire/{userId?}', [SubscriptionApiController::class, 'testExpire']);
Route::post('/subscription/test-reset-trial/{userId?}', [SubscriptionApiController::class, 'testResetTrial']);


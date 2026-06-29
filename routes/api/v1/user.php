<?php

use App\Http\Controllers\API\User\UserController;
use App\Http\Controllers\API\User\UserBlockReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::controller(UserController::class)->prefix('user')->middleware(['auth:sanctum', 'throttle:profile-api'])->group(function () {
    Route::get('details', 'profile');
    Route::post('update', 'update');
    Route::post('update-password', 'updatePassword');
    Route::post('notifications', 'updateNotifications');
    Route::delete('delete', 'destroy');
    Route::post('update/cover-image', 'updateCoverImage');
    Route::post('update/currency', 'updateCurrency');

    // Follow routes
    Route::post('{user}/follow', 'follow');
    Route::post('{user}/unfollow', 'unfollow');
    Route::post('{user}/toggle-follow', 'toggleFollow');
    Route::get('{user}/followers', 'getFollowers');
    Route::get('{user}/following', 'getFollowing');


    Route::get('{user_id}/details', 'userDetails');
    Route::get('chat/{chat_id}/attachment/list', 'attachmentList');
    Route::get('chat/{chat_id}/links/list', 'linksList');
});

// Block & Report routes
Route::controller(UserBlockReportController::class)
    ->prefix('user')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        // Block
        Route::post('{userId}/block', 'block');
        Route::post('{userId}/unblock', 'unblock');
        Route::post('{userId}/toggle-block', 'toggleBlock');
        Route::get('blocked/list', 'blockedList');
        Route::get('{userId}/block-status', 'blockStatus');

        // Report
        Route::post('{userId}/report', 'report');
    });

<?php

use App\Http\Controllers\ChatAppController;
use App\Http\Controllers\Web\Backend\DashboardController;
use App\Http\Controllers\Web\Frontend\PageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::inertia('/', 'welcome')->name('home');
Route::get('/', [DashboardController::class, 'home'])->name('home');

Route::get('/privacy-policy', [PageController::class, 'privacyPolicy'])->name('frontend.privacy.policy');
Route::get('/terms-and-conditions', [PageController::class, 'termsAndConditions'])->name('frontend.terms.conditions');

require __DIR__ . '/backend.php';
require __DIR__ . '/settings.php';



Route::get('/message', function () {
    return view('chat-home');
});

Route::post('/chatroom', function (Request $request) {

    $username = $request->name;
    return view('chatroom.chat', compact('username'));
})->name('chatroom');
// ->middleware('auth')

Route::post('/send-message', [ChatAppController::class, 'sendMessage'])->name('sendMessage');

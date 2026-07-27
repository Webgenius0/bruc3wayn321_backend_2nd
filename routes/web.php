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



// Chat App Routes
Route::get('/chat', [ChatAppController::class, 'enterName'])->name('chat.enter-name');
Route::post('/chat/room', [ChatAppController::class, 'chatroom'])->name('chat.room');
Route::get('/chat/room', [ChatAppController::class, 'chatroom']); // For reloads
Route::post('/send-message', [ChatAppController::class, 'sendMessage'])->name('chat.send-message');

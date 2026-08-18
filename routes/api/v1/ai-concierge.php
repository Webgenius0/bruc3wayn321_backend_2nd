<?php

use App\Http\Controllers\API\AiConcierge\AiConciergeController;
use Illuminate\Support\Facades\Route;

Route::controller(AiConciergeController::class)
    ->prefix('ai-concierge')
    ->middleware(['auth:sanctum', 'subscription:any'])
    ->group(function () {
        Route::get('conversations', 'index');
        Route::get('conversations/{conversation}', 'show');
        Route::post('chat', 'chat');
        Route::post('voice-chat', 'voiceChat');
        Route::delete('conversations/delete/{conversation}', 'destroy');
    });

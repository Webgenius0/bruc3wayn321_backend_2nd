<?php

use App\Http\Controllers\API\AiConcierge\AiConciergeController;
use Illuminate\Support\Facades\Route;

Route::controller(AiConciergeController::class)
    ->prefix('ai-concierge')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        Route::get('conversations', 'index');
        Route::get('conversations/{conversation}', 'show');
        Route::post('chat', 'chat')->middleware('subscription:any');;
        Route::post('voice-chat', 'voiceChat')->middleware('subscription:any');;
        Route::delete('conversations/delete/{conversation}', 'destroy');
    });

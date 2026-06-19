<?php

use App\Http\Controllers\API\Currency\CurrencyController;
use Illuminate\Support\Facades\Route;



Route::controller(CurrencyController::class)->middleware('auth:sanctum', 'throttle:auth-api')->group(function () {
    Route::get('/currencies', 'index');
    Route::post('/currency/convert', 'convert');
});

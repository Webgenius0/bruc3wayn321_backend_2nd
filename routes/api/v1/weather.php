<?php

use App\Http\Controllers\API\Destination\DestinationController;
use App\Http\Controllers\API\Weather\OpenWeatherController;
use Illuminate\Support\Facades\Route;


Route::controller(OpenWeatherController::class)->group(function () {
    Route::post('/location/weather', 'liveWeather');
});

Route::controller(DestinationController::class)->middleware('auth:sanctum')->group(function () {
    Route::get('/destination/list', 'index');
    Route::get('/destination/details/{id}', 'show');


    Route::get('/my-destinations', 'mydestination');
    Route::get('/user-destinations/{user_id}', 'userDestination');
    Route::post('/destinations/save', 'store');
    Route::delete('/destinations/save/{placeId}', 'destroy');
});

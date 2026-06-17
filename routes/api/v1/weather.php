<?php

use App\Http\Controllers\API\Weather\OpenWeatherController;
use Illuminate\Support\Facades\Route;


Route::controller(OpenWeatherController::class)->group(function () {
    Route::post('/location/weather', 'liveWeather');
});

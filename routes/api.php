<?php

use App\Services\OpenWeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/weather', function (OpenWeatherService $service) {

    // $data = $service->currentByCity("mohakhali");
    $data = $service->currentByCoordinates('23.8103', '90.4125');

    return $data;
});


Route::prefix('v1')->group(function () {
    require base_path('routes/api/v1/auth.php');
    require base_path('routes/api/v1/user.php');
    require base_path('routes/api/v1/settings.php');
    require base_path('routes/api/v1/currency.php');
    require base_path('routes/api/v1/post.php');
    require base_path('routes/api/v1/weather.php');
    require base_path('routes/api/v1/ai-concierge.php');
    require base_path('routes/api/v1/translate.php');
});

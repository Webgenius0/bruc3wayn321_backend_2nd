<?php

namespace App\Http\Controllers\API\Weather;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\WeatherRequest;
use App\Services\OpenWeatherService;
use Illuminate\Http\Request;

class OpenWeatherController extends Controller
{
    use ApiResponse;

    public function liveWeather(WeatherRequest $request, OpenWeatherService $service)
    {
        $data = $service->currentByCoordinates($request->lat, $request->lon);
        // dd($data);
        return $this->successResponse('Weather details', $data, 200);
    }
}

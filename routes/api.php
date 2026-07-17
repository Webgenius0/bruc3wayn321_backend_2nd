<?php

// use App\Http\Controllers\API\ChatApiController;
use App\Http\Controllers\API\Message\ChatApiController;
use App\Http\Controllers\API\Notification\FirebaseTokenController;
use App\Services\FCMService;
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



Route::middleware('auth:sanctum')->get('/test-push', [FirebaseTokenController::class, 'test']);



// Chat routes below were provided by twopoint0/reverb-chat (package removed).
// Uncomment when a chat package is re-integrated.
// Route::prefix('api/chat')->middleware(['api', 'auth:sanctum'])->group(function () {
//     Route::post('open-group', [ChatApiController::class, 'openOrCreate']);
//     Route::post('{chat}/message', [ChatApiController::class, 'sendByChat']);
//     Route::post('user/{user}/message', [ChatApiController::class, 'sendByUser']);
//     Route::get('messages/{id}', [ChatApiController::class, 'messagelist']);
//     Route::get('messages/user/{id}', [ChatApiController::class, 'usermessagelist']);
//     Route::get('/list', [ChatApiController::class, 'chatList']);
// });


// chat message controller 
Route::prefix('chat')->controller(ChatApiController::class)->middleware('auth:sanctum')->group(function () {
    Route::post('/open-group', 'openOrCreate');
    Route::post('{chat}/message', 'sendByChat');
    Route::post('user/{user}/message', 'sendByUser');
    Route::get('messages/{id}', 'messagelist');
    Route::get('messages/user/{id}', 'usermessagelist');
    Route::get('/list', 'chatList');
});

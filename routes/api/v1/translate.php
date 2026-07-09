<?php

use App\Http\Controllers\API\Translate\TranslateImageController;
use Illuminate\Support\Facades\Route;


Route::controller(TranslateImageController::class)->prefix('translate')->group(function () {
    Route::post('image', 'translate');
    Route::get('languages', 'languages');
});

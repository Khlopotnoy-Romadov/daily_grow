<?php

use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\ReviewController;

Route::post('/yandex/parse-reviews', [ReviewController::class, 'parseReviews']);

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/settings', [SettingController::class, 'show']);
    Route::post('/settings', [SettingController::class, 'update']);

    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::post('/rewiews/fetch', [ReviewController::class, 'fetchFromYandex']);
});
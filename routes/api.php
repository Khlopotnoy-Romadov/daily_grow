<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SettingController;

// Sanctum CSRF cookie
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
});

// Публичные маршруты
Route::post('/login', [AuthController::class, 'login']); // POST /api/login

// Защищенные маршруты
Route::middleware('auth:sanctum')->group(function(){
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/settings', [SettingController::class, 'show']);
    Route::post('/settings', [SettingController::class, 'update']);

    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews/fetch', [ReviewController::class, 'fetchFromYandex']);
    
    Route::post('/yandex/parse-reviews', [ReviewController::class, 'parseReviews']);
});
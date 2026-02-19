<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SettingController;

Route::get('/sanctum/csrf-cookie', function () {
    return response()->noContent();
});

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
    
    Route::post('/api/yandex/parse-reviews', [ReviewController::class, 'parseReviews']);
    Route::get('/api/reviews', [ReviewController::class, 'index']);
    Route::post('/api/reviews/fetch', [ReviewController::class, 'fetchFromYandex']);
    
    Route::get('/api/settings', [SettingController::class, 'show']);
    Route::post('/api/settings', [SettingController::class, 'update']);
});

// Страница входа
Route::get('/login', function () {
    return view('app');
});

// Все остальные маршруты
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');
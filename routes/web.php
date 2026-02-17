<?php
// routes/web.php

use App\Http\Controllers\ReviewController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::post('/api/yandex/parse-reviews', [ReviewController::class, 'parseReviews']);

Route::get('/{any?}', function () {
    return view('app');
})->where('any', '^(?!api).*$');
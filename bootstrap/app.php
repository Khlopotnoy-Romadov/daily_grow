<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
// Импортируйте ваш класс:
use App\Http\Middleware\VerifyCsrfToken;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // ... остальные настройки роутинга
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ЗАМЕНА СТАНДАРТНОГО MIDDLEWARE НА ВАШ:
        $middleware->statefulApi(); // Важно для Sanctum!
        
        // Подменяем стандартный класс VerifyCsrfToken на наш, где есть $except
        $middleware->replace(
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            VerifyCsrfToken::class
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
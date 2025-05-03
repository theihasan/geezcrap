<?php

use App\Jobs\ProcessJobScrapingJob;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        $schedule->job(new ProcessJobScrapingJob('simply-hired', 'https://www.simplyhired.com/search?q=php+developer&l=New+York%2C+NY'))->everyMinute();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

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
        $searchConfigs = config('job-search.simply-hired.search_urls', []);

        collect($searchConfigs)->each(function ($config) use ($schedule) {
            $url = sprintf(
                'https://www.simplyhired.com/search?q=%s&l=%s',
                $config['query'],
                $config['location']
            );

            $job = new ProcessJobScrapingJob('simply-hired', $url);

            $schedule->job($job)->daily();
        });

        //$easyJobAIConfigs = config('job-search.easy-job-ai.search_urls', []);
        //
        //collect($easyJobAIConfigs)->each(function($config) use ($schedule){
        //    $url = sprintf(
        //        'https://easyjobai.com/search/%s',
        //        $config['query']
        //    );
        //
        //    $job = new ProcessJobScrapingJob('easy-job-ai', $url);
        //    $schedule->job($job)->daily();
        //});

        $schedule->command('schedule:scrape-job-details --limit=10')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/scheduler.log'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

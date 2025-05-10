<?php

namespace App\Providers;

use App\Services\Scraper\Contracts\ParserInterface;
use App\Services\Scraper\Contracts\ScraperInterface;
use App\Services\Scraper\Parsers\DOMParser;
use App\Services\Scraper\Parsers\SimplyHiredJobDetailParser;
use App\Services\Scraper\Strategies\SimplyHired;
use App\Services\Scraper\Strategies\SimplyHiredJobDetail;
use Illuminate\Support\ServiceProvider;

class ScraperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the listing parser
        $this->app->bind('scraper.parser.simplyhired', function ($app) {
            return new DOMParser();
        });

        // Register the job detail parser
        $this->app->bind('scraper.parser.simplyhired.detail', function ($app) {
            return new SimplyHiredJobDetailParser();
        });

        // Register the listing scraper
        $this->app->bind('scraper.simplyhired', function ($app) {
            return new SimplyHired($app->make('scraper.parser.simplyhired'));
        });

        // Register the job detail scraper
        $this->app->bind('scraper.simplyhired.detail', function ($app) {
            return new SimplyHiredJobDetail($app->make('scraper.parser.simplyhired.detail'));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

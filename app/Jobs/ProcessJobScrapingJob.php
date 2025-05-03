<?php

namespace App\Jobs;

use App\Services\Scraper\Factory\ScraperFactory;
use App\Repositories\JobRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessJobScraping implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $source,
        private readonly string $url
    ) {}

    public function handle(ScraperFactory $factory, JobRepository $repository): void
    {
        $scraper = $factory->create($this->source);
        $jobs = $scraper->scrape($this->url);

        foreach ($jobs as $job) {
            if ($scraper->validate($job)) {
                $transformedJob = $scraper->transform($job);
                $repository->create($transformedJob);
            }
        }
    }
}

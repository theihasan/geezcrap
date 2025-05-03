<?php declare(strict_types=1);

namespace App\Jobs;

use App\Services\Scraper\Factory\ScraperFactory;
use App\Repositories\JobRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessJobScrapingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $source,
        private readonly string $url
    ) {
        Log::info('Job initialized', [
            'source' => $source,
            'url' => $url
        ]);
    }

    public function handle(ScraperFactory $factory, JobRepository $repository): void
    {
        Log::info('Starting job processing', [
            'source' => $this->source,
            'url' => $this->url
        ]);

        $scraper = $factory->create($this->source);
        Log::info('Scraper created', ['scraper_type' => get_class($scraper)]);

        $jobs = $scraper->scrape($this->url);
        Log::info('Scraping completed', [
            'jobs_found' => count($jobs)
        ]);

        $validJobs = 0;
        foreach ($jobs as $job) {
            try {
                if ($scraper->validate($job)) {
                    $transformedJob = $scraper->transform($job);
                    $repository->create($transformedJob);
                    $validJobs++;

                    Log::info('Job processed successfully', [
                        'title' => $transformedJob['title'] ?? 'N/A',
                        'company' => $transformedJob['company'] ?? 'N/A'
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error processing job', [
                    'error' => $e->getMessage(),
                    'job_data' => $job
                ]);
            }
        }

        Log::info('Job processing completed', [
            'total_jobs' => count($jobs),
            'valid_jobs' => $validJobs
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Job failed', [
            'source' => $this->source,
            'url' => $this->url,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}

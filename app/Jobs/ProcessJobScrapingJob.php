<?php declare(strict_types=1);

namespace App\Jobs;

use App\Services\Scraper\Factory\ScraperFactory;
use App\Repositories\JobRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ProcessJobScrapingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public array $backoff = [60, 120];
    public int $timeout = 600;
    public int $maxExceptions = 1;

    public function __construct(
        private readonly string $source,
        private readonly string $url
    ) {
        Log::info('Job initialized', [
            'source' => $source,
            'url' => $url,
            'attempt' => $this->attempts()
        ]);
    }

    public function handle(ScraperFactory $factory, JobRepository $repository): void
    {
        $rateLimitKey = "scraper_rate_limit_{$this->source}";
        $lockKey = "scraper_lock_{$this->source}_{$this->attempts()}";

        try {
            if (Cache::has($rateLimitKey)) {
                $delay = random_int(60, 120);
                Log::info('Rate limit hit, releasing job back to queue', [
                    'source' => $this->source,
                    'delay' => $delay,
                    'attempt' => $this->attempts()
                ]);
                $this->release($delay);
                return;
            }

            if (!Cache::add($lockKey, true, 300)) {
                Log::info('Job already processing, releasing back to queue', [
                    'source' => $this->source,
                    'attempt' => $this->attempts()
                ]);
                $this->release(30);
                return;
            }


            Cache::put($rateLimitKey, true, now()->addMinutes(2));

            Log::info('Starting job processing', [
                'source' => $this->source,
                'url' => $this->url,
                'attempt' => $this->attempts()
            ]);

            $scraper = $factory->create($this->source);
            Log::info('Scraper created', [
                'scraper_type' => get_class($scraper),
                'attempt' => $this->attempts()
            ]);

            $jobs = $scraper->scrape($this->url);

            if (empty($jobs)) {
                Log::warning('No jobs found', [
                    'source' => $this->source,
                    'url' => $this->url,
                    'attempt' => $this->attempts()
                ]);
                return;
            }

            Log::info('Scraping completed', [
                'jobs_found' => count($jobs),
                'attempt' => $this->attempts()
            ]);

            $validJobs = 0;
            $errors = 0;

            collect($jobs)->each(function ($job, $index) use ($scraper, $repository, &$validJobs, &$errors) {
                try {
                    if (! $scraper->validate($job)) {
                        Log::warning('Invalid job data', [
                            'index' => $index,
                            'job_data' => $job
                        ]);
                        return;
                    }

                    $transformedJob = $scraper->transform($job);
                    $repository->create($transformedJob);
                    $validJobs++;

                    Log::info('Job processed successfully', [
                        'index' => $index,
                        'title' => $transformedJob['title'] ?? 'N/A',
                        'company' => $transformedJob['company'] ?? 'N/A'
                    ]);
                } catch (\Exception $e) {
                    $errors++;
                    Log::error('Error processing individual job', [
                        'index' => $index,
                        'error' => $e->getMessage(),
                        'job_data' => $job
                    ]);

                    if ($errors >= 3) {
                        throw new RuntimeException('Too many errors processing jobs');
                    }
                }
            });

            Log::info('Job processing completed', [
                'total_jobs' => count($jobs),
                'valid_jobs' => $validJobs,
                'errors' => $errors,
                'attempt' => $this->attempts()
            ]);

        } catch (\Throwable $e) {
            Log::error('Job processing failed', [
                'source' => $this->source,
                'url' => $this->url,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);
            throw $e;
        } finally {
            Cache::forget($rateLimitKey);
            Cache::forget($lockKey);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Job failed permanently', [
            'source' => $this->source,
            'url' => $this->url,
            'error' => $exception->getMessage(),
            'final_attempt' => $this->attempts(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Clean up any remaining locks or rate limits
        Cache::forget("scraper_rate_limit_{$this->source}");
        Cache::forget("scraper_lock_{$this->source}_{$this->attempts()}");
    }
}

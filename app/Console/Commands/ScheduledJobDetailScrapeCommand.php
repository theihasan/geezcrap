<?php

namespace App\Console\Commands;

use App\Models\Source;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScheduledJobDetailScrapeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:scrape-job-details {--limit=50}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape job details from saved sources that have not been processed yet';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');

        $this->info("Starting scheduled job detail scraping (limit: {$limit})");
        Log::info("Starting scheduled job detail scraping", ['limit' => $limit]);

        try {
            $sources = Source::where('source', 'simply-hired')
                ->whereNull('processed_at')
                ->whereNotNull('url')
                ->limit($limit)
                ->get();

            $count = $sources->count();
            $this->info("Found {$count} sources to process");
            Log::info("Found sources to process", ['count' => $count]);

            if ($count === 0) {
                $this->info("No sources to process. Exiting.");
                return 0;
            }

            $scraper = app('scraper.simplyhired.detail');
            $processed = 0;
            $failed = 0;

            foreach ($sources as $source) {
                if (empty($source->url)) {
                    $this->warning("Skipping source with ID {$source->id} - source_url is empty");
                    Log::warning("Skipping source with empty source_url", ['source_id' => $source->id]);

                    $source->processed_at = now();
                    $source->save();
                    continue;
                }

                $this->info("Processing source: {$source->url}");

                try {
                    $scraper->scrape($source->url);

                    $source->processed_at = now();
                    $source->save();

                    $processed++;
                    $this->info("Successfully processed source: {$source->source_url}");

                    sleep(rand(2, 5));

                } catch (\Exception $e) {
                    $failed++;
                    $this->error("Failed to process source {$source->source_url}: {$e->getMessage()}");
                    Log::error("Failed to process source", [
                        'source_url' => $source->source_url,
                        'error' => $e->getMessage()
                    ]);


                    $source->fail_count = ($source->fail_count ?? 0) + 1;
                    if ($source->fail_count >= 3) {
                        $source->processed_at = now();
                    }

                    $source->save();
                }
            }

            $this->info("Completed job detail scraping: {$processed} processed, {$failed} failed");
            Log::info("Completed job detail scraping", [
                'processed' => $processed,
                'failed' => $failed
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error("Error in scheduled job scraping: {$e->getMessage()}");
            Log::error("Error in scheduled job scraping", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return 1;
        }
    }
}

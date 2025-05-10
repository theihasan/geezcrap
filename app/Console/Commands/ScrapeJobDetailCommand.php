<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScrapeJobDetailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:job-detail {url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape job details from a specific URL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->argument('url');

        $this->info("Starting to scrape job details from: {$url}");

        try {
            // Get the appropriate scraper based on the URL
            if (str_contains($url, 'simplyhired.com')) {
                $scraper = app('scraper.simplyhired.detail');
            } else {
                $this->error('Unsupported job board URL');
                return 1;
            }

            // Scrape the job details
            $result = $scraper->scrape($url);

            $this->info('Job details scraped successfully:');
            $this->table(
                ['Field', 'Value'],
                collect($result)
                    ->map(function ($value, $key) {
                        if (is_array($value)) {
                            $value = json_encode($value);
                        } elseif (is_bool($value)) {
                            $value = $value ? 'Yes' : 'No';
                        }
                        return [$key, $value];
                    })
                    ->toArray()
            );

            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to scrape job details: {$e->getMessage()}");
            Log::error('Job detail scraping command failed', [
                'url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return 1;
        }
    }
}

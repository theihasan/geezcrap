<?php declare(strict_types=1);

namespace App\Services\Scraper\Strategies;

use App\Services\Scraper\AbstractScraper;
use App\Services\Scraper\Contracts\ParserInterface;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Log;

class SimplyHired extends AbstractScraper
{
    public function __construct(ParserInterface $parser)
    {
        parent::__construct(new Browsershot(), $parser);
        Log::info('SimplyHired scraper initialized');
    }

    public function scrape(string $url): array
    {
        Log::info('Starting SimplyHired scraping', ['url' => $url]);

        $html = $this->getHtml($url);
        Log::info('HTML content retrieved', [
            'content_length' => strlen($html)
        ]);

        $results = $this->parser->parse($html);
        Log::info('Parsing completed', [
            'results_count' => count($results)
        ]);

        return $results;
    }

    public function validate(string $url): bool
    {
        $isValid = str_contains($url, 'simplyhired.com');
        Log::info('URL validation', [
            'url' => $url,
            'is_valid' => $isValid
        ]);
        return $isValid;
    }

    public function transform(array $data): array
    {
        Log::info('Transforming job data', [
            'original_data_keys' => array_keys($data)
        ]);

        $transformed = array_map(function($job) {
            $result = [
                'title' => $job['title'],
                'company' => $job['company'],
                'location' => $job['location'],
                'description' => $job['description'],
                'source' => 'simplyhired',
                'source_url' => $job['url'] ?? null,
                'scraped_at' => now(),
            ];

            Log::debug('Transformed job', [
                'title' => $result['title'],
                'company' => $result['company']
            ]);

            return $result;
        }, $data);

        Log::info('Transform completed', [
            'transformed_count' => count($transformed)
        ]);

        return $transformed;
    }
}

<?php declare(strict_types=1);

namespace App\Services\Scraper\Strategies;

use App\DTO\JobSourceDTO;
use App\Jobs\SaveSourcesJob;
use App\Services\Scraper\AbstractScraper;
use App\Services\Scraper\Contracts\ParserInterface;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class SimplyHired extends AbstractScraper
{
    public function __construct(ParserInterface $parser)
    {
        parent::__construct($parser);
        Log::info('SimplyHired scraper initialized');
    }

    public function scrape(string $url): array
    {
        Log::info('Starting SimplyHired scraping', ['url' => $url]);

        if (!$this->validate($url)) {
            throw new \InvalidArgumentException('Invalid SimplyHired URL');
        }

        try {
            // Use pagination to scrape multiple pages
            $results = $this->scrapeWithPagination($url);

            Log::info('Scraping completed', [
                'results_count' => count($results)
            ]);

            return $results;
        } catch (\Throwable $e) {
            Log::error('Scraping failed', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Extract the next page URL from SimplyHired pagination
     */
    protected function extractNextPageUrl(string $html): ?string
    {
        try {
            $crawler = new Crawler($html);
            $nextPageLink = $crawler->filter('a[data-testid="pageNumberBlockNext"]');

            if ($nextPageLink->count() > 0) {
                $href = $nextPageLink->attr('href');

                // If the href is a relative URL, convert it to absolute
                if (strpos($href, 'http') !== 0) {
                    $href = 'https://www.simplyhired.com' . $href;
                }

                Log::info('Found next page URL', ['url' => $href]);
                return $href;
            }
        } catch (\Exception $e) {
            Log::warning('Error extracting next page URL', [
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    public function validate(mixed $data): bool
    {
        if (is_string($data)) {
            $isValid = !empty($data) && str_contains($data, 'simplyhired.com');
            Log::info('URL validation', [
                'url' => $data,
                'is_valid' => $isValid
            ]);
            return $isValid;
        }

        if (is_array($data)) {
            $isValid = !empty($data['title']) &&
                !empty($data['url']) &&
                $data['title'] !== 'Apply Now' &&
                str_contains($data['url'], 'simplyhired.com');

            Log::info('Job data validation', [
                'title' => $data['title'] ?? 'N/A',
                'url' => $data['url'] ?? 'N/A',
                'is_valid' => $isValid
            ]);

            return $isValid;
        }

        return false;
    }

    public function transform(array $data): array
    {
        Log::info('Transforming job data', [
            'original_data_keys' => array_keys($data)
        ]);

        $result = [
            'title' => $data['title'],
            'source' => 'simply-hired',
            'source_url' => $data['url']
        ];

        SaveSourcesJob::dispatchSync(new JobSourceDTO(
            $result['title'],
            $result['source_url'],
            $result['source']
        ));
        Log::debug('Transformed job', [
            'title' => $result['title'],
            'source_url' => $result['source_url']
        ]);

        return $result;
    }
}

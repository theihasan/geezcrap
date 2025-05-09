<?php declare(strict_types=1);

namespace App\Services\Scraper;

use App\Services\Scraper\Contracts\ParserInterface;
use App\Services\Scraper\Contracts\ScraperInterface;
use Spatie\Browsershot\Browsershot;
use Spatie\Browsershot\Exceptions\CouldNotTakeBrowsershot;
use Illuminate\Support\Facades\Log;

abstract class AbstractScraper implements ScraperInterface
{
    protected Browsershot $browser;
    protected int $maxPages = 3; // Default to scrape 3 pages

    public function __construct(protected ParserInterface $parser)
    {
        $this->browser = new Browsershot();
        $this->configureBrowser();
    }

    protected function configureBrowser(): void
    {
        $this->browser
            ->waitUntilNetworkIdle()
            ->dismissDialogs()
            ->ignoreHttpsErrors()
            ->timeout(90)
            ->windowSize(1920, 1080)
            ->noSandbox()
            ->setDelay(3000);
    }

    protected function getHtml(string $url): string
    {
        if (empty($url)) {
            throw new \InvalidArgumentException('URL cannot be empty');
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid URL format');
        }

        try {
            Log::debug('Initializing browser instance', ['url' => $url]);

            $browsershot = Browsershot::url($url);

            $browsershot
                ->waitUntilNetworkIdle()
                ->dismissDialogs()
                ->ignoreHttpsErrors()
                ->timeout(90)
                ->windowSize(1920, 1080)
                ->noSandbox()
                ->setDelay(3000)
                ->setExtraHttpHeaders([
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36'
                ])
                ->addChromiumArguments([
                    '--no-sandbox',
                    '--disable-setuid-sandbox',
                    '--disable-dev-shm-usage'
                ]);

            Log::debug('Fetching page content', ['url' => $url]);

            $html = $browsershot->bodyHtml();

            if (app()->environment('local')) {

                $timestamp = now()->format('Y-m-d_H-i-s');
                $filename = 'page_content_' . $timestamp;

                file_put_contents(
                    storage_path("logs/{$filename}.html"),
                    $html
                );

                $browsershot->save(storage_path("logs/{$filename}.jpg"));
            }

            return $html;

        } catch (\Throwable $e) {
            Log::error('Browser error', [
                'url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new CouldNotTakeBrowsershot(
                "Failed to fetch page content: {$e->getMessage()}",
                previous: $e
            );
        }
    }

    /**
     * Scrape multiple pages with pagination
     *
     * @param string $baseUrl The initial URL to scrape
     * @param int|null $maxPages Maximum number of pages to scrape
     * @return array Combined results from all pages
     */
    protected function scrapeWithPagination(string $baseUrl, int $maxPages = null): array
    {
        $maxPages = $maxPages ?? $this->maxPages;
        $allResults = [];
        $currentUrl = $baseUrl;
        $currentPage = 1;

        Log::info("Starting pagination scraping", [
            'base_url' => $baseUrl,
            'max_pages' => $maxPages
        ]);

        while ($currentPage <= $maxPages) {
            Log::info("Scraping page {$currentPage} of {$maxPages}", ['url' => $currentUrl]);

            // Get the HTML content of the current page
            $html = $this->getHtml($currentUrl);

            // Parse the current page and add to results
            $pageResults = $this->parser->parse($html);
            $allResults = array_merge($allResults, $pageResults);

            Log::info("Page {$currentPage} results", ['count' => count($pageResults)]);

            // Stop if we've reached the max pages
            if ($currentPage >= $maxPages) {
                break;
            }

            // Find the next page URL
            $nextPageUrl = $this->extractNextPageUrl($html);

            // If no next page URL found, break the loop
            if (empty($nextPageUrl)) {
                Log::info("No next page URL found, stopping pagination", ['current_page' => $currentPage]);
                break;
            }

            $currentUrl = $nextPageUrl;
            $currentPage++;
        }

        Log::info("Pagination scraping completed", ['total_results' => count($allResults)]);
        return $allResults;
    }

    /**
     * Extract the next page URL from the HTML content
     * To be implemented by child classes
     *
     * @param string $html The HTML content
     * @return string|null The next page URL or null if not found
     */
    protected function extractNextPageUrl(string $html): ?string
    {
        return null;
    }

    abstract public function scrape(string $url): array;
}

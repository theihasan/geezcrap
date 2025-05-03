<?php declare(strict_types=1);

namespace App\Services\Scraper;

use App\Services\Scraper\Contracts\ParserInterface;
use App\Services\Scraper\Contracts\ScraperInterface;
use Spatie\Browsershot\Browsershot;

abstract class AbstractScraper implements ScraperInterface
{
    public function __construct(protected Browsershot $browser, protected ParserInterface $parser)
    {
        $this->browser = new Browsershot();
    }

    abstract public function scrape(string $url): array;

    protected function getHtml(string $url): string
    {
        return $this->browser
            ->url($url)
            ->waitUntilNetworkIdle()
            ->bodyHtml();
    }
}

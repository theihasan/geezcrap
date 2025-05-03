<?php declare(strict_types=1);

namespace App\Services\Scraper\Factory;

use App\Services\Scraper\AbstractScraper;
use App\Services\Scraper\Contracts\ScraperInterface;
use App\Services\Scraper\Parsers\DOMParser;
use App\Services\Scraper\Strategies\SimplyHired;

class ScraperFactory
{
    public function create(string $source) : AbstractScraper
    {
        return match ($source) {
            'simply-hired' => new SimplyHired(new DOMParser()),
            default => throw new \InvalidArgumentException('Invalid source'),
        };
    }
}

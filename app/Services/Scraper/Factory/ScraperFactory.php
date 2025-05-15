<?php declare(strict_types=1);

namespace App\Services\Scraper\Factory;

use App\Services\Scraper\AbstractScraper;
use App\Services\Scraper\Parsers\DOMParser;
use App\Services\Scraper\Strategies\EasyJobAI;
use App\Services\Scraper\Strategies\SimplyHired;
use App\Services\Scraper\Parsers\EasyJobAIParser;
use App\Services\Scraper\Contracts\ScraperInterface;

class ScraperFactory
{
    public function create(string $source) : AbstractScraper
    {
        return match ($source) {
            'simply-hired' => new SimplyHired(new DOMParser()),
            'easy-job-ai' => new EasyJobAI(new EasyJobAIParser()),
            default => throw new \InvalidArgumentException('Invalid source'),
        };
    }
}

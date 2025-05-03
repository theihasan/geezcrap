<?php declare(strict_types=1);

namespace App\Services\Scraper\Strategies;

use App\Services\Scraper\AbstractScraper;
use App\Services\Scraper\Contracts\ParserInterface;
use Spatie\Browsershot\Browsershot;

class SimplyHired extends AbstractScraper
{
    public function __construct(ParserInterface $parser)
    {
        parent::__construct(new Browsershot(), $parser);
    }

    public function scrape(string $url): array
    {
        $html = $this->getHtml($url);
        return $this->parser->parse($html);
    }

    public function validate(string $url): bool
    {
        return str_contains($url, 'simplyhired.com');
    }

    public function transform(array $data): array
    {
        return array_map(function($job) {
            return [
                'title' => $job['title'],
                'company' => $job['company'],
                'location' => $job['location'],
                'description' => $job['description'],
                'source' => 'simplyhired',
                'source_url' => $job['url'] ?? null,
                'scraped_at' => now(),
            ];
        }, $data);
    }
}

<?php declare(strict_types=1);

namespace App\Services\Scraper\Parsers;

use App\Services\Scraper\Contracts\ParserInterface;
use Symfony\Component\DomCrawler\Crawler;

class DOMParser implements ParserInterface
{
    public function parse(string $html): array
    {
        $crawler = new Crawler($html);


        return $crawler->filter('.job-listing')->each(function (Crawler $node) {
            return [
                'title' => $node->filter('.job-title')->text(''),
                'company' => $node->filter('.company-name')->text(''),
                'location' => $node->filter('.location')->text(''),
                'description' => $node->filter('.job-description')->text(''),
            ];
        });
    }
}

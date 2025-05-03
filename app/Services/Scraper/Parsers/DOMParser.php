<?php declare(strict_types=1);

namespace App\Services\Scraper\Parsers;

use App\Services\Scraper\Contracts\ParserInterface;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class DOMParser implements ParserInterface
{
    public function parse(string $html): array
    {
        Log::info('Starting DOM parsing', [
            'html_length' => strlen($html)
        ]);

        $crawler = new Crawler($html);
        $jobElements = $crawler->filter('.job-listing');

        Log::info('Found job listings', [
            'count' => $jobElements->count()
        ]);

        $results = $jobElements->each(function (Crawler $node, $i) {
            try {
                $job = [
                    'title' => $node->filter('.job-title')->text(''),
                    'company' => $node->filter('.company-name')->text(''),
                    'location' => $node->filter('.location')->text(''),
                    'description' => $node->filter('.job-description')->text(''),
                ];

                Log::debug('Parsed job listing', [
                    'index' => $i,
                    'title' => $job['title']
                ]);

                return $job;
            } catch (\Exception $e) {
                Log::error('Error parsing job element', [
                    'index' => $i,
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        });

        $results = array_filter($results);

        Log::info('Parsing completed', [
            'successful_parses' => count($results)
        ]);

        return $results;
    }
}

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

        $jobElements = $crawler->filter('.chakra-button[data-mdref]');

        Log::info('Found job listings', [
            'count' => $jobElements->count()
        ]);

        $results = $jobElements->each(function (Crawler $node, $i) {
            try {
                $title = $node->text();
                $href = $node->attr('data-mdref');

                if (empty($title) || empty($href)) {
                    Log::warning('Missing required data', [
                        'index' => $i,
                        'has_title' => !empty($title),
                        'has_href' => !empty($href)
                    ]);
                    return null;
                }

                $job = [
                    'title' => $title,
                    'url' => 'https://www.simplyhired.com' . $href,
                    'source_id' => str_replace('/job/', '', $href)
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

        if (empty($results)) {
            Log::warning('No jobs were successfully parsed', [
                'selectors_used' => [
                    'job_container' => '.chakra-button[data-mdref]'
                ]
            ]);
        }

        return $results;
    }
}

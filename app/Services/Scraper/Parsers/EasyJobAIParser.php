<?php declare(strict_types=1);

namespace App\Services\Scraper\Parsers;

use App\Services\Scraper\Contracts\ParserInterface;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class EasyJobAIParser implements ParserInterface
{
    public function parse(string $html): array
    {
        Log::info('Starting EasyJobAI DOM parsing', [
            'html_length' => strlen($html)
        ]);

        $crawler = new Crawler($html);
        $jobElements = $crawler->filter('li.group.relative');

        Log::info('Found job listings', [
            'count' => $jobElements->count()
        ]);

        $results = $jobElements->each(function (Crawler $node, $i) {
            try {
                $title = $node->filter('h3')->text();
                $href = $node->filter('a')->attr('href');

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
                    'url' => 'https://easyjobai.com' . $href,
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

        return array_filter($results);
    }
}
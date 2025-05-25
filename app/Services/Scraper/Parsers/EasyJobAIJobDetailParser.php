<?php declare(strict_types=1);

namespace App\Services\Scraper\Parsers;

use App\Services\Scraper\Contracts\ParserInterface;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class EasyJobAIJobDetailParser implements ParserInterface
{
    public function parse(string $html): array
    {
        $crawler = new Crawler($html);
        $jobData = [];

        try {
            $jobTitle = $this->extractJobTitle($crawler);
            $jobData['title'] = $jobTitle;

            $applyLink = $this->extractApplyLink($crawler);
            $jobData['apply_link'] = $applyLink;

            $location = $this->extractLocation($crawler);
            $jobData['location'] = $location;

            $skills = $this->extractSkills($crawler);
            if (!empty($skills)) {
                $jobData['skills'] = $skills;
            }

            return $jobData;

        } catch (\Exception $e) {
            Log::error('Error parsing EasyJobAI job details', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $jobData;
        }
    }

    private function extractJobTitle(Crawler $crawler): ?string
    {
        try {
            return $crawler->filter('h1.w-2/3.font-bold.text-base.text-foreground.tracking-wide')->text();
        } catch (\Exception $e) {
            Log::warning('Failed to extract job title', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function extractApplyLink(Crawler $crawler): ?string
    {
        try {
            // Method 1: Target the exact structure of the Apply button
            $applyLink = $crawler->filter('a.cursor-pointer[target="_blank"][rel="noopener nofollow"][data-slot="tooltip-trigger"]:contains("Apply")');
            
            if ($applyLink->count() > 0) {
                $href = $applyLink->attr('href');
                Log::info('Found apply link by exact structure', ['href' => $href]);
                return $href;
            }
            
            // Method 2: More specific selection targeting the parent container structure
            $applyLink = $crawler->filter('div.w-full.flex-1 a[target="_blank"]');
            
            if ($applyLink->count() > 0) {
                $href = $applyLink->attr('href');
                Log::info('Found apply link by parent structure', ['href' => $href]);
                return $href;
            }
            
            // Method 3: Find the link by the unique "Apply" text with SVG child
            $applyLinks = [];
            $crawler->filter('a')->each(function (Crawler $node) use (&$applyLinks) {
                // Check if the text contains "Apply" and has an SVG child
                $text = trim($node->text());
                if (
                    (stripos($text, 'Apply') !== false) && 
                    $node->filter('svg.lucide-square-arrow-out-up-right')->count() > 0
                ) {
                    $applyLinks[] = $node->attr('href');
                }
            });
            
            if (!empty($applyLinks)) {
                Log::info('Found apply link by text and SVG child', ['href' => $applyLinks[0]]);
                return $applyLinks[0];
            }
            
            // Method 4: Try to find links to job application sites
            $jobSiteLinks = [];
            $crawler->filter('a[href*="jobs.lever.co"], a[href*="workday"], a[href*="greenhouse.io"], a[href*="careers"], a[href*="job"]')->each(function (Crawler $node) use (&$jobSiteLinks) {
                if ($href = $node->attr('href')) {
                    // Filter out email links
                    if (strpos($href, 'mailto:') === false) {
                        $jobSiteLinks[] = trim($href);
                    }
                }
            });
            
            if (!empty($jobSiteLinks)) {
                Log::info('Found apply link by job site domains', ['href' => $jobSiteLinks[0]]);
                return $jobSiteLinks[0];
            }
            
            // Fallback: Log all non-mailto links for debugging
            $links = [];
            $crawler->filter('a[href]:not([href^="mailto:"])')->each(function (Crawler $node) use (&$links) {
                if ($href = $node->attr('href')) {
                    $text = trim($node->text());
                    $links[$text] = trim($href);
                }
            });
            
            Log::info('All non-mailto links found on page', ['links' => $links]);
            
            // If we've gotten this far without finding a match, look for any non-mailto link
            // that contains job-related terms in the URL
            foreach ($links as $text => $href) {
                if (
                    strpos($href, 'apply') !== false || 
                    strpos($href, 'job') !== false || 
                    strpos($href, 'career') !== false
                ) {
                    Log::info('Found potential apply link by URL keywords', ['href' => $href]);
                    return $href;
                }
            }
            
            return null;
        } catch (\Exception $e) {
            Log::warning('Failed to extract apply link', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return null;
        }
    }

    private function extractLocation(Crawler $crawler): ?string
    {
        try {
            return $crawler->filter('#overview .text-muted-foreground.text-sm')->text();
        } catch (\Exception $e) {
            Log::warning('Failed to extract location', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function extractSkills(Crawler $crawler): array
    {
        try {
            $skills = [];
            $crawler->filter('#overview [data-slot="badge"]')->each(function (Crawler $node) use (&$skills) {
                $skills[] = $node->text();
            });
            return $skills;
        } catch (\Exception $e) {
            Log::warning('Failed to extract skills', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
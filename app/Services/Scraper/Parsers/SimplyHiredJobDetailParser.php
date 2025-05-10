<?php declare(strict_types=1);

namespace App\Services\Scraper\Parsers;

use App\Services\Scraper\Contracts\ParserInterface;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class SimplyHiredJobDetailParser implements ParserInterface
{
    public function parse(string $html): array
    {
        Log::info('Starting SimplyHired job detail parsing', [
            'html_length' => strlen($html)
        ]);

        $crawler = new Crawler($html);
        $jobData = [];

        try {
            // Extract job title
            $jobTitle = $this->extractJobTitle($crawler);
            $jobData['title'] = $jobTitle;

            // Extract company name
            $companyName = $this->extractCompanyName($crawler);
            $jobData['company'] = $companyName;

            // Extract location
            $location = $this->extractLocation($crawler);
            $jobData['location'] = $location;

            // Extract salary information
            $salary = $this->extractSalary($crawler);
            if ($salary) {
                $jobData['salary'] = $salary;
            }

            // Extract job type
            $jobType = $this->extractJobType($crawler);
            if ($jobType) {
                $jobData['job_type'] = $jobType;
            }

            // Extract benefits
            $benefits = $this->extractBenefits($crawler);
            if (!empty($benefits)) {
                $jobData['benefits'] = $benefits;
            }

            // Extract qualifications
            $qualifications = $this->extractQualifications($crawler);
            if (!empty($qualifications)) {
                $jobData['qualifications'] = $qualifications;
            }

            // Extract full job description
            $description = $this->extractJobDescription($crawler);
            if ($description) {
                $jobData['description'] = $description;
            }

            // Extract company logo URL if available
            $logoUrl = $this->extractCompanyLogo($crawler);
            if ($logoUrl) {
                $jobData['company_logo'] = $logoUrl;
            }

            // Extract apply link
            $applyLink = $this->extractApplyLink($crawler);
            if ($applyLink) {
                $jobData['apply_link'] = $applyLink;
            }

            Log::info('Successfully parsed job details', [
                'title' => $jobData['title'] ?? 'N/A',
                'company' => $jobData['company'] ?? 'N/A',
                'fields_extracted' => count($jobData)
            ]);

            return $jobData;

        } catch (\Exception $e) {
            Log::error('Error parsing job details', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return whatever data we were able to extract
            return $jobData;
        }
    }

    private function extractJobTitle(Crawler $crawler): ?string
    {
        try {
            return $crawler->filter('[data-testid="viewJobTitle"]')->text();
        } catch (\Exception $e) {
            Log::warning('Failed to extract job title', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function extractCompanyName(Crawler $crawler): ?string
    {
        try {
            return $crawler->filter('[data-testid="viewJobCompanyName"] [data-testid="detailText"]')->text();
        } catch (\Exception $e) {
            Log::warning('Failed to extract company name', ['error' => $e->getMessage()]);
            return null;
        }
    }
    private function extractLocation(Crawler $crawler): ?string
    {
        try {
            return $crawler->filter('[data-testid="viewJobCompanyLocation"] [data-testid="detailText"]')->text();
        } catch (\Exception $e) {
            Log::warning('Failed to extract location', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function extractSalary(Crawler $crawler): ?string
    {
        try {
            return $crawler->filter('[data-testid="viewJobSalary"] [data-testid="detailText"]')->text();
        } catch (\Exception $e) {
            Log::warning('Failed to extract salary', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function extractJobType(Crawler $crawler): ?string
    {
        try {
            return $crawler->filter('[data-testid="viewJobJobType"] [data-testid="detailText"]')->text();
        } catch (\Exception $e) {
            Log::warning('Failed to extract job type', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function extractBenefits(Crawler $crawler): array
    {
        try {
            $benefits = [];
            $benefitsSection = $crawler->filter('[data-testid="viewJobBenefits"]');

            if ($benefitsSection->count() > 0) {
                $benefitItems = $benefitsSection->filter('li');
                $benefits = $benefitItems->each(function (Crawler $node) {
                    return trim($node->text());
                });
            }

            return array_filter($benefits);
        } catch (\Exception $e) {
            Log::warning('Failed to extract benefits', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function extractQualifications(Crawler $crawler): array
    {
        try {
            $qualifications = [];

            // Try to find a qualifications section
            $qualificationsSection = $crawler->filter('.viewjob-section:contains("Qualifications")');

            if ($qualificationsSection->count() > 0) {
                $listItems = $qualificationsSection->filter('ul li');

                if ($listItems->count() > 0) {
                    $qualifications = $listItems->each(function (Crawler $node) {
                        return trim($node->text());
                    });
                }
            }

            // If no specific qualifications section, try to extract from the job description
            if (empty($qualifications)) {
                $descriptionSection = $crawler->filter('[data-testid="viewJobDescription"]');

                if ($descriptionSection->count() > 0) {
                    $listItems = $descriptionSection->filter('ul li');

                    if ($listItems->count() > 0) {
                        // Take the first few list items as potential qualifications
                        $allItems = $listItems->each(function (Crawler $node) {
                            return trim($node->text());
                        });

                        // Filter items that might be qualifications (containing keywords)
                        $qualificationKeywords = ['experience', 'degree', 'skill', 'knowledge', 'proficiency', 'ability'];

                        foreach ($allItems as $item) {
                            foreach ($qualificationKeywords as $keyword) {
                                if (stripos($item, $keyword) !== false) {
                                    $qualifications[] = $item;
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            return array_filter($qualifications);
        } catch (\Exception $e) {
            Log::warning('Failed to extract qualifications', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function extractJobDescription(Crawler $crawler): ?string
    {
        try {
            $descriptionNode = $crawler->filter('[data-testid="viewJobDescription"]');

            if ($descriptionNode->count() > 0) {
                // Get the HTML content of the description
                return $descriptionNode->html();
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Failed to extract job description', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function extractCompanyLogo(Crawler $crawler): ?string
    {
        try {
            $logoNode = $crawler->filter('[data-testid="viewJobCompanyLogo"] img');

            if ($logoNode->count() > 0) {
                return $logoNode->attr('src');
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Failed to extract company logo', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function extractApplyLink(Crawler $crawler): ?string
    {
        try {
            $applyButton = $crawler->filter('[data-testid="viewJobApplyButton"]');

            if ($applyButton->count() > 0) {
                return $applyButton->attr('href');
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Failed to extract apply link', ['error' => $e->getMessage()]);
            return null;
        }
    }
}


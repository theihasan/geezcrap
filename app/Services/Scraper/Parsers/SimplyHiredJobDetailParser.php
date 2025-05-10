<?php declare(strict_types=1);

namespace App\Services\Scraper\Parsers;

use App\Services\Scraper\Contracts\ParserInterface;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class SimplyHiredJobDetailParser implements ParserInterface
{
    public function parse(string $html): array
    {
        $crawler = new Crawler($html);
        $jobData = [];

        try {
            $jobTitle = $this->extractJobTitle($crawler);
            $jobData['title'] = $jobTitle;

            $companyName = $this->extractCompanyName($crawler);
            $jobData['company'] = $companyName;

            $location = $this->extractLocation($crawler);
            $jobData['location'] = $location;

            $salary = $this->extractSalary($crawler);
            if ($salary) {
                $jobData['salary'] = $salary;
            }

            $jobType = $this->extractJobType($crawler);
            if ($jobType) {
                $jobData['job_type'] = $jobType;
            }

            $benefits = $this->extractBenefits($crawler);
            if (!empty($benefits)) {
                $jobData['benefits'] = $benefits;
            }

            $qualifications = $this->extractQualifications($crawler);
            if (!empty($qualifications)) {
                $jobData['qualifications'] = $qualifications;
            }

            $description = $this->extractJobDescription($crawler);
            if ($description) {
                $jobData['description'] = $description;
            }

            $logoUrl = $this->extractCompanyLogo($crawler);
            if ($logoUrl) {
                $jobData['company_logo'] = $logoUrl;
            }

            $applyLink = $this->extractApplyLink($crawler);
            if ($applyLink) {
                $jobData['apply_link'] = $applyLink;
            }

            return $jobData;

        } catch (\Exception $e) {
            Log::error('Error parsing job details', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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
            return $crawler->filter('[data-testid="viewJobBodyJobCompensation"] [data-testid="detailText"]')->text();
        } catch (\Exception $e) {
            Log::warning('Failed to extract salary', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function extractJobType(Crawler $crawler): ?string
    {
        try {
            return $crawler->filter('[data-testid="viewJobBodyJobDetailsJobType"] [data-testid="detailText"]')->text();
        } catch (\Exception $e) {
            Log::warning('Failed to extract job type', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function extractBenefits(Crawler $crawler): array
    {
        try {
            $benefits = [];
            $benefitsSection = $crawler->filter('[data-testid="viewJobBodyJobBenefits"]');

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

            $qualificationsSection = $crawler->filter('.chakra-wrap');

            if ($qualificationsSection->count() > 0) {
                $listItems = $qualificationsSection->filter('ul li');

                if ($listItems->count() > 0) {
                    $qualifications = $listItems->each(function (Crawler $node) {
                        return trim($node->text());
                    });
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
            $descriptionNode = $crawler->filter('.css-cxpe4v');
            if ($descriptionNode->count() > 0) {
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
            $logoNode = $crawler->filter('[data-testid="companyVJLogo"] img');

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


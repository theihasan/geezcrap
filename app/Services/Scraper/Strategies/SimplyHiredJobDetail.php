<?php declare(strict_types=1);

namespace App\Services\Scraper\Strategies;

use App\DTO\JobDetailDTO;
use App\Jobs\SaveJobDetailsJob;
use App\Services\Scraper\AbstractScraper;
use App\Services\Scraper\Contracts\ParserInterface;
use Illuminate\Support\Facades\Log;

class SimplyHiredJobDetail extends AbstractScraper
{
    public function __construct(ParserInterface $parser)
    {
        parent::__construct($parser);
        Log::info('SimplyHiredJobDetail scraper initialized');
    }

    public function scrape(string $url): array
    {
        Log::info('Starting SimplyHiredJobDetail scraping', ['url' => $url]);

        if (!$this->validate($url)) {
            throw new \InvalidArgumentException('Invalid SimplyHired job detail URL');
        }

        try {
            $html = $this->getHtml($url);

            $jobDetails = $this->parser->parse($html);

            $jobDetails['source_url'] = $url;
            $jobDetails['source'] = 'simply-hired';

            $transformedData = $this->transform($jobDetails);

            Log::info('Job detail scraping completed', [
                'title' => $transformedData['job_title'] ?? 'N/A',
                'company' => $transformedData['employer_name'] ?? 'N/A'
            ]);

            return $transformedData;
        } catch (\Throwable $e) {
            Log::error('Job detail scraping failed', [
                'url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function validate(mixed $data): bool
    {
        if (is_string($data)) {
            $isValid = !empty($data) &&
                str_contains($data, 'simplyhired.com') &&
                str_contains($data, '/job/');

            Log::info('URL validation', [
                'url' => $data,
                'is_valid' => $isValid
            ]);

            return $isValid;
        }

        if (is_array($data)) {
            $isValid = !empty($data['title']) && !empty($data['company']);

            Log::info('Job detail data validation', [
                'title' => $data['title'] ?? 'N/A',
                'company' => $data['company'] ?? 'N/A',
                'is_valid' => $isValid
            ]);

            return $isValid;
        }

        return false;
    }

    public function transform(array $data): array
    {
        Log::info('Transforming job detail data', [
            'original_data_keys' => array_keys($data)
        ]);

        $minSalary = null;
        $maxSalary = null;
        $salaryPeriod = null;

        if (!empty($data['salary'])) {
            $this->parseSalaryInfo($data['salary'], $minSalary, $maxSalary, $salaryPeriod);
        }

        $isRemote = false;
        $location = $data['location'] ?? '';
        if (stripos($location, 'remote') !== false) {
            $isRemote = true;
        }

        $city = null;
        $state = null;
        $country = 'US';

        if (!empty($location)) {
            $this->parseLocation($location, $city, $state, $country);
        }

        $jobCategoryId = 1;


        $result = [
            'employer_name' => $data['company'] ?? '',
            'employer_logo' => $data['company_logo'] ?? null,
            'employer_website' => null,
            'publisher' => 'SimplyHired',
            'employment_type' => $data['job_type'] ?? null,
            'job_title' => $data['title'] ?? '',
            'job_category_id' => $jobCategoryId,
            'category_image' => '/images/categories/default.jpg',
            'apply_link' => $data['source_url'] ?? '',
            'description' => $data['description'] ?? '',
            'is_remote' => $isRemote,
            'city' => $city,
            'state' => $state,
            'country' => $country,
            'google_link' => null,
            'posted_at' => now()->subDays(rand(1, 14))->toDateTimeString(),
            'expired_at' => now()->addDays(30)->toDateTimeString(),
            'min_salary' => $minSalary,
            'max_salary' => $maxSalary,
            'salary_period' => $salaryPeriod,
            'benefits' => $data['benefits'] ?? [],
            'qualifications' => $data['qualifications'] ?? [],
            'responsibilities' => [],
        ];

        $this->dispatchSaveJob($result);

        Log::debug('Transformed job detail', [
            'job_title' => $result['job_title'],
            'employer_name' => $result['employer_name'],
            'location' => $result['city'] . ', ' . $result['state']
        ]);

        return $result;
    }

    /**
     * Parse salary information from a string like "$100,000 - $150,000 a year"
     */
    private function parseSalaryInfo(string $salaryText, ?float &$minSalary, ?float &$maxSalary, ?string &$salaryPeriod): void
    {
        try {
            $salaryText = trim($salaryText);

            if (stripos($salaryText, 'year') !== false) {
                $salaryPeriod = 'yearly';
            } elseif (stripos($salaryText, 'month') !== false) {
                $salaryPeriod = 'monthly';
            } elseif (stripos($salaryText, 'week') !== false) {
                $salaryPeriod = 'weekly';
            } elseif (stripos($salaryText, 'hour') !== false || stripos($salaryText, 'hourly') !== false) {
                $salaryPeriod = 'hourly';
            } else {
                $salaryPeriod = 'yearly'; // Default
            }

            preg_match_all('/\$([0-9,.]+)/', $salaryText, $matches);

            if (!empty($matches[1])) {
                $numbers = array_map(function($num) {
                    return (float) str_replace([',', '$'], '', $num);
                }, $matches[1]);


                sort($numbers);

                if (count($numbers) >= 1) {
                    $minSalary = $numbers[0];
                }

                if (count($numbers) >= 2) {
                    $maxSalary = $numbers[1];
                } else {
                    $maxSalary = $minSalary;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to parse salary information', [
                'salary_text' => $salaryText,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Parse location information from a string like "New York, NY"
     */
    private function parseLocation(string $locationText, ?string &$city, ?string &$state, ?string &$country): void
    {
        try {
            $locationText = trim($locationText);

            if (strtolower($locationText) === 'remote') {
                $city = 'Remote';
                $state = null;
                return;
            }

            if (strpos($locationText, ',') !== false) {
                list($cityPart, $statePart) = explode(',', $locationText, 2);
                $city = trim($cityPart);
                $state = trim($statePart);


                if (strlen($state) > 2) {
                    $country = $state;
                    $state = null;
                }
            } else {
                $city = $locationText;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to parse location information', [
                'location_text' => $locationText,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Dispatch job to save the data
     */
    private function dispatchSaveJob(array $jobData): void
    {
        try {
            SaveJobDetailsJob::dispatchSync(new JobDetailDTO(
                employerName: $jobData['employer_name'],
                employerLogo: $jobData['employer_logo'],
                employerWebsite: $jobData['employer_website'],
                publisher: $jobData['publisher'],
                employmentType: $jobData['employment_type'],
                jobTitle: $jobData['job_title'],
                jobCategory: $jobData['job_category_id'],
                categoryImage: $jobData['category_image'],
                applyLink: $jobData['apply_link'],
                description: $jobData['description'],
                isRemote: $jobData['is_remote'],
                city: $jobData['city'],
                state: $jobData['state'],
                country: $jobData['country'],
                googleLink: $jobData['google_link'],
                postedAt: $jobData['posted_at'],
                expiredAt: $jobData['expired_at'],
                minSalary: $jobData['min_salary'],
                maxSalary: $jobData['max_salary'],
                salaryPeriod: $jobData['salary_period'],
                benefits: $jobData['benefits'],
                qualifications: $jobData['qualifications'],
                responsibilities: $jobData['responsibilities']
            ));

            Log::info('Job save dispatched successfully');
        } catch (\Exception $e) {
            Log::error('Failed to dispatch job save', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function extractNextPageUrl(string $html): ?string
    {
        return null;
    }
}

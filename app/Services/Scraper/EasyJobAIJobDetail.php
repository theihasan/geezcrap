<?php declare(strict_types=1);

namespace App\Services\Scraper;

use App\DTO\JobDetailDTO;
use App\Jobs\SaveJobDetailsJob;
use Illuminate\Support\Facades\Log;
use App\Services\Scraper\AbstractScraper;
use App\Services\Scraper\Contracts\ParserInterface;
use App\Services\Scraper\Contracts\ScraperInterface;
use App\Services\Scraper\Parsers\EasyJobAIJobDetailParser;

class EasyJobAIJobDetail extends AbstractScraper implements ScraperInterface
{
    protected string $source = 'easy-job-ai';
    protected string $type = 'job-detail';
    
    public function __construct(ParserInterface $parser)
    {
        parent::__construct($parser);
    }

    public function scrape(string $url): array
    {
        try {
            $html = $this->getHtml($url);
            $jobDetails = $this->parser->parse($html);
            $jobDetails['source_url'] = $url;
            $jobDetails['source'] = $this->source;

            $transformedData = $this->transform($jobDetails);
            $this->dispatchSaveJob($transformedData);

            return $transformedData;
        } catch (\Exception $e) {
            Log::error('Error scraping EasyJobAI job details', [
                'error' => $e->getMessage(),
                'url' => $url
            ]);
            return [];
        }
    }

    public function validate(string $url): bool
    {
        return str_starts_with($url, 'https://easyjobai.com/jobs/');
    }

    public function transform(array $data): array
    {
        $isRemote = false;
        $city = null;
        $state = null;
        $country = 'US';

        if (!empty($data['location'])) {
            $location = trim($data['location']);
            if (strtolower($location) === 'remote') {
                $isRemote = true;
                $city = 'Remote';
            } else if (strpos($location, ',') !== false) {
                list($cityPart, $statePart) = explode(',', $location, 2);
                $city = trim($cityPart);
                $state = trim($statePart);
            } else {
                $city = $location;
            }
        }

        // Extract apply link from the data
        $applyLink = '';
        if (!empty($data['apply_link'])) {
            // Remove any extra whitespace and quotes
            $applyLink = trim($data['apply_link'], " \t\n\r\0\x0B'\"");
        }

        return [
            'employer_name' => $data['company'] ?? '',
            'employer_logo' => $data['company_logo'] ?? null,
            'employer_website' => null,
            'publisher' => 'EasyJobAI',
            'employment_type' => $data['job_type'] ?? null,
            'job_title' => $data['title'] ?? '',
            'job_category_id' => 1,
            'category_image' => '/images/categories/default.jpg',
            'apply_link' => $applyLink,
            'description' => $data['description'] ?? '',
            'is_remote' => $isRemote,
            'city' => $city,
            'state' => $state,
            'country' => $country,
            'google_link' => null,
            'posted_at' => now()->toDateTimeString(),
            'expired_at' => now()->addDays(30)->toDateTimeString(),
            'min_salary' => null,
            'max_salary' => null,
            'salary_period' => null,
            'benefits' => [],
            'qualifications' => $data['skills'] ?? [],
            'responsibilities' => []
        ];
    }

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
}
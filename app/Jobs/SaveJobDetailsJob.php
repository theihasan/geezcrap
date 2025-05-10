<?php declare(strict_types=1);

namespace App\Jobs;

use App\DTO\JobDetailDTO;
use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SaveJobDetailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly JobDetailDTO $jobDetail
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Saving job details', [
                'title' => $this->jobDetail->jobTitle,
                'company' => $this->jobDetail->employerName
            ]);

            // Check if the job already exists to avoid duplicates
            $existingJob = Job::where('job_title', $this->jobDetail->jobTitle)
                ->where('employer_name', $this->jobDetail->employerName)
                ->where('apply_link', $this->jobDetail->applyLink)
                ->first();

            if ($existingJob) {
                Log::info('Job already exists, updating', ['id' => $existingJob->id]);

                $existingJob->update([
                    'employer_logo' => $this->jobDetail->employerLogo,
                    'employer_website' => $this->jobDetail->employerWebsite,
                    'publisher' => $this->jobDetail->publisher,
                    'employment_type' => $this->jobDetail->employmentType,
                    'job_category_id' => $this->jobDetail->jobCategory,
                    'category_image' => $this->jobDetail->categoryImage,
                    'description' => $this->jobDetail->description,
                    'is_remote' => $this->jobDetail->isRemote,
                    'city' => $this->jobDetail->city,
                    'state' => $this->jobDetail->state,
                    'country' => $this->jobDetail->country,
                    'google_link' => $this->jobDetail->googleLink,
                    'posted_at' => $this->jobDetail->postedAt,
                    'expired_at' => $this->jobDetail->expiredAt,
                    'min_salary' => $this->jobDetail->minSalary,
                    'max_salary' => $this->jobDetail->maxSalary,
                    'salary_period' => $this->jobDetail->salaryPeriod,
                    'benefits' => $this->jobDetail->benefits,
                    'qualifications' => $this->jobDetail->qualifications,
                    'responsibilities' => $this->jobDetail->responsibilities,
                ]);

                Log::info('Job updated successfully', ['id' => $existingJob->id]);
            } else {
                // Create a new job record
                $job = Job::create([
                    'employer_name' => $this->jobDetail->employerName,
                    'employer_logo' => $this->jobDetail->employerLogo,
                    'employer_website' => $this->jobDetail->employerWebsite,
                    'publisher' => $this->jobDetail->publisher,
                    'employment_type' => $this->jobDetail->employmentType,
                    'job_title' => $this->jobDetail->jobTitle,
                    'job_category_id' => $this->jobDetail->jobCategory,
                    'category_image' => $this->jobDetail->categoryImage,
                    'apply_link' => $this->jobDetail->applyLink,
                    'description' => $this->jobDetail->description,
                    'is_remote' => $this->jobDetail->isRemote,
                    'city' => $this->jobDetail->city,
                    'state' => $this->jobDetail->state,
                    'country' => $this->jobDetail->country,
                    'google_link' => $this->jobDetail->googleLink,
                    'posted_at' => $this->jobDetail->postedAt,
                    'expired_at' => $this->jobDetail->expiredAt,
                    'min_salary' => $this->jobDetail->minSalary,
                    'max_salary' => $this->jobDetail->maxSalary,
                    'salary_period' => $this->jobDetail->salaryPeriod,
                    'benefits' => $this->jobDetail->benefits,
                    'qualifications' => $this->jobDetail->qualifications,
                    'responsibilities' => $this->jobDetail->responsibilities,
                ]);

                Log::info('Job created successfully', ['id' => $job->id]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to save job details', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
}

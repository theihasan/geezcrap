<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MigrateJobsData extends Command
{
    protected $signature = 'jobs:migrate-data {--timeout=120}';
    protected $description = 'Migrate data from SQLite jobs_details to MySQL job_listings table';

    public function handle()
    {
        $this->info('Starting jobs data migration...');

        try {
            // Get data from SQLite (current database)
            $jobs = DB::connection('sqlite')
                ->table('jobs_details')
                ->get();

            $this->info("Found {$jobs->count()} jobs to migrate.");

            $jobs->each(function ($job) {
                try {
                    $this->info("Processing job ID {$job->id}");
                    DB::connection('old_database')
                        ->table('job_listings')
                        ->insert([
                            'uuid' => Str::uuid()->toString(),
                            'employer_name' => $job->employer_name,
                            'employer_logo' => $job->employer_logo,
                            'employer_website' => $job->employer_website,
                            'employer_company_type' => null,
                            'publisher' => $job->publisher,
                            'employment_type' => $job->employment_type,
                            'job_title' => $job->job_title,
                            'slug' => Str::slug($job->job_title),
                            'job_category' => random_int(1,25),
                            'apply_link' => $job->apply_link,
                            'description' => strip_tags($job->description),
                            'is_remote' => $job->is_remote,
                            'city' => $job->city,
                            'state' => $job->state,
                            'country' => $job->country,
                            'google_link' => $job->google_link,
                            'posted_at' => $job->posted_at,
                            'expired_at' => $job->expired_at,  // Changed from expaire_at to expire_at
                            'min_salary' => $job->min_salary,
                            'max_salary' => $job->max_salary,
                            'salary_currency' => null,
                            'salary_period' => $job->salary_period,
                            'benefits' => $job->benefits,
                            'qualifications' => $job->qualifications,
                            'responsibilities' => $job->responsibilities,
                            'required_experience' => null,
                            'latitude' => null,
                            'longitude' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                    $this->info("Migrated job: {$job->job_title}");
                } catch (\Exception $e) {
                    $this->error("Failed to migrate job ID {$job->id}: " . $e->getMessage());
                }
            });

            $this->info('Jobs data migration completed successfully!');
        } catch (\Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            return 1;
        }
    }
}
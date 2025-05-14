<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDO;

class MigrateJobsData extends Command
{
    protected $signature = 'jobs:migrate-data {--timeout=300}';
    protected $description = 'Migrate data from SQLite jobs_details to MySQL job_listings table';

    public function handle()
    {
        $this->info('Starting jobs data migration...');

        config([
            'database.connections.old_database.options' => [
                PDO::ATTR_TIMEOUT => $this->option('timeout'),
                PDO::ATTR_PERSISTENT => false,
            ],
            'database.connections.old_database.sslmode' => 'require',
            'database.connections.old_database.ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ]
        ]);

        DB::purge('old_database');

        try {
            DB::connection('old_database')->getPdo();
            $this->info('Successfully connected to database');

            $jobs = DB::connection('sqlite')
                ->table('jobs_details')
                ->get();

            $this->info("Found {$jobs->count()} jobs to migrate.");
            $duplicateCount = 0;
            $successCount = 0;

            $jobs->each(function ($job) use (&$duplicateCount, &$successCount) {
                try {
                    $exists = DB::connection('old_database')
                        ->table('job_listings')
                        ->where('employer_name', $job->employer_name)
                        ->where('job_title', $job->job_title)
                        ->exists();

                    if ($exists) {
                        $this->warn("Skipping duplicate job: {$job->job_title} from {$job->employer_name}");
                        $duplicateCount++;
                        return;
                    }

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
                            'expired_at' => $job->expired_at,
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
                    $successCount++;
                } catch (\Exception $e) {
                    $this->error("Failed to migrate job ID {$job->id}: " . $e->getMessage());
                }
            });

            $this->info('Jobs data migration completed successfully!');
            $this->info("Summary:");
            $this->info("- Total jobs processed: " . $jobs->count());
            $this->info("- Successfully migrated: " . $successCount);
            $this->info("- Duplicates skipped: " . $duplicateCount);
        } catch (\Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            $this->info('Connection details:');
            $this->info('Host: ' . config('database.connections.old_database.host'));
            $this->info('Port: ' . config('database.connections.old_database.port'));
            $this->info('Database: ' . config('database.connections.old_database.database'));
            return 1;
        }
    }
}
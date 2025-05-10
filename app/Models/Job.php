<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use SoftDeletes;

    protected $table = 'jobs_details';

    protected $fillable = [
        'employer_name',
        'employer_logo',
        'employer_website',
        'publisher',
        'employment_type',
        'job_title',
        'job_category_id',
        'category_image',
        'apply_link',
        'description',
        'is_remote',
        'city',
        'state',
        'country',
        'google_link',
        'posted_at',
        'expired_at',
        'min_salary',
        'max_salary',
        'salary_period',
        'benefits',
        'qualifications',
        'responsibilities',
    ];

    protected $casts = [
        'is_remote' => 'boolean',
        'min_salary' => 'float',
        'max_salary' => 'float',
        'benefits' => 'array',
        'qualifications' => 'array',
        'responsibilities' => 'array',
        'posted_at' => 'datetime',
        'expired_at' => 'datetime',
    ];
    public function category()
    {
        return $this->belongsTo(JobCategory::class, 'job_category_id');
    }

}

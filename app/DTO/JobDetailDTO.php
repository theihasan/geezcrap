<?php declare(strict_types=1);

namespace App\DTO;

class JobDetailDTO
{
    public function __construct(
        public string  $employerName,
        public ?string $employerLogo,
        public ?string $employerWebsite,
        public string  $publisher,
        public ?string $employmentType,
        public string  $jobTitle,
        public int     $jobCategory,
        public string  $categoryImage,
        public string  $applyLink,
        public string  $description,
        public bool    $isRemote,
        public ?string $city,
        public ?string $state,
        public ?string $country,
        public ?string $googleLink,
        public ?string $postedAt,
        public ?string $expiredAt,
        public ?float  $minSalary,
        public ?float  $maxSalary,
        public ?string $salaryPeriod,
        public ?array  $benefits,
        public ?array  $qualifications,
        public ?array  $responsibilities,
    ) {}
}

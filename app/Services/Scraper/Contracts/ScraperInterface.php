<?php
declare(strict_types=1);

namespace App\Services\Scraper\Contracts;

interface ScraperInterface
{
    public function scrape(string $url): array;
    public function validate(string $url): bool;
    public function transform(array $data): array;

}

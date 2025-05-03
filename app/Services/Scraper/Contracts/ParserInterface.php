<?php declare(strict_types=1);

namespace App\Services\Scraper\Contracts;

interface ParserInterface
{
    public function parse(string $html): array;
}

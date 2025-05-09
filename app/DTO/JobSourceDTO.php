<?php declare(strict_types=1);

namespace App\DTO;

final readonly class JobSourceDTO
{
    public function __construct(
        public string $title,
        public string $url,
        public string $source
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['title'],
            $data['url'],
            $data['source']
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'url' => $this->url,
            'source' => $this->source
        ];
    }

}

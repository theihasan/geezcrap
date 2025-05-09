<?php

namespace App\Jobs;

use App\DTO\JobSourceDTO;
use App\Models\Source;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SaveSourcesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public JobSourceDTO $source)
    {
    }

    public function handle(): void
    {
        Source::query()->create([
            'title' => $this->source->title,
            'url' => $this->source->url,
            'source' => $this->source->source
        ]);
    }
}

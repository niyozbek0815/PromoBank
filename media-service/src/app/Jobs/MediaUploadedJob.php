<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MediaUploadedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $fileData,
        public string $context,
        public string $correlationId,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // bu media-service ichida
        Log::info('MediaUploadedJob ishladi');
    }
}
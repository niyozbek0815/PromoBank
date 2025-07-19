<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class StoreBase64MediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $base64,
        public string $context,
        public string $correlationId,
        public string $callbackQueue,
        public ?array $deleteMediaUrls = null
    ) {}

    public function handle(): void
    {
        Log::info('StoreBase64MediaJob ishladi');
    }
}
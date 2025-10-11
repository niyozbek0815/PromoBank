<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSmsPromoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     */
    public function __construct(
        public  string $phone,
        public  string $promocode,
        public  string $shortPhone,
        public  string $correlationId,
        public  string $createdAt
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Log::info(message: 'ProccesSmsPromoJob ishladi Api-getaway ichida');
    }
}

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
    public $phone;
    public $promocode;
    public $shortphone;
    public $correlationId;
    public $created_at;

    public function __construct(string $phone, $promocode, $shortphone, $correlationId, $created_at)
    {
        $this->phone         = $phone;
        $this->promocode     = $promocode;
        $this->shortphone    = $shortphone;
        $this->correlationId = $correlationId;
        $this->created_at    = $created_at;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Log::info(message: 'ProccesSmsPromoJob ishladi Api-getaway ichida');
    }
}
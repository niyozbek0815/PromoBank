<?php

namespace App\Jobs;

use App\Models\PromoAction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreatePromoActionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $payload;

    /**
     * Create a new job instance.
     */
    public function __construct(array $payload)
    {
        // 🔒 Filter faqat kerakli maydonlar
        $this->payload = array_intersect_key($payload, array_flip([
            'promotion_id',
            'promo_code_id',
            'platform_id',
            'user_id',
            'prize_id',
            'action',
            'status',
            'attempt_time',
            'message',
        ]));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("Actions data", ['data' => $this->payload]);
            PromoAction::create($this->payload);
        } catch (\Throwable $e) {
            Log::channel('daily')->error('PromoAction yaratishda xatolik', [
                'payload' => $this->payload,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

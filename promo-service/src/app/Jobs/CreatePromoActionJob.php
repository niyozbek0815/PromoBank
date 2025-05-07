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
    protected array $data;
    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            PromoAction::create([
                'promotion_id'   => $this->data['promotion_id'] ?? null,
                'promo_code_id'  => $this->data['promo_code_id'] ?? null,
                'user_id'        => $this->data['user_id'] ?? null,
                'prize_id'       => $this->data['prize_id'] ?? null,
                'action'         => $this->data['action'] ?? null,
                'status'         => $this->data['status'] ?? null,
                'attempt_time'   => $this->data['attempt_time'] ?? now(),
                'message'        => $this->data['message'] ?? null,
            ]);

            Log::info('PromoAction created successfully.', ['data' => $this->data]);
        } catch (\Throwable $e) {
            Log::error('Failed to create PromoAction.', [
                'data' => $this->data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e; // bu xatolik queue loglarida ham ko‘rinadi
        }
    }
}

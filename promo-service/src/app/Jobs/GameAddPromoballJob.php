<?php

namespace App\Jobs;

use App\Models\EncouragementPoint;
use App\Models\PromoAction;
use App\Models\UserPointBalance;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GameAddPromoballJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     */
    public function __construct(
        protected $promoball,
        protected $session,
        protected $user_id,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('GameAddPromoballJob data', ["session_id" => $this->session, "user_id" => $this->user_id]);

        DB::transaction(function () {
            $balance = UserPointBalance::firstOrCreate(
                ['user_id' => $this->user_id],
                ['balance' => 0]
            );
            $balance->increment('balance', $this->promoball);
            $en = EncouragementPoint::create([
                'user_id' => $this->user_id,
                'scope_type' => "App\\Models\\GameSession"


                ,
            ,
                'scope_id' => $this->session,
                'type' => 'game',
                'points' => $this->promoball,
            ]);
            Log::info('GameAddPromoballJob data', ['balance' => $balance, "session_id" => $this->session, "user_id" => $this->user_id, 'en' => $en]);
        });
    }
}

<?php

namespace App\Jobs;

use App\Models\EncouragementPoint;
use App\Models\Prize;
use App\Models\PromoCodeUser;
use App\Models\SalesProduct;
use App\Models\SalesReceipt;
use App\Models\UserPointBalance;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class CreateReceiptAndProductJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;



    public $tries = 3;
    public $timeout = 15;

    public function __construct(
        protected array $data,
        protected array $user,
        protected  $promoCodeId = null,
        protected $platformId,
        protected  $selectedPrizes = [],
        protected  $subPrizeId = null,
        protected  $manualPrizeCount = 0,
        protected   $promotionId,
    ) {
        $this->data = $data;
        $this->user = $user;
        $this->promoCodeId = $promoCodeId;
        $this->platformId = $platformId;
        $this->selectedPrizes = $selectedPrizes;
        $this->subPrizeId = $subPrizeId;
        $this->manualPrizeCount = $manualPrizeCount;
        $this->promotionId = $promotionId;
    }
    public function middleware()
    {
        return [new WithoutOverlapping("receipt_{$this->data['chek_id']}")];
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = $this->user;
        // PromoCodeUser::query()->delete();
        // SalesReceipt::query()->delete();
        // SalesProduct::query()->delete();
        try {
            $receipt_id = DB::transaction(function () use ($user) {
                $receipt = SalesReceipt::create([
                    'user_id'      => $user['id'],
                    'name'         => $this->data['name'],
                    'chek_id'      => $this->data['chek_id'],
                    'nkm_number'   => $this->data['nkm_number'],
                    'sn'           => $this->data['sn'],
                    'check_date'   => $this->data['check_date'],
                    'qqs_summa'    => $this->data['qqs_summa'],
                    'summa'        => $this->data['summa'],
                    'lat'          => $this->data['lat'] ?? null,
                    'long'         => $this->data['long'] ?? null,
                ]);

                $products = collect($this->data['products'])->map(function ($product) use ($receipt) {
                    return [
                        'receipt_id' => $receipt->id,
                        'name'       => $product['name'],
                        'count'      => $product['count'],
                        'summa'      => $product['summa'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                });

                SalesProduct::insert($products->toArray());
                return $receipt['id'];
            });
            if ($this->manualPrizeCount > 0 || !empty($this->selectedPrizes)) {
                Log::info("Dispatching PromoCodeUserForReceiptJob", [
                    'user_id' => $user['id'],
                    'receipt_id' => $receipt_id,
                    'manual_prize_count' => $this->manualPrizeCount,
                    'selected_prizes' => $this->selectedPrizes,
                ]);
                $this->dispatchPromoCodeJob($user['id'], $receipt_id);
            };
            if ($this->manualPrizeCount == 0 && empty($thiss->selectedPrize)) {
                $this->giveEncouragementPoints($user['id'], $receipt_id);
            }
        } catch (\Exception $e) {

            Log::error("Error processing job: " . $e->getMessage());
            Log::info("Selected prizes count", ['count' => count($this->selectedPrizes)]);
            Log::info("Manual prize count", ['count' => $this->manualPrizeCount]);
        }
    }
    private function giveEncouragementPoints($user_id, $receipt_id)
    {
        $encouragementPoints = config('services.constants.encouragement_points');

        // Update balance in user_point_balances table
        // 1. Avval satr bor-yo'qligini tekshirish va yaratish (agar yo'q bo'lsa)
        UserPointBalance::firstOrCreate(
            ['user_id' => $user_id],
            ['balance' => 0]
        );

        // 2. So'ngra balansni oshirish
        UserPointBalance::where('user_id', $user_id)->increment('balance', $encouragementPoints);

        // Log the awarded encouragement points
        EncouragementPoint::create([
            'user_id' => $user_id,
            'receipt_id' => $receipt_id,
            'points' => $encouragementPoints,
        ]);
    }
    private function dispatchPromoCodeJob($userId, $receiptId): void
    {
        Queue::connection('rabbitmq')->push(new PromoCodeUserForReceiptJob(
            null,
            $userId,
            $this->platformId,
            $receiptId,
            null,
            null,
            $this->subPrizeId,
            $this->promotionId,
            $this->manualPrizeCount,
            $this->selectedPrizes
        ));
    }
}

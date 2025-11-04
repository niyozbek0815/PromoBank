<?php

namespace App\Jobs;

use App\Models\EncouragementPoint;
use App\Models\PlatformPromoSetting;
use App\Models\Prize;
use App\Models\PromoAction;
use App\Models\PromoCodeUser;
use App\Models\SalesProduct;
use App\Models\SalesReceipt;
use App\Models\UserPointBalance;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
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
        protected $promoCodeId = null,
        protected $platformId,
        protected $selectedPrizes = [],
        protected $subPrizeId = null,
        protected $manualPrizeCount = 0,
        protected $promotionId,
        protected $shopId = null
    ) {
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

        $actions = [];
        try {
             DB::transaction(function () use ($user, $actions) {
                $receipt = SalesReceipt::create([
                    'user_id' => $user['id'],
                    'name' => $this->data['name'],
                    'chek_id' => $this->data['chek_id'],
                    'nkm_number' => $this->data['nkm_number'],
                    'sn' => $this->data['sn'],
                    'check_date' => $this->data['check_date'],
                    'qqs_summa' => $this->data['qqs_summa'],
                    'summa' => $this->data['summa'],
                    'lat' => $this->data['lat'] ?? null,
                    'long' => $this->data['long'] ?? null,
                    // "payment_type" => "naqt",
                ]);
                $actions[] = $this->buildAction(
                    'vote',
                    'scaner',
                    $receipt->id,
                    null,
                    'Receipt created',
                    null
                );
                Log::info('Receipts', ['receipts' => $receipt]);

                $products = collect($this->data['products'])->map(function ($product) use ($receipt) {
                    return [
                        'receipt_id' => $receipt->id,
                        'name' => $product['name'],
                        'count' => $product['count'],
                        'summa' => $product['summa'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                });

                Log::info("receipt", ['receipt' => $receipt]);

                SalesProduct::insert($products->toArray());
                if ($this->manualPrizeCount > 0 || !empty($this->selectedPrizes)) {
                    Log::info("Dispatching PromoCodeUserForReceiptJob", [
                        'user_id' => $user['id'],
                        'receipt_id' => $receipt['id'],
                        'manual_prize_count' => $this->manualPrizeCount,
                        'selected_prizes' => $this->selectedPrizes,
                    ]);
                    $this->dispatchPromoCodeJob($user['id'], $receipt['id'], $actions);
                }
                ;
                if ($this->manualPrizeCount == 0 && empty($this->selectedPrizes)) {
                    Log::info("Ball berildi");
                    $this->giveEncouragementPoints($user['id'], $receipt['id'], $actions);
                }
            });

        } catch (\Exception $e) {

            Log::error("Error processing job: " . $e->getMessage());
            Log::info("Selected prizes count", ['count' => count($this->selectedPrizes)]);
            Log::info("Manual prize count", ['count' => $this->manualPrizeCount]);
        }
    }
    private function giveEncouragementPoints($user_id, $receipt_id, array &$actions)
    {
        $settings = Cache::remember('platform_promo_settings', now()->addHours(1), function () {
            return PlatformPromoSetting::default();
        });
        $promoball = $settings['scanner_points'];
        UserPointBalance::firstOrCreate(
            ['user_id' => $user_id],
            ['balance' => 0]
        );

        // 2. So'ngra balansni oshirish
        UserPointBalance::where('user_id', $user_id)->increment('balance', $promoball);
        Log::info("promoball",['promo'=>$promoball]);

        $actions[] = $this->buildAction(
            'points_win',
            'scaner_win',
            $receipt_id,
            null,
            "Encouragement points: {$promoball}",
            null
        );
        // Log the awarded encouragement points
        EncouragementPoint::create([
            'user_id' => $user_id,
            'scope_id' => $receipt_id,
            'scope_type' => "scanner",
            'points' => $promoball,
        ]);

    }
    private function dispatchPromoCodeJob(int $userId, int $receiptId, array &$actions): void
    {
        $now = now();
        $baseData = [
            'promo_code_id' => $this->promoCodeId,
            'user_id' => $userId,
            'platform_id' => $this->platformId,
            'receipt_id' => $receiptId,
            'sub_prize_id' => $this->subPrizeId,
            'promotion_id' => $this->promotionId,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $lastIdBeforeInsert = PromoCodeUser::max('id') ?? 0;

        // 1️⃣ Manual prizes
        if ($this->manualPrizeCount > 0) {
            $manualRows = array_map(fn() => array_merge($baseData, ['prize_id' => null]), range(1, $this->manualPrizeCount));
            PromoCodeUser::insert($manualRows);

            foreach ($manualRows as $_) {
                $actions[] = $this->buildAction('manual_win', 'scaner_pending', $receiptId, null, 'Manual win recorded', $this->shopId);
            }
        }

        // 2️⃣ Selected prizes
        if (!empty($this->selectedPrizes)) {
            $rows = $prizeIds = [];
            foreach ($this->selectedPrizes as $item) {
                $prize = $item['prize'] ?? null;
                $entry = $item['entry'] ?? [];
                if (!isset($prize['id']))
                    continue;

                $rows[] = array_merge($baseData, [
                    'prize_id' => $prize['id'],
                    'promotion_product_id' => $entry['product_id'] ?? null,
                ]);
                $prizeIds[] = $prize['id'];

                $actions[] = $this->buildAction(
                    'auto_win',
                    'scaner_win',
                    $receiptId,
                    $prize['id'],
                    "Prize auto-awarded: {$prize['name']}",
                    $this->shopId ?? null
                );
            }

            if ($rows) {
                PromoCodeUser::insert($rows);

                // Prize awarded quantity update
                foreach (array_count_values($prizeIds) as $id => $count) {
                    Prize::where('id', $id)->increment('awarded_quantity', $count);
                }
            }
        }

        // 3️⃣ Actions insert
        if (!empty($actions)) {
            try {
                PromoAction::insert($actions);
            } catch (\Throwable $e) {
                Log::error("PromoAction insert failed", ['error' => $e->getMessage()]);
            }
        }

        // 4️⃣ Log inserted rows (optional, light logging)
        Log::info('Inserted PromoCodeUsers', PromoCodeUser::where('id', '>', $lastIdBeforeInsert)->get(['id', 'prize_id', 'promotion_product_id'])->toArray());
    }
    private function buildAction(
        string $action,
        string $status,
        ?int $receiptId = null,
        ?int $prizeId = null,
        ?string $message = null,
        ?int $shopId = null
    ): array {
        return [
            'promotion_id' => $this->promotionId,
            'promo_code_id' => $this->promoCodeId,
            'platform_id' => $this->platformId,
            'receipt_id' => $receiptId,
            'shop_id' => $shopId,
            'user_id' => $this->user['id'],
            'prize_id' => $prizeId,
            'action' => $action,
            'status' => $status,
            'attempt_time' => now(),
            'message' => $message,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

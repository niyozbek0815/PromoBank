<?php

namespace App\Jobs;

use App\Models\Prize;
use App\Models\PromoCodeUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PromoCodeUserForReceiptJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected $promoCodeId = null,
        protected $userId,
        protected $platformId,
        protected $receiptId = null,
        protected $promotionProductId = null,
        protected $prizeId = null,
        protected $subPrizeId = null,
        protected $promotionId,
        protected $count,
        protected array $selectedPrizes = [],
    ) {
        $this->promoCodeId = $promoCodeId;
        $this->userId = $userId;
        $this->platformId = $platformId;
        $this->receiptId = $receiptId;
        $this->promotionProductId = $promotionProductId;
        $this->prizeId = $prizeId;
        $this->subPrizeId = $subPrizeId;
        $this->promotionId = $promotionId;
        $this->count = $count;
        $this->selectedPrizes = $selectedPrizes;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            DB::transaction(function () {
                $baseData = [
                    'promo_code_id' => $this->promoCodeId,
                    'user_id' => $this->userId,
                    'platform_id' => $this->platformId,
                    'receipt_id' => $this->receiptId,
                    'promotion_product_id' => $this->promotionProductId,
                    'sub_prize_id' => $this->subPrizeId,
                    'promotion_id' => $this->promotionId,
                ];
                Log::info("data", ['data' => $baseData]);

                // Transaction boshida oxirgi ID ni saqlab olamiz
                $lastIdBeforeInsert = PromoCodeUser::max('id') ?? 0;
                if ($this->count > 0) {
                    PromoCodeUser::insert(array_fill(0, (int) $this->count, array_merge($baseData, [
                        'prize_id' => null,
                    ])));
                }
                if (!empty($this->selectedPrizes)) {
                    $rows = [];
                    $prizeIds = [];

                    foreach ($this->selectedPrizes as $item) {
                        $prize = $item['prize'] ?? null;
                        $entry=$item['entry'];
                        if (!isset($prize['id'])) {
                            continue;
                        }
                        $rows[] = array_merge($baseData, [
                            'prize_id' => $prize['id'],
                            'promotion_product_id' => $entry['product_id'] ?? null, // entry asosida update
                        ]);
                        $prizeIds[] = $prize['id'];
                    }
                    if (!empty($rows)) {
                        Log::info("Rows (before insert)", ['rows' => $rows]);

                        // Insert qilish
                        PromoCodeUser::insert($rows);

                        // Oxirgi insert qilingan ID ni olish
                        $lastId = PromoCodeUser::latest('id')->value('id');

                        // Insert qilingan qatorlar soni
                        $count = count($rows);

                        // Insert qilingan ID lar diapazoni
                        $insertedIds = range($lastId - $count + 1, $lastId);

                        // DB dan yangi qo‘shilganlarni olish
                        $inserted = PromoCodeUser::whereIn('id', $insertedIds)
                            ->with(['prize:id,name', 'promotionProduct:id,name'])
                            ->get();

                        Log::info("Inserted PromoCodeUsers from DB", $inserted->toArray());

                        // Prize count update
                        foreach (array_count_values($prizeIds) as $id => $count) {
                            Prize::where('id', $id)->increment('awarded_quantity', $count);
                        }
                    }
                }
                $inserted = PromoCodeUser::where('id', '>', $lastIdBeforeInsert)->get();
                Log::info('Inserted PromoCodeUsers from DB', $inserted->toArray());
            });

        } catch (\Throwable $e) {
            Log::error('PromoCodeUserForReceiptJob failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $this->userId,
                'promotion_id' => $this->promotionId,
                'selectedPrizes' => $this->selectedPrizes,
            ]);
            throw $e;
        }
    }
}

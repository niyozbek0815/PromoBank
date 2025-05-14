<?php

namespace App\Jobs;

use App\Models\SalesProduct;
use App\Models\SalesReceipt;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class CreateReceiptAndProductJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected array $data;
    protected array $user;
    protected $promoCodeId;
    protected $userId;
    protected $platformId;
    protected $receiptId;
    protected $promotionProductId;
    protected $prizeId;
    protected $subPrizeId;
    public $tries = 3;
    public $timeout = 15;
    protected $status;

    public function __construct(
        array $data,
        array $user,
        $promoCodeId = null,
        $platformId,
        $promotionProductId = null,
        $prizeId = null,
        $subPrizeId = null,
        $status
    ) {
        $this->data = $data;
        $this->user = $user;
        $this->promoCodeId = $promoCodeId;
        $this->platformId = $platformId;
        $this->promotionProductId = $promotionProductId;
        $this->prizeId = $prizeId;
        $this->subPrizeId = $subPrizeId;
        $this->status = $status;
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

        try {
            $receipt_id = DB::transaction(function () {
                $receipt = SalesReceipt::create([
                    'user_id'      => $this->user['id'],
                    'name'         => $this->data['name'],
                    'chek_id'      => $this->data['chek_id'],
                    'nkm_number'   => $this->data['nkm_number'],
                    'sn'           => $this->data['sn'],
                    'check_date'   => $this->data['check_date'],
                    'payment_type' => $this->data['payment_type'],
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
            if ($this->status !== "failed") {
                Queue::connection(name: 'rabbitmq')->push(new PromoCodeConsumeJob(
                    $this->promoCodeId,
                    $this->user['id'],
                    $this->platformId,
                    $receipt_id,
                    $this->promotionProductId,
                    $this->prizeId,
                    $this->subPrizeId
                ));
            }
        } catch (\Exception $e) {
            // Log error
            \Log::error("Error processing job: " . $e->getMessage());
        }
    }
}

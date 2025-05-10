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

class CreateReceiptAndProductJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected array $data;
    protected array $user;

    public $tries = 3;
    public $timeout = 15;

    public function __construct(array $data, array $user)
    {
        $this->data = $data;
        $this->user = $user;
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
            DB::transaction(function () {
                DB::transaction(function () {
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
                });
            });
        } catch (\Exception $e) {
            // Log error
            \Log::error("Error processing job: " . $e->getMessage());
        }
    }
}

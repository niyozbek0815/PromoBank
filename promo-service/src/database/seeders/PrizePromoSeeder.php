<?php

namespace Database\Seeders;

use App\Models\Prize;
use App\Models\PrizeCategory;
use App\Models\PrizePromo;
use App\Models\PromoCode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrizePromoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            $targetCategories = PrizeCategory::whereIn('name', ['manual', 'auto_bind'])->pluck('id');
            $prizes = Prize::whereIn('category_id', $targetCategories)->get();

            $usedPromoCodeIds = PrizePromo::pluck('promo_code_id')->filter()->toArray();
            $availablePromoCodes = PromoCode::whereNotIn('id', $usedPromoCodeIds)->get();

            $promoCodeIndex = 0;
            $insertData = [];

            foreach ($prizes as $prize) {
                for ($i = 0; $i < $prize->quantity; $i++) {
                    if (!isset($availablePromoCodes[$promoCodeIndex])) {
                        throw new \Exception("Yetarlicha promo_code yo‘q. Prize ID: {$prize->id}, kerakli son: {$prize->quantity}");
                    }

                    $promoCode = $availablePromoCodes[$promoCodeIndex++];

                    $insertData[] = [
                        'promotion_id'   => $prize->promotion_id,
                        'prize_id'       => $prize->id,
                        'category_id'    => $prize->category_id,
                        'promo_code_id'  => $promoCode->id,
                        'sub_prize'      => null,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ];
                }
            }

            PrizePromo::insert($insertData);
            DB::commit(); // Commit the transaction if all is fine
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback in case of error
            throw $e; // Rethrow the exception
        }
    }
}

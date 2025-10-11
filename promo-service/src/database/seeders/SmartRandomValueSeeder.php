<?php
namespace Database\Seeders;

use App\Models\Prize;
use App\Models\PrizeCategory;
use App\Models\SmartRandomRule;
use App\Models\SmartRandomValue;
use Illuminate\Database\Seeder;

class SmartRandomValueSeeder extends Seeder
{
    public function run(): void
    {
        $category = PrizeCategory::where('name', 'smart_random')->first();
        if (! $category) {
            $this->command->error("'smart_random' category topilmadi.");
            return;
        }

        $prizes = Prize::where('category_id', $category->id)->get();
        $rules  = SmartRandomRule::all()->keyBy('key');

        if ($rules->isEmpty()) {
            $this->command->error("SmartRandomRule ma'lumotlari mavjud emas.");
            return;
        }

        $insertData = [];

        foreach ($prizes as $prize) {
            // Har bir Prize uchun shartlar tanlanadi
            $prizeRules = [];

            // 1) Majburiy qoida: code_length = 10
            $prizeRules[] = [
                'rule_id'  => $rules['code_length']->id,
                'operator' => '=',
                'values'   => [10],
            ];

            // 2) 50% ehtimollik bilan digit_count >= 3
            if (rand(0, 1)) {
                $prizeRules[] = [
                    'rule_id'  => $rules['digit_count']->id,
                    'operator' => '>=',
                    'values'   => [3],
                ];
            }

            // 3) 33% ehtimollik bilan boshlanish 'WDA'
            if (rand(1, 3) === 1) {
                $prizeRules[] = [
                    'rule_id'  => $rules['starts_with']->id,
                    'operator' => 'in',
                    'values'   => ['WDA'],
                ];
            }

            // 4) 33% ehtimollik bilan tugashi 'WRE'
            if (rand(1, 3) === 1) {
                $prizeRules[] = [
                    'rule_id'  => $rules['ends_with']->id,
                    'operator' => 'in',
                    'values'   => ['WRE', 'EE'],
                ];
            }

            // 5) 25% ehtimollik bilan uppercase_count >= 2
            if (isset($rules['uppercase_count']) && rand(1, 4) === 1) {
                $prizeRules[] = [
                    'rule_id'  => $rules['uppercase_count']->id,
                    'operator' => '>=',
                    'values'   => [2],
                ];
            }

            // Insertga tayyorlash
         foreach ($prizeRules as $rule) {
    $insertData[] = [
        'prize_id'   => $prize->id,
        'rule_id'    => $rule['rule_id'],
        'operator'   => $rule['operator'],
        'values'     => json_encode($rule['values']),
        'created_at' => now(),
        'updated_at' => now(),
    ];
}

        }

        SmartRandomValue::insert($insertData);
        $this->command->info(count($insertData) . " ta smart_random rule yozildi.");
    }
}

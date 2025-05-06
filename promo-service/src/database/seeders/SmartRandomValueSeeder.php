<?php

namespace Database\Seeders;

use App\Models\Prize;
use App\Models\PrizeCategory;
use App\Models\SmartRandomRule;
use App\Models\SmartRandomValue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SmartRandomValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $smartCategory = PrizeCategory::where('name', 'smart_random')->first();
        if (!$smartCategory) {
            $this->command->error("'smart_random' category topilmadi");
            return;
        }

        $prizes = Prize::where('category_id', $smartCategory->id)->get();

        $availableRules = SmartRandomRule::all()->keyBy('key');

        foreach ($prizes as $prize) {
            $rules = [];

            // Basic rule: umumiy uzunlik = 10
            $rules[] = [
                'rule_id' => $availableRules['code_length']->id,
                'operator' => '=',
                'values' => json_encode([10]),
            ];

            // 50% ehtimollik bilan raqamlar soni >= 3
            if (rand(0, 1)) {
                $rules[] = [
                    'rule_id' => $availableRules['digit_count']->id,
                    'operator' => '>=',
                    'values' => json_encode([3]),
                ];
            }

            // 33% ehtimollik bilan boshlanish 'WDA'
            if (rand(1, 3) === 1) {
                $rules[] = [
                    'rule_id' => $availableRules['starts_with']->id,
                    'operator' => 'in',
                    'values' => json_encode(['WDA']),
                ];
            }

            // 33% ehtimollik bilan tugashi 'WRE'
            if (rand(1, 3) === 1) {
                $rules[] = [
                    'rule_id' => $availableRules['ends_with']->id,
                    'operator' => 'in',
                    'values' => json_encode(['WRE']),
                ];
            }

            foreach ($rules as $rule) {
                SmartRandomValue::create([
                    'prize_id' => $prize->id,
                    'rule_id' => $rule['rule_id'],
                    'operator' => $rule['operator'],
                    'values' => $rule['values'],
                ]);
            }
        }
    }
}

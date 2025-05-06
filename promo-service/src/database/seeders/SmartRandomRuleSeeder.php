<?php

namespace Database\Seeders;

use App\Models\SmartRandomRule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SmartRandomRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rules = [
            [
                'key' => 'code_length',
                'label' => 'Promocode uzunligi',
                'input_type' => 'number',
                'is_comparison' => true,
            ],
            [
                'key' => 'uppercase_count',
                'label' => 'Katta harflar soni',
                'input_type' => 'number',
                'is_comparison' => true,
            ],
            [
                'key' => 'lowercase_count',
                'label' => 'Kichik harflar soni',
                'input_type' => 'number',
                'is_comparison' => true,
            ],
            [
                'key' => 'digit_count',
                'label' => 'Raqamlar soni',
                'input_type' => 'number',
                'is_comparison' => true,
            ],
            [
                'key' => 'special_char_count',
                'label' => 'Maxsus belgilar soni',
                'input_type' => 'number',
                'is_comparison' => true,
            ],
            [
                'key' => 'starts_with',
                'label' => 'Shu belgilar bilan boshlansin',
                'input_type' => 'text_array',
                'is_comparison' => false,
            ],
            [
                'key' => 'not_starts_with',
                'label' => 'Shu belgilar bilan boshlanmasin',
                'input_type' => 'text_array',
                'is_comparison' => false,
            ],
            [
                'key' => 'ends_with',
                'label' => 'Shu belgilar bilan tugasin',
                'input_type' => 'text_array',
                'is_comparison' => false,
            ],
            [
                'key' => 'not_ends_with',
                'label' => 'Shu belgilar bilan tugamasin',
                'input_type' => 'text_array',
                'is_comparison' => false,
            ],
            [
                'key' => 'contains',
                'label' => 'Ichida shu belgi bo‘lsin',
                'input_type' => 'text_array',
                'is_comparison' => false,
            ],
            [
                'key' => 'not_contains',
                'label' => 'Ichida shu belgi bo‘lmasin',
                'input_type' => 'text_array',
                'is_comparison' => false,
            ],
            [
                'key' => 'contains_sequence',
                'label' => 'Ichida shu ketma-ketlik bo‘lsin',
                'input_type' => 'text_array',
                'is_comparison' => false,
            ],
            [
                'key' => 'unique_char_count',
                'label' => 'Takrorlanmas belgilar soni',
                'input_type' => 'number',
                'is_comparison' => true,
            ],
        ];

        foreach ($rules as $rule) {
            SmartRandomRule::updateOrCreate(
                ['key' => $rule['key']],
                [
                    'label' => $rule['label'],
                    'input_type' => $rule['input_type'],
                    'is_comparison' => $rule['is_comparison'],
                ]
            );
        }
    }
}

<?php
namespace Database\Seeders;

use App\Models\SmartRandomRule;
use Illuminate\Database\Seeder;

class SmartRandomRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'key'                => 'code_length',
                'label'              => 'Promocode uzunligi',
                'input_type'         => 'number',
                'is_comparison'      => true,
                'accepted_operators' => ['=', '!=', '>', '>=', '<', '<='],
                'description'        => 'Promokoddagi belgilar umumiy sonini belgilaydi. Masalan: 1.',
            ],
            [
                'key'                => 'uppercase_count',
                'label'              => 'Katta harflar soni',
                'input_type'         => 'number',
                'is_comparison'      => true,
                'accepted_operators' => ['=', '!=', '>', '>=', '<', '<='],
                'description'        => 'Promokodda nechta katta harf (A-Z) bo‘lishi kerakligini belgilaydi. Masalan: 2',
            ],
            [
                'key'                => 'lowercase_count',
                'label'              => 'Kichik harflar soni',
                'input_type'         => 'number',
                'is_comparison'      => true,
                'accepted_operators' => ['=', '!=', '>', '>=', '<', '<='],
                'description'        => 'Promokodda nechta kichik harf (a-z) bo‘lishi kerakligini belgilaydi. Masalan: 3',
            ],
            [
                'key'                => 'digit_count',
                'label'              => 'Raqamlar soni',
                'input_type'         => 'number',
                'is_comparison'      => true,
                'accepted_operators' => ['=', '!=', '>', '>=', '<', '<='],
                'description'        => 'Promokodda nechta raqam (0-9) bo‘lishi kerakligini belgilaydi. Masalan: 4',
            ],
            [
                'key'                => 'special_char_count',
                'label'              => 'Maxsus belgilar soni',
                'input_type'         => 'number',
                'is_comparison'      => true,
                'accepted_operators' => ['=', '!=', '>', '>=', '<', '<='],
                'description'        => 'Promokodda nechta maxsus belgi (!, @, #, $, va hokazo) bo‘lishi kerakligini belgilaydi. Masalan: 1',
            ],
            [
                'key'                => 'starts_with',
                'label'              => 'Shu belgilar bilan boshlansin',
                'input_type'         => 'text_array',
                'is_comparison'      => true,
                'accepted_operators' => ['IN'],
                'description'        => 'Promokod quyidagi belgilar bilan boshlanishi kerak. Misollar: A , PROMO, 1X',
            ],
            [
                'key'                => 'not_starts_with',
                'label'              => 'Shu belgilar bilan boshlanmasin',
                'input_type'         => 'text_array',
                'is_comparison'      => true,
                'accepted_operators' => ['NOT IN'],
                'description'        => 'Promokod quyidagi belgilar bilan boshlanmasligi kerak. Misollar: 0, TEST, !',
            ],
            [
                'key'                => 'ends_with',
                'label'              => 'Shu belgilar bilan tugasin',
                'input_type'         => 'text_array',
                'is_comparison'      => true,
                'accepted_operators' => ['IN'],
                'description'        => 'Promokod quyidagi belgilar bilan tugashi kerak. Misollar: Z, 2025, !!.',
            ],
            [
                'key'                => 'not_ends_with',
                'label'              => 'Shu belgilar bilan tugamasin',
                'input_type'         => 'text_array',
                'is_comparison'      => true,
                'accepted_operators' => ['NOT IN'],
                'description'        => 'Promokod quyidagi belgilar bilan tugamasligi kerak. Misollar: 0, X, !@#.',
            ],
            [
                'key'                => 'contains',
                'label'              => 'Ichida shu belgi bo‘lsin',
                'input_type'         => 'text_array',
                'is_comparison'      => true,
                'accepted_operators' => ['LIKE', 'IN'],
                'description'        => 'Promokod tarkibida quyidagi belgilar mavjud bo‘lishi kerak. Misollar: a, 12, X#, !.',
            ],
            [
                'key'                => 'not_contains',
                'label'              => 'Ichida shu belgi bo‘lmasin',
                'input_type'         => 'text_array',
                'is_comparison'      => true,
                'accepted_operators' => ['NOT LIKE', 'NOT IN'],
                'description'        => 'Promokod tarkibida quyidagi belgilar mavjud bo‘lmasligi kerak. Misollar: 0, AB, @!',
            ],
            [
                'key'                => 'contains_sequence',
                'label'              => 'Ichida shu ketma-ketlik bo‘lsin',
                'input_type'         => 'text_array',
                'is_comparison'      => true,
                'accepted_operators' => ['LIKE'],
                'description'        => 'Promokodda aynan quyidagi ketma-ketliklardan biri mavjud bo‘lishi kerak. Misollar: XYZ, 123, !!',
            ],
            [
                'key'                => 'unique_char_count',
                'label'              => 'Takrorlanmas belgilar soni',
                'input_type'         => 'number',
                'is_comparison'      => true,
                'accepted_operators' => ['=', '!=', '>', '>=', '<', '<='],
                'description'        => 'Promokod ichida nechta noyob belgi bo‘lishi kerak. Masalan: 8.',
            ],
        ];

        foreach ($rules as $rule) {
            SmartRandomRule::updateOrCreate(
                ['key' => $rule['key']],
                [
                    'label'              => $rule['label'],
                    'input_type'         => $rule['input_type'],
                    'is_comparison'      => $rule['is_comparison'],
                    'accepted_operators' => $rule['accepted_operators'],
                    'description'        => $rule['description'],
                ]
            );
        }
    }
}

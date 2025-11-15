<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompaniesUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bindings = [
            ['company_id' => 1, 'user_id' => 12],
            ['company_id' => 1, 'user_id' => 13],
            ['company_id' => 2, 'user_id' => 14],
            ['company_id' => 2, 'user_id' => 15],
            ['company_id' => 3, 'user_id' => 16],
            ['company_id' => 3, 'user_id' => 17],
        ];

        DB::table('companies_users')->insert($bindings);
    }
}

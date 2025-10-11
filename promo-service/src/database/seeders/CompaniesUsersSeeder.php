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
            ['company_id' => 1, 'user_id' => 101],
            ['company_id' => 1, 'user_id' => 102],
            ['company_id' => 2, 'user_id' => 103],
            ['company_id' => 2, 'user_id' => 101],
            ['company_id' => 3, 'user_id' => 104],
            ['company_id' => 3, 'user_id' => 102],
        ];

        DB::table('companies_users')->insert($bindings);
    }
}

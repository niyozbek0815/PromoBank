<?php
namespace Database\Seeders;

use App\Models\ParticipationType;
use Illuminate\Database\Seeder;

class ParticipationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ParticipationType::insert([
            ['name' => 'QR code', 'slug' => 'qr_code'],
            ['name' => 'Text code', 'slug' => 'text_code'],
            ['name' => 'Receipt scan', 'slug' => 'receipt_scan'],
            // ['name' => 'Telegram', 'slug' => 'telegram'],
            // ['name' => 'SMS', 'slug' => 'sms'],
        ]);
    }
}
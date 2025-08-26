<?php
namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserDevicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker     = Faker::create();
        $batchSize = 1000;   // bir martada nechta yozuv insert qilish
        $total     = 15000; // umumiy yozuv soni
        $types     = ['android', 'ios', 'web'];
        // 'telegram'



        for ($i = 0; $i < $total; $i += $batchSize) {
            $data = [];

            for ($j = 0; $j < $batchSize; $j++) {
                $data[] = [
                    'user_id'       => rand(1, 50000), // user service ID (simulyatsiya)
                    'ip_address'    => $faker->ipv4,
                    'is_guest'      => (bool) rand(0, 1),
                    'fcm_token'     => uniqid('fcm_', true) . bin2hex(random_bytes(8)),
                    'device_type'   => $types[array_rand($types)], // sms yo‘q
                    'device_name'   => $faker->randomElement(['iPhone', 'Samsung', 'Redmi', 'Pixel', 'Huawei']),
                    'app_version'   => $faker->numerify('v#.##'),
                    'phone'         => $faker->phoneNumber,
                    'user_agent'    => $faker->userAgent,
                    'last_activity' => time() - rand(0, 86400), // 1 kun ichida tasodifiy
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            DB::table('user_devices')->insert($data);

            echo "Inserted: " . ($i + $batchSize) . " / $total\n";
        }

        echo "✅ 100,000 user_devices yozuv yaratildi!\n";
    }
}

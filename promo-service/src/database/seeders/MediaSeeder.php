<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (range(1, 6) as $id) {
            DB::table('media')->insert([
                'model_type'      => 'promo_banner', // yoki model class: App\Models\PromoBanner
                'model_id'        => $id,
                'uuid'            => (string) Str::uuid(),
                'collection_name' => 'default',
                'name'            => $id . 'gif',
                'file_name'       => $id . 'gif',
                'mime_type'       => 'image/jpeg',
                'path'            => "promo_banners/$id.jpg",
                'url'             => "/media/upload/promo_banners/$id.jpg",
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }
}

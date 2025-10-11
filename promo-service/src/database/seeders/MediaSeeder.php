<?php

namespace Database\Seeders;

use App\Models\Company;
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
                'model_type'      =>  \App\Models\Promotions::class, // yoki model class: App\Models\PromoBanner
                'model_id'        => $id,
                'uuid'            => (string) Str::uuid(),
                'collection_name' => 'promo_banner',
                'name'            => $id . 'gif',
                'file_name'       => $id . 'gif',
                'mime_type'       => 'image/jpeg',
                'path'            => "promo_banners/$id.jpg",
                'url'             => "/media/uploads/promo_banners/$id.jpg",
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
        foreach (Company::all() as $company) {
            DB::table('media')->insert([
                'model_type'      => \App\Models\Company::class,
                'model_id'        => $company->id,
                'uuid'            => (string) Str::uuid(),
                'collection_name' => 'company_logo',
                'name'            => "1",
                'file_name'       => "1.png",
                'mime_type'       => 'image/jpeg',
                'path'            => "company_logo/1.png",
                'url'             => "/media/uploads/company_logo/1.png",
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
            DB::table('media')->insert([
                'model_type'      => \App\Models\Promotions::class,
                'model_id'        => $company->id,
                'uuid'            => (string) Str::uuid(),
                'collection_name' => 'promo_video',
                'name'            => "1",
                'file_name'       => "1.mp4",
                'mime_type'       => 'mp4',
                'path'            => "promo_video/1.mp4",
                'url'             => "/media/uploads/promo_video/1.mp4",
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }
}

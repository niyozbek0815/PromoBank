<?php

namespace Database\Seeders;

use App\Models\SocialLink;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SocialLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $links = [
            ['type' => 'instagram',   'url' => 'https://instagram.com/yourpage'],
            ['type' => 'facebook',    'url' => 'https://facebook.com/yourpage'],
            ['type' => 'telegram',    'url' => 'https://t.me/yourchannel'],
            ['type' => 'youtube',     'url' => 'https://youtube.com/yourchannel'],
            ['type' => 'appstore',    'url' => 'https://apps.apple.com/yourapp'],
            ['type' => 'googleplay',  'url' => 'https://play.google.com/store/apps/details?id=yourapp'],
        ];

        foreach ($links as $position => $link) {
            SocialLink::updateOrCreate(
                ['type' => $link['type']],
                [
                    'url'      => $link['url'],
                    'position' => $position,
                    'status'   => 1,
                    'label'    => null, // kerak bo‘lsa tillar qo‘shishingiz mumkin
                ]
            );
        }
    }
}

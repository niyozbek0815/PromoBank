<?php
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SocialMediaSeeder extends Seeder
{
    public function run(): void
    {
        // Mavjud typelar
        $types = [
            1 => 'Telegram',
            2 => 'Instagram',
            3 => 'Facebook',
            4 => 'YouTube',
            5 => 'LinkedIn',
            6 => 'Website',
        ];

        // Kompaniyalar (id => slug yoki nomi uchun prefix)
        $companies = [
            1 => 'artel',
            2 => 'beelineuz',
            3 => 'texnomart',
        ];

        $records = [];

        foreach ($companies as $companyId => $slug) {
            foreach ($types as $typeId => $typeName) {
                $url = $this->generateUrl($slug, $typeName);
                $records[] = [
                    'company_id' => $companyId,
                    'type_id' => $typeId,
                    'url' => $url,
                ];
            }
        }

        DB::table('social_media')->insert($records);
    }

    private function generateUrl(string $slug, string $type): string
    {
        return match ($type) {
            'Telegram' => "https://t.me/{$slug}",
            'Instagram' => "https://instagram.com/{$slug}",
            'Facebook' => "https://facebook.com/{$slug}",
            'YouTube' => "https://youtube.com/{$slug}",
            'LinkedIn' => "https://linkedin.com/company/{$slug}",
            'Website' => "https://{$slug}.uz",
            default => '',
        };
    }
}

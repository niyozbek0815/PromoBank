<?php

namespace Database\Seeders;

use App\Models\Contact;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $contacts = [
            [
                'type' => 'address',
                'url' => 'https://maps.google.com/?q=Toshkent, Amir Temur ko‘chasi, 15-uy',
                'label' => [
                    'uz' => 'Toshkent, Amir Temur ko‘chasi, 15-uy',
                    'ru' => 'Ташкент, улица Амира Темура, дом 15',
                    'kr' => 'Тошкент, Амир Темур кўчаси, 15-уй',
                    'en' => 'Tashkent, Amir Temur street, 15', // inglizcha qo‘shildi
                ],
            ],
            [
                'type' => 'phone',
                'url' => 'tel:+998901234567',
                'label' => [
                    'uz' => '+998 (90) 123-45-67',
                    'ru' => '+998 (90) 123-45-67',
                    'kr' => '+998 (90) 123-45-67',
                    'en' => '+998 (90) 123-45-67', // inglizcha qo‘shildi
                ],
            ],
            [
                'type' => 'email',
                'url' => 'mailto:support@promobank.uz',
                'label' => [
                    'uz' => 'support@promobank.uz',
                    'ru' => 'support@promobank.uz',
                    'kr' => 'support@promobank.uz',
                    'en' => 'support@promobank.uz', // inglizcha qo‘shildi
                ],
            ],
            [
                'type' => 'telegram',
                'url' => 'https://t.me/promobank_support',
                'label' => [
                    'uz' => 'Telegram qo‘llab-quvvatlash',
                    'ru' => 'Поддержка в Telegram',
                    'kr' => 'Телеграм қўллаб-қувватлаш',
                    'en' => 'Telegram support', // inglizcha qo‘shildi
                ],
            ],
            [
                'type' => 'instagram',
                'url' => 'https://instagram.com/promobank',
                'label' => [
                    'uz' => 'Instagram sahifamiz',
                    'ru' => 'Наша страница в Instagram',
                    'kr' => 'Инстаграм саҳифамиз',
                    'en' => 'Our Instagram page', // inglizcha qo‘shildi
                ],
            ],
        ];

        foreach ($contacts as $position => $contact) {
            Contact::updateOrCreate(
                ['type' => $contact['type']],
                [
                    'url' => $contact['url'],
                    'label' => $contact['label'],
                    'position' => $position,
                    'status' => 1,
                ]
            );
        }
    }
}

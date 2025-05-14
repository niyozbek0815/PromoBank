<?php

namespace Database\Seeders;

use App\Models\PromotionMessage;
use App\Models\Promotions;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PromotionMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Platformalar va xabar turlari
        $platforms = ['sms', 'mobile', 'bot', 'all'];
        $types = ['success', 'fail', 'claim', 'info', 'etc'];

        // Har bir Promotion uchun xabarlar yaratish
        Promotions::all()->each(function ($promotion) use ($platforms, $types) {
            foreach ($platforms as $platform) {
                foreach ($types as $type) {
                    // Xabarni yaratish yoki mavjudini tekshirish
                    PromotionMessage::firstOrCreate(
                        [
                            'promotion_id' => $promotion->id,
                            'platform' => $platform,
                            'message_type' => $type,
                        ],
                        [
                            'message' => $this->getMessageForPlatformAndType($platform, $type),
                        ]
                    );
                }
            }
        });
    }
    private function getMessageForPlatformAndType($platform, $type)
    {
        // Har bir kombinatsiya uchun xabarlar
        $messages = [
            'sms' => [
                'success' => [
                    'uz' => "Promocode muvaffaqiyatli ro'yhatga olindi. SMS orqali tasdiqlandi.",
                    'ru' => "Промокод успешно зарегистрирован. Подтверждено через SMS.",
                    'kr' => "프로모코드가 성공적으로 등록되었습니다. SMS로 확인되었습니다.",
                ],
                'fail' => [
                    'uz' => "Afsuski, bu safar sizga sovg‘a chiqmadi. Omadingizni yana sinab ko‘ring!",
                    'ru' => "Промокод не зарегистрирован. Пожалуйста, попробуйте еще раз.",
                    'kr' => "프로모코드를 등록하지 못했습니다. 다시 시도해 주세요.",
                ],
                'claim' => [
                    'uz' => "Promocodedan avval foydalanilgan",
                    'ru' => "Ваша заявка на промокод успешно принята.",
                    'kr' => "프로모코드가 성공적으로 처리되었습니다.",
                ],
                'info' => [
                    'uz' => "Promo-kodingiz haqida qo'shimcha ma'lumot: ...",
                    'ru' => "Дополнительная информация о вашем промокоде: ...",
                    'kr' => "귀하의 프로모코드에 대한 추가 정보: ...",
                ],
                'etc' => [
                    'uz' => "Boshqa xabarlar haqida ma'lumot.",
                    'ru' => "Дополнительная информация.",
                    'kr' => "기타 정보.",
                ],
            ],
            'mobile' => [
                'success' => [
                    'uz' => "Promocode muvaffaqiyatli ro'yhatga olindi.",
                    'ru' => "Промокод успешно зарегистрирован.",
                    'kr' => "프로모코드가 성공적으로 등록되었습니다.",
                ],
                'fail' => [
                    'uz' => "Promocode ro'yhatga olinmadi. Iltimos, yana bir bor urunib ko'ring.",
                    'ru' => "Промокод не зарегистрирован. Пожалуйста, попробуйте еще раз.",
                    'kr' => "프로모코드를 등록하지 못했습니다. 다시 시도해 주세요.",
                ],
                'claim' => [
                    'uz' => "Promocodedan avval foydalanilgan",
                    'ru' => "Ваша заявка на промокод успешно принята.",
                    'kr' => "프로모코드가 성공적으로 처리되었습니다.",
                ],
                'info' => [
                    'uz' => "Promo-kodingiz haqida qo'shimcha ma'lumot: ...",
                    'ru' => "Дополнительная информация о вашем промокоде: ...",
                    'kr' => "귀하의 프로모코드에 대한 추가 정보: ...",
                ],
                'etc' => [
                    'uz' => "Boshqa xabarlar haqida ma'lumot.",
                    'ru' => "Дополнительная информация.",
                    'kr' => "기타 정보.",
                ],
            ],
            'bot' => [
                'success' => [
                    'uz' => "Promocode muvaffaqiyatli ro'yhatga olindi. Telegram bot orqali tasdiqlandi.",
                    'ru' => "Промокод успешно зарегистрирован через Telegram бота.",
                    'kr' => "프로모코드가 성공적으로 등록되었습니다. 텔레그램 봇을 통해 확인되었습니다.",
                ],
                'fail' => [
                    'uz' => "Promocode ro'yhatga olinmadi. Iltimos, yana bir bor urunib ko'ring.",
                    'ru' => "Промокод не зарегистрирован. Пожалуйста, попробуйте еще раз.",
                    'kr' => "프로모코드를 등록하지 못했습니다. 다시 시도해 주세요.",
                ],
                'claim' => [
                    'uz' => "Promocodedan avval foydalanilgan",
                    'ru' => "Ваша заявка на промокод успешно принята.",
                    'kr' => "프로모코드가 성공적으로 처리되었습니다.",
                ],
                'info' => [
                    'uz' => "Promo-kodingiz haqida qo'shimcha ma'lumot: ...",
                    'ru' => "Дополнительная информация о вашем промокоде: ...",
                    'kr' => "귀하의 프로모코드에 대한 추가 정보: ...",
                ],
                'etc' => [
                    'uz' => "Boshqa xabarlar haqida ma'lumot.",
                    'ru' => "Дополнительная информация.",
                    'kr' => "기타 정보.",
                ],
            ],
            'all' => [
                'success' => [
                    'uz' => "Promocode muvaffaqiyatli ro'yhatga olindi. Barcha platformalar uchun tasdiqlandi.",
                    'ru' => "Промокод успешно зарегистрирован. Подтверждено на всех платформах.",
                    'kr' => "프로모코드가 성공적으로 등록되었습니다. 모든 플랫폼에서 확인되었습니다.",
                ],
                'fail' => [
                    'uz' => "Promocode ro'yhatga olinmadi. Iltimos, yana bir bor urunib ko'ring.",
                    'ru' => "Промокод не зарегистрирован. Пожалуйста, попробуйте еще раз.",
                    'kr' => "프로모코드를 등록하지 못했습니다. 다시 시도해 주세요.",
                ],
                'claim' => [
                    'uz' => "Promocodedan avval foydalanilgan",
                    'ru' => "Ваша заявка на промокод успешно принята.",
                    'kr' => "프로모코드가 성공적으로 처리되었습니다.",
                ],
                'info' => [
                    'uz' => "Promo-kodingiz haqida qo'shimcha ma'lumot: ...",
                    'ru' => "Дополнительная информация о вашем промокоде: ...",
                    'kr' => "귀하의 프로모코드에 대한 추가 정보: ...",
                ],
                'etc' => [
                    'uz' => "Boshqa xabarlar haqida ma'lumot.",
                    'ru' => "Дополнительная информация.",
                    'kr' => "기타 정보.",
                ],
            ],
        ];

        // Platforma va type bo'yicha xabarni qaytaradi
        return $messages[$platform][$type] ?? ['uz' => 'No message available', 'ru' => 'Нет сообщения', 'kr' => '메시지가 없습니다'];
    }
}
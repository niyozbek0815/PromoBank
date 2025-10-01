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
                    'en' => "Promocode successfully registered. Confirmed via SMS.",
                    'kr' => "Промокод муваффақиятли рўйхатга олинди. SMS орқали тасдиқланди.",
                ],
                'fail' => [
                    'uz' => "Afsuski, bu safar sizga sovg‘a chiqmadi. Omadingizni yana sinab ko‘ring!",
                    'ru' => "Промокод не зарегистрирован. Пожалуйста, попробуйте еще раз.",
                    'en' => "Unfortunately, no prize this time. Try again!",
                    'kr' => "Афсуски, бу сафар сизга совға чиқмади. Омадингизни яна синаб кўринг!",
                ],
                'claim' => [
                    'uz' => "Promocodedan avval foydalanilgan.",
                    'ru' => "Промокод уже был использован ранее.",
                    'en' => "The promocode has already been used.",
                    'kr' => "Промокоддан аввал фойдаланилган.",
                ],
                'info' => [
                    'uz' => "Promo-kodingiz haqida qo'shimcha ma'lumot: ...",
                    'ru' => "Дополнительная информация о вашем промокоде: ...",
                    'en' => "Additional information about your promocode: ...",
                    'kr' => "Промо-кодингиз ҳақида қўшимча маълумот: ...",
                ],
                'etc' => [
                    'uz' => "Boshqa xabarlar haqida ma'lumot.",
                    'ru' => "Дополнительная информация.",
                    'en' => "Additional information.",
                    'kr' => "Бошқа хабарлар ҳақида маълумот.",
                ],
            ],
            'mobile' => [
                'success' => [
                    'uz' => "Promocode muvaffaqiyatli ro'yhatga olindi.",
                    'ru' => "Промокод успешно зарегистрирован.",
                    'en' => "Promocode successfully registered.",
                    'kr' => "Промокод муваффақиятли рўйхатга олинди.",
                ],
                'fail' => [
                    'uz' => "Promocode ro'yhatga olinmadi. Iltimos, yana bir bor urunib ko'ring.",
                    'ru' => "Промокод не зарегистрирован. Пожалуйста, попробуйте еще раз.",
                    'en' => "Promocode not registered. Please try again.",
                    'kr' => "Промокод рўйхатга олинмади. Илтимос, яна бир бор уриниб кўринг.",
                ],
                'claim' => [
                    'uz' => "Promocodedan avval foydalanilgan.",
                    'ru' => "Промокод уже был использован ранее.",
                    'en' => "The promocode has already been used.",
                    'kr' => "Промокоддан аввал фойдаланилган.",
                ],
                'info' => [
                    'uz' => "Promo-kodingiz haqida qo'shimcha ma'lumot: ...",
                    'ru' => "Дополнительная информация о вашем промокоде: ...",
                    'en' => "Additional information about your promocode: ...",
                    'kr' => "Промо-кодингиз ҳақида қўшимча маълумот: ...",
                ],
                'etc' => [
                    'uz' => "Boshqa xabarlar haqida ma'lumot.",
                    'ru' => "Дополнительная информация.",
                    'en' => "Additional information.",
                    'kr' => "Бошқа хабарлар ҳақида маълумот.",
                ],
            ],
            'bot' => [
                'success' => [
                    'uz' => "Promocode muvaffaqiyatli ro'yhatga olindi. Telegram bot orqali tasdiqlandi.",
                    'ru' => "Промокод успешно зарегистрирован через Telegram бота.",
                    'en' => "Promocode successfully registered via Telegram bot.",
                    'kr' => "Промокод муваффақиятли рўйхатга олинди. Telegram бот орқали тасдиқланди.",
                ],
                'fail' => [
                    'uz' => "Promocode ro'yhatga olinmadi. Iltimos, yana bir bor urunib ko'ring.",
                    'ru' => "Промокод не зарегистрирован. Пожалуйста, попробуйте еще раз.",
                    'en' => "Promocode not registered. Please try again.",
                    'kr' => "Промокод рўйхатга олинмади. Илтимос, яна бир бор уриниб кўринг.",
                ],
                'claim' => [
                    'uz' => "Promocodedan avval foydalanilgan.",
                    'ru' => "Промокод уже был использован ранее.",
                    'en' => "The promocode has already been used.",
                    'kr' => "Промокоддан аввал фойдаланилган.",
                ],
                'info' => [
                    'uz' => "Promo-kodingiz haqida qo'shimcha ma'lumot: ...",
                    'ru' => "Дополнительная информация о вашем промокоде: ...",
                    'en' => "Additional information about your promocode: ...",
                    'kr' => "Промо-кодингиз ҳақида қўшимча маълумот: ...",
                ],
                'etc' => [
                    'uz' => "Boshqa xabarlar haqida ma'lumot.",
                    'ru' => "Дополнительная информация.",
                    'en' => "Additional information.",
                    'kr' => "Бошқа хабарлар ҳақида маълумот.",
                ],
            ],
            'all' => [
                'success' => [
                    'uz' => "Promocode muvaffaqiyatli ro'yhatga olindi. Barcha platformalar uchun tasdiqlandi.",
                    'ru' => "Промокод успешно зарегистрирован. Подтверждено на всех платформах.",
                    'en' => "Promocode successfully registered. Confirmed on all platforms.",
                    'kr' => "Промокод муваффақиятли рўйхатга олинди. Барча платформаларда тасдиқланди.",
                ],
                'fail' => [
                    'uz' => "Promocode ro'yhatga olinmadi. Iltimos, yana bir bor urunib ko'ring.",
                    'ru' => "Промокод не зарегистрирован. Пожалуйста, попробуйте еще раз.",
                    'en' => "Promocode not registered. Please try again.",
                    'kr' => "Промокод рўйхатга олинмади. Илтимос, яна бир бор уриниб кўринг.",
                ],
                'claim' => [
                    'uz' => "Promocodedan avval foydalanilgan.",
                    'ru' => "Промокод уже был использован ранее.",
                    'en' => "The promocode has already been used.",
                    'kr' => "Промокоддан аввал фойдаланилган.",
                ],
                'info' => [
                    'uz' => "Promo-kodingiz haqida qo'shimcha ma'lumot: ...",
                    'ru' => "Дополнительная информация о вашем промокоде: ...",
                    'en' => "Additional information about your promocode: ...",
                    'kr' => "Промо-кодингиз ҳақида қўшимча маълумот: ...",
                ],
                'etc' => [
                    'uz' => "Boshqa xabarlar haqida ma'lumot.",
                    'ru' => "Дополнительная информация.",
                    'en' => "Additional information.",
                    'kr' => "Бошқа хабарлар ҳақида маълумот.",
                ],
            ],
        ];

        // Platforma va type bo'yicha xabarni qaytaradi
        return $messages[$platform][$type]
            ?? ['uz' => 'Xabar topilmadi', 'ru' => 'Сообщение не найдено', 'en' => 'Message not found', 'kr' => 'Хабар топилмади'];
    }
}

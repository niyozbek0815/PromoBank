<?php

namespace Database\Seeders;

use App\Models\Messages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MessagesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $definitions = [
            'promo' => [
                [
                    'status' => 'claim',
                    'message' => [
                        'uz' => "Promokod: :code allaqachon foydalanilgan.",
                        'ru' => "Промокод: :code уже использован.",
                        'en' => "Promo code: :code has already been used.",
                        'kr' => "Промокод: :code аллақачон фойдаланилган.",
                    ],
                    'sms' => "Promokod: :code allaqachon foydalanilgan.",
                ],
                [
                    'status' => 'pending',
                    'message' => [
                        'uz' => "Promokod: :code qabul qilindi. Natija tez orada e’lon qilinadi.",
                        'ru' => "Промокод: :code принят. Результаты будут объявлены скоро.",
                        'en' => "Promo code: :code accepted. The result will be announced soon.",
                        'kr' => "Промокод: :code қабул қилинди. Натижа тез орада эълон қилинади.",
                    ],
                    'sms' => "Promokod: :code qabul qilindi. Natija tez orada e’lon qilinadi.",
                ],
                [
                    'status' => 'invalid',
                    'message' => [
                        'uz' => "Noto‘g‘ri promokod: :code. Iltimos, qaytadan tekshirib kiriting.",
                        'ru' => "Неверный промокод: :code. Пожалуйста, проверьте и введите снова.",
                        'en' => "Invalid promo code: :code. Please check and try again.",
                        'kr' => "Нотўғри промокод: :code. Илтимос, қайтадан текшириб киритинг.",
                    ],
                    'sms' => "Noto‘g‘ri promokod: :code. Iltimos, qaytadan tekshirib kiriting.",
                ],
                [
                    'status' => 'win',
                    'message' => [
                        'uz' => "Tabriklaymiz! Siz (:prize) yutdingiz. Tafsilotlar uchun operatorlarimiz bog‘lanadi.",
                        'ru' => "Поздравляем! Вы выиграли (:prize). Наши операторы скоро свяжутся с вами.",
                        'en' => "Congratulations! You won (:prize). Our operators will contact you soon.",
                        'kr' => "Табриклаймиз! Сиз (:prize) ютдингиз. Тафсилотлар учун операторларимиз боғланади.",
                    ],
                    'sms' => "Tabriklaymiz! Siz (:prize) yutdingiz. Tafsilotlar uchun operatorlarimiz bog‘lanadi.",
                ],
                [
                    'status' => 'fail',
                    'message' => [
                        'uz' => "Promocode ro'yhatga olinmadi. Iltimos, yana bir bor urunib ko'ring.",
                        'ru' => "Промокод не зарегистрирован. Пожалуйста, попробуйте еще раз.",
                        'en' => "Promocode not registered. Please try again.",
                        'kr' => "Промокод рўйхатга олинмади. Илтимос, яна бир бор уриниб кўринг.",
                    ],
                    'sms' => "Promocode ro'yhatga olinmadi. Iltimos, yana bir bor urunib ko'ring.",
                ],
                [
                    'status' => 'lose',
                    'message' => [
                        'uz' => "Afsus, :code promokod yutuq bermadi. Yana urinib ko‘ring!",
                        'ru' => "Увы, промокод :code не принес выигрыш. Попробуйте ещё раз!",
                        'en' => "Unfortunately, promo code :code did not win. Try again!",
                        'kr' => "Афсус, :code промокод ютуқ бермади. Яна уриниб кўринг!",
                    ],
                    'sms' => "Afsus, :code promokod yutuq bermadi. Yana urinib ko‘ring!",
                ],
            ],
            'receipt' => [
                [
                    'status' => 'claim',
                    'message' => [
                        'uz' => "Ushbu chek allaqachon ro‘yxatdan o‘tkazilgan.",
                        'ru' => "Этот чек уже зарегистрирован.",
                        'en' => "This receipt has already been registered.",
                        'kr' => "Ушбу чек аллақачон рўйхатдан ўтказилган.",
                    ],
                ],
                [
                    'status' => 'pending',
                    'message' => [
                        'uz' => "Chekingiz qabul qilindi. Yutuq natijasi tez orada e’lon qilinadi.",
                        'ru' => "Ваш чек принят. Результаты будут объявлены скоро.",
                        'en' => "Your receipt has been accepted. Results will be announced soon.",
                        'kr' => "Чекингиз қабул қилинди. Ютуқ натижаси тез орада эълон қилинади.",
                    ],
                ],
                [
                    'status' => 'invalid',
                    'message' => [
                        'uz' => "Chek ma’lumotlari noto‘g‘ri yoki o‘qilmadi. Iltimos, yana bir bor aniqroq suratga oling.",
                        'ru' => "Данные чека неверные или не читаются. Пожалуйста, сделайте более чёткое фото.",
                        'en' => "Receipt data is invalid or unreadable. Please take a clearer photo.",
                        'kr' => "Чек маълумотлари нотўғри ёки ўқилмади. Илтимос, яна бир бор аниқроқ суратга олинг.",
                    ],
                ],
                [
                    'status' => 'win',
                    'message' => [
                        'uz' => "Tabriklaymiz! Chekingiz orqali siz (:prize) yutdingiz. Operatorlarimiz tez orada siz bilan bog‘lanadi.",
                        'ru' => "Поздравляем! Ваш чек выиграл (:prize). Наши операторы скоро свяжутся с вами.",
                        'en' => "Congratulations! Your receipt won (:prize). Our operators will contact you soon.",
                        'kr' => "Табриклаймиз! Чекингиз орқали сиз (:prize) ютдингиз. Операторларимиз тез орада сиз билан боғланади.",
                    ],
                ],
                [
                    'status' => 'lose',
                    'message' => [
                        'uz' => "Afsus, bu chekda yutuq yo‘q. Yana boshqa xarid cheklari bilan urinib ko‘ring!",
                        'ru' => "Увы, этот чек не выиграл. Попробуйте с другими чеками!",
                        'en' => "Unfortunately, this receipt did not win. Try with other receipts!",
                        'kr' => "Афсус, бу чекда ютуқ йўқ. Яна бошқа харид чеклари билан уриниб кўринг!",
                    ],
                ],
                [
                    'status' => 'fail',
                    'message' => [
                        'uz' => "Chek ro'yhatga olinmadi. Iltimos, yana bir bor urinib ko'ring.",
                        'ru' => "Чек не зарегистрирован. Пожалуйста, попробуйте еще раз.",
                        'en' => "Receipt not registered. Please try again.",
                        'kr' => "Чек рўйхатга олинмади. Илтимос, яна бир бор уриниб кўринг.",
                    ],
                ],
            ],
        ];

        $insertData = [];
        $channels = Messages::CHANNELS; // model constant
        foreach ($channels as $channel) {
            foreach ($definitions as $type => $messages) {
                if ($channel === 'sms' && $type !== 'promo') {
                    continue;
                }

                foreach ($messages as $msg) {
                    $messageValue = $channel === 'sms'
                        ? ($msg['sms'] ?? $msg['message']['uz'])
                        : json_encode($msg['message'], JSON_UNESCAPED_UNICODE);

                    $insertData[] = [
                        'scope_type' => 'platform',
                        'scope_id' => null,
                        'channel' => $channel,
                        'type' => $type,
                        'status' => $msg['status'],
                        'message' => $messageValue,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        Messages::insert($insertData);
    }
}

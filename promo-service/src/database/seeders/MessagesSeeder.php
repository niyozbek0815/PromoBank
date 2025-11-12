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
            'secret-number' => [

                [
                    'status' => 'invalid',
                    'message' => [
                        'uz' => "❌ Noto‘g‘ri kod: :code. Agar hozir MY5 telekanalida sirli raqam e'lon qilingan bo‘lsa, uni 1 daqiqa ichida botga yuboring. Iltimos, kodni tekshirib qayta kiriting.",
                        'ru' => "❌ Неверный код: :code. Если сейчас на канале MY5 был показан секретный код, отправьте его в бот в течение 1 минуты. Пожалуйста, проверьте код и попробуйте снова.",
                        'en' => "❌ Invalid code: :code. If MY5 has just shown the secret code now, send it to the bot within 1 minute. Please check the code and try again.",
                        'kr' => "❌ Нотўғри код: :code. Агар ҳозир MY5 телеканалидан сирли код эълон қилинган бўлса, уни 1 дақиқа ичида ботга юборинг. Илтимос, кодни текшириб қайта киритинг.",
                    ],
                    'sms' => "❌ Noto‘g‘ri kod: :code. Agar MY5da raqam e'lon qilinsa, uni 1 daqiqa ichida yuboring.",
                ],
                [
                    'status' => 'claim',
                    'message' => [
                        'uz' => "Sirli raqam: :code dan avval foydalanilgansiz.",
                        'ru' => "Секретный номер: :code уже был использован ранее.",
                        'en' => "Secret number: :code has already been used.",
                        'kr' => "Сирли рақам: :code дан аввал фойдаланилгансиз.",
                    ],
                    'sms' => "Sirli raqam: :code dan avval foydalanilgansiz.",
                ],
                [
                    'status' => 'inactive_window',
                    'message' => [
                        'uz' => "⏳ Sirli raqam hozir aktiv emas. MY5 telekanalida sirli raqamlar 16:00–18:00 orasida e'lon qilinadi — e'lon qilingandan so‘ng 1 daqiqa ichida qayta yuboring.",
                        'ru' => "⏳ Секретный код сейчас не активен. Коды транслируются на MY5 с 16:00 до 18:00 — отправьте код в течение 1 минуты после его показа.",
                        'en' => "⏳ Secret code is currently inactive. Codes on MY5 air between 16:00–18:00 — send the code within 1 minute after it is shown.",
                        'kr' => "⏳ Сирли код ҳозир фаол эмас. MY5да кодлар 16:00–18:00 оралиғида эълон қилинади — эълон қилингач 1 дақиқа ичида юборинг.",
                    ],
                    'sms' => "Sirli raqam hozir aktiv emas. MY5da 16:00–18:00 orasida e'lon qilinadi — e'lon qilingach 1 daqiqa ichida yuboring.",
                ],
                [
                    'status' => 'win',
                    'message' => [
                        'uz' => "Tabriklaymiz! Siz sirli raqamni topdingiz va (:prize) ball yutdingiz.",
                        'ru' => "Поздравляем! Вы угадали секретное число и выиграли (:prize) баллов.",
                        'en' => "Congratulations! You found the secret number and won (:prize) points.",
                        'kr' => "Табриклаймиз! Сиз сирли рақамни топдингиз ва (:prize) балл ютдингиз.",
                    ],
                ],
                [
                    'status' => 'step0',
                    'message' => [
                        'uz' => "👏 Ajoyib boshladingiz! Sizda allaqachon ball bor, lekin Promobank reytingida yuqoriga chiqish uchun yanada harakat qilish kerak. 💡 Sirli raqamlarni ko‘proq kiriting yoki yangi do‘stlarni taklif qiling — har biri sizga qo‘shimcha ball beradi. Omad siz tomonda!",
                        'ru' => "👏 Отличное начало! У вас уже есть баллы, но чтобы подняться в рейтинге Promobank, нужно постараться ещё. 💡 Вводите больше секретных кодов или приглашайте друзей — каждый приносит дополнительные баллы. Удача на вашей стороне!",
                        'en' => "👏 Great start! You already have some points, but to climb higher in the Promobank ranking, you need to push further. 💡 Enter more secret codes or invite friends — each earns you extra points. Luck is on your side!",
                        'kr' => "👏 Ажойиб бошланди! Сизда аллақачон баллар бор, лекин Promobank рейтингда юқорироқ чиқиш учун яна ҳаракат қилиш керак. 💡 Кўпроқ сирли рақам киритинг ёки дўстларни таклиф қилинг — ҳар бири сизга қўшимча балл олиб келади. Омад сизнинг томонда!",
                    ],
                    'sms' => "Ajoyib boshladingiz! Promobank reytingida yuqoriga chiqish uchun sirli raqam kiriting yoki do‘st taklif qiling.",
                ],

                [
                    'status' => 'step1',
                    'message' => [
                        'uz' => "💪 Zo‘r ketayapsiz! Siz 10+ ball to‘pladingiz va Promobank reytingida o‘z o‘rningizni egalladingiz! 🎯 Endi maqsad — 30 ballni zabt etish va kuchli ishtirokchilar orasiga kirish. Ko‘proq sirli raqam kiriting, do‘stlaringizni taklif qiling — sovrinlar sizga tobora yaqinlashmoqda 🏆",
                        'ru' => "💪 Отлично идёте! У вас уже более 10 баллов и вы заняли место в рейтинге Promobank! 🎯 Следующая цель — 30 баллов и вход в топ участников. Вводите больше кодов, приглашайте друзей — призы всё ближе 🏆",
                        'en' => "💪 Great job! You’ve earned 10+ points and secured your place in the Promobank ranking! 🎯 Next goal — reach 30 points and join the top players. Enter more codes, invite friends — prizes are getting closer 🏆",
                        'kr' => "💪 Зўр кетяпсиз! Сиз 10+ балл тўпладингиз ва Promobank рейтингда ўз ўрнингизни эгалладингиз! 🎯 Эндиликда мақсад — 30 баллга етишиш ва кучли қатнашчилар орасида бўлиш. Кўпроқ сирли рақам киритинг, дўстларни таклиф қилинг — совринлар сизни кутяпти 🏆",
                    ],
                    'sms' => "Zo‘r ketayapsiz! 10+ ball to‘pladingiz, endi maqsad — 30 ball. Omad siz tomonda!",
                ],

                [
                    'status' => 'step2',
                    'message' => [
                        'uz' => "🎉 Zo‘r natija! Siz 30+ ball to‘pladingiz — endi Promobank reytingining yuqori qismidasiz! 🔥 50 ballga yaqinlashyapsiz, demak sovrinlar bir necha qadamingizda. Ko‘proq sirli raqam kiriting va do‘stlarni taklif etishda davom eting — g‘olib bo‘lish imkoniyatingiz yuqori!",
                        'ru' => "🎉 Отличный результат! У вас уже более 30 баллов — вы в верхней части рейтинга Promobank! 🔥 Осталось немного до 50 баллов — призы совсем близко. Вводите коды, приглашайте друзей и увеличивайте шанс на победу!",
                        'en' => "🎉 Amazing result! You’ve earned 30+ points — you’re now in the top of the Promobank ranking! 🔥 Getting close to 50 points — prizes are just steps away. Keep entering codes and inviting friends — your chance to win is high!",
                        'kr' => "🎉 Зўр натижа! Сиз 30+ балл тўпладингиз — эндиликда Promobank рейтингнинг юқори қисмидасиз! 🔥 50 баллга яқинлашяпсиз, демак совринлар бир неча қадамда. Кўпроқ сирли рақам киритинг ва дўстларни таклиф этишда давом этинг!",
                    ],
                    'sms' => "30+ ballga erishdingiz! Endi siz Promobank reytingining yuqori qismidasiz. Davom eting!",
                ],

                [
                    'status' => 'step_won',
                    'message' => [
                        'uz' => "🏆 Siz yutuqli o‘yinda ishtirok etasiz! Tabriklaymiz 🎉 Ballaringiz Promobank sovrinli o‘yin tizimiga kiritildi — g‘oliblar orasida bo‘lish imkoniyatingiz yuqori!",
                        'ru' => "🏆 Вы участвуете в призовой игре! Поздравляем 🎉 Ваши баллы учтены в системе Promobank — у вас высокий шанс стать победителем!",
                        'en' => "🏆 You are now part of the prize draw! Congratulations 🎉 Your points have been added to the Promobank prize system — you have a strong chance to win!",
                        'kr' => "🏆 Сиз ютуқли ўйинда иштирок этасиз! Табриклаймиз 🎉 Балларингиз Promobank совринли тизимига киритилди — ғолиб бўлиш имкониятингиз юқори!",
                    ],
                    'sms' => "Tabriklaymiz! Siz yutuqli o‘yinda ishtirok etasiz. Ballaringiz tizimga qo‘shildi.",
                ],

            ],

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
                        'uz' => "Tabriklaymiz! Chekingiz orqali siz (:prize) yutdingiz. Operatorlarimiz tez orada siz bilan bog‘lanadi.",
                        'ru' => "Поздравляем! Ваш чек выиграл (:prize). Наши операторы скоро свяжутся с вами.",
                        'en' => "Congratulations! Your receipt won (:prize). Our operators will contact you soon.",
                        'kr' => "Табриклаймиз! Чекингиз орқали сиз (:prize) ютдингиз. Операторларимиз тез орада сиз билан боғланади.",
                    ],
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

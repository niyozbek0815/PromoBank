<?php
namespace App\Telegram\Services;

use Illuminate\Support\Facades\Cache;

class Translator
{
    protected string $defaultLanguage = 'uz';

    protected array $messages = [
        'language_selection' => [
            'uz' => "🇺🇿 O'zbekcha",
            'ru' => "🇷🇺 Русский",
            'kr' => "🇺🇿 Кирилл",
            'en' => "🇬🇧 English",
        ],
        "language_prompt" => [
            'uz' => "🌐 Iltimos, tilni tanlang:",
            'ru' => "🌐 Пожалуйста, выберите язык:",
            'kr' => "🌐 Илтимос, тилни танланг:",
            'en' => "🌐 Please, select your language:",
        ],
        'start' => [
            'uz' => "Assalomu alaykum, Promobank platformasining Telegramdagi rasmiy botiga xush kelibsiz!",
            'ru' => "Здравствуйте, добро пожаловать в официальный бот платформы Promobank в Telegram!",
            'kr' => "Ассалому алайкум, Promobank платформасининг Telegramдаги расмий ботига хуш келибсиз!",
            'en' => "Hello, welcome to the official Promobank platform bot on Telegram!",
        ],
        'welcome' => [
            'uz' => "🎉 Promobank platformasiga xush kelibsiz! Endilikda siz bu platformadagi barcha loyihalarda qatnasha olasiz.",
            'ru' => "🎉 Добро пожаловать на платформу Promobank! Теперь вы можете участвовать во всех проектах на этой платформе.",
            'kr' => "🎉 Промобанк платформасига хуш келибсиз! Энди сиз ушбу платформадаги барча лойиҳаларда қатнашишингиз мумкин.",
            'en' => "🎉 Welcome to the Promobank platform! You can now participate in all projects on this platform.",
        ],
        "ask_name" => [
            'uz' => "📋 Iltimos, ro'yxatdan o'tish uchun familiya va ismingizni kiriting: Namuna: Abdullayev Abdulla",
            'ru' => "📋 Пожалуйста, введите вашу фамилию и имя для регистрации: Пример: Иванов Иван",
            'kr' => "📋 Илтимос, рўйхатдан ўтиш учун фамилия ва исмингизни киритинг: Намуна: Абдуллаев Абдулла",
            'en' => "📋 Please enter your surname and name for registration: Example: Smith John",
        ],
        'invalid_name_format' => [
            'uz' => "❌ Noto‘g‘ri format. Iltimos, faqat harflardan iborat bo‘lgan va kamida 3 ta belgi kiriting.",
            'ru' => "❌ Неверный формат. Пожалуйста, введите только буквы и минимум 3 символа.",
            'kr' => "❌ Нотўғри формат. Илтимос, фақат ҳарфлардан иборат бўлган ва камида 3 та белгидан иборат бўлган матн киритинг.",
            'en' => "❌ Invalid format. Please enter only letters with at least 3 characters.",
        ],
        'name_received' => [
            'uz' => "✅ Ismingiz qabul qilindi.",
            'ru' => "✅ Ваше имя принято.",
            'kr' => "✅ Исмингиз қабул қилинди.",
            'en' => "✅ Your name has been received.",
        ],
        'ask_phone' => [
            'uz' => "📱 Telefon raqamingizni quyidagi tugmani bosish orqali yuboring",
            'ru' => "📱 Отправьте ваш номер телефона, нажав на кнопку ниже",
            'kr' => "📱 Телефон рақамингизни қуйидаги тугмани босиш орқали юборинг",
            'en' => "📱 Please send your phone number by clicking the button below",
        ],
        'share_phone_button' => [
            'uz' => '📱 Raqamni yuborish',
            'ru' => '📱 Отправить номер',
            'kr' => '📱 Рақамни юбориш',
            'en' => '📱 Send Number',
        ],
        'invalid_phone_format' => [
            'uz' => "❗️Iltimos, faqat telefon raqamingizni quyidagi tugmani bosish orqali biz ulashing.",
            'ru' => "❗️Пожалуйста, отправьте только номер телефона, нажав на кнопку ниже.",
            'kr' => "❗️Илтимос, фақат телефон рақамингизни қуйидаги тугмани босиш орқали юборинг.",
            'en' => "❗️ Please, send only your phone number by clicking the button below.",
        ],

        'phone_received' => [
            'uz' => "📱 Telefon raqam qabul qilindi.",
            'ru' => "📱 Ваш номер телефона принят.",
            'kr' => "📱 Телефон рақамингиз қабул қилинди.",
            'en' => "📱 Phone number received.",
        ],

        'already_registered' => [
            'uz' => "✅ Siz bizning Promobank platformamizda avval ro'yxatdan o'tganligingiz tufayli registratsiya jarayoni muvaffaqiyatli yakunlandi.",
            'ru' => "✅ Так как вы уже зарегистрированы на нашей платформе Promobank, процесс регистрации успешно завершён.",
            'kr' => "✅ Сиз Promobank платформамизда аввал рўйхатдан ўтганингиз сабабли рўйхатдан ўтиш жараёни муваффақиятли якунланди.",
            'en' => "✅ Since you are already registered on our Promobank platform, the registration process has been successfully completed.",
        ],

        'ask_phone2' => [
            'uz' => "📱 Biz siz bilan bog‘lanishimiz uchun qo‘shimcha telefon raqamini kiriting yoki bu bosqichni o'tkazib yuboring.",
            'ru' => "📱 Пожалуйста, введите дополнительный номер телефона, чтобы мы могли связаться с вами, или пропустите этот шаг.",
            'kr' => "📱 Биз сиз билан боғланишимиз учун қўшимча телефон рақамини киритинг ёки ушбу босқични ўтказиб юборинг.",
            'en' => "📱 Please enter an additional phone number so we can contact you, or skip this step.",
        ],
        'invalid_phone2_format' => [
            'uz' => "❗️Noto‘g‘ri format kiritildi. Iltimos, telefon raqamni +998901234567 tarzida yuboring.",
            'ru' => "❗️Неверный формат. Пожалуйста, отправьте номер телефона в формате +998901234567.",
            'kr' => "❗️Нотўғри формат киритилди. Илтимос, телефон рақамни +998901234567 кўринишида юборинг.",
            'en' => "❗️ Invalid format. Please send the phone number in the format +998901234567.",
        ],
        'phone2_received' => [
            'uz' => "✅ Qo'shimcha  raqam qabul qilindi.",
            'ru' => "✅ Дополнительный номер принят.",
            'kr' => "✅ Қўшимча рақам қабул қилинди.",
            'en' => "✅ Additional phone number received.",
        ],
        'ask_gender' => [
            'uz' => "👫 Iltimos, jinsingizni tanlang",
            'ru' => "👫 Пожалуйста, выберите ваш пол",
            'kr' => "👫 Илтимос, жинсингизни танланг",
            'en' => "👫 Please select your gender",
        ],
        'gender_male' => [
            'uz' => '👨 Erkak',
            'ru' => '👨 Мужчина',
            'kr' => '👨 Эркак',
            'en' => '👨 Male',
        ],
        'gender_female' => [
            'uz' => '👩 Ayol',
            'ru' => '👩 Женщина',
            'kr' => '👩 Аёл',
            'en' => '👩 Female',
        ],
        'invalid_gender_format' => [
            'uz' => "❗ Iltimos, tugmalardan birini tanlang:",
            'ru' => "❗ Пожалуйста, выберите один из предложенных вариантов:",
            'kr' => "❗ Илтимос, берилган тугмалардан бирини танланг:",
            'en' => "❗ Please select one of the options:",
        ],

        'gender_received' => [
            'uz' => "✅ Jins muvaffaqiyatli tanlandi.",
            'ru' => "✅ Пол успешно выбран.",
            'kr' => "✅ Жинс муваффақиятли танланди.",
            'en' => "✅ Gender successfully selected.",
        ],
        'ask_region' => [
            'uz' => "📍 Iltimos, yashash hududingizni belgilang.",
            'ru' => "📍 Пожалуйста, укажите регион вашего проживания.",
            'kr' => "📍 Илтимос, яшаш ҳудудингизни белгиланг.",
            'en' => "📍 Please specify your living region.",
        ],
        'invalid_region_choice' => [
            'uz' => "❌ Noto‘g‘ri tanlov. Iltimos, yuqoridagi ro‘yxatdan hududni tanlang.",
            'ru' => "❌ Неверный выбор. Пожалуйста, выберите регион из списка выше.",
            'kr' => "❌ Нотўғри танлов. Илтимос, юқоридаги рўйхатдан ҳудудни танланг.",
            'en' => "❌ Invalid choice. Please select a region from the list above.",
        ],
        'region_received' => [
            'uz' => "✅ Hudud muvaffaqiyatli tanlandi.",
            'ru' => "✅ Регион успешно выбран.",
            'kr' => "✅ Ҳудуд муваффақиятли танланди.",
            'en' => "✅ Region successfully selected.",
        ],
        'ask_district' => [
            'uz' => "🏘 Iltimos, o'z yashash shahringiz yoki tumaningizni belgilang.",
            'ru' => "🏘 Пожалуйста, укажите ваш город или район проживания.",
            'kr' => "🏘 Илтимос, ўз яшаш шаҳриингиз ёки туманиингизни белгиланг.",
            'en' => "🏘 Please specify your city or district of residence.",
        ],
        'invalid_district_choice' => [
            'uz' => "❌ Noto‘g‘ri tanlov. Iltimos, yuqoridagi ro‘yxatdan tuman yoki shaharni tanlang.",
            'ru' => "❌ Неверный выбор. Пожалуйста, выберите район или город из списка выше.",
            'kr' => "❌ Нотўғри танлов. Илтимос, юқоридаги рўйхатдан туман ёки шаҳарни танланг.",
            'en' => "❌ Invalid choice. Please select a district or city from the list above.",
        ],
        'district_received' => [
            'uz' => "✅ Shahar yoki tuman muvaffaqiyatli tanlandi.",
            'ru' => "✅ Город или район успешно выбран.",
            'kr' => "✅ Шаҳар ёки туман муваффақиятли танланди.",
            'en' => "✅ City or district successfully selected.",
        ],
        'ask_birthdate' => [
            'uz' => "📅 Tug‘ilgan sanangizni kun.oy.yil formatida yuboring (masalan, 31.12.2000)",
            'ru' => "📅 Отправьте дату своего рождения в формате день.месяц.год (например, 31.12.2000)",
            'kr' => "📅 Туғилган санаингизни кун.ой.йил форматда юборинг (масалан, 31.12.2000)",
            'en' => "📅 Please send your birth date in the format day.month.year (e.g., 31.12.2000)",
        ],

        'invalid_birthdate_format' => [
            'uz' => "❗️Noto‘g‘ri format kiritildi. Iltimos, Tug‘ilgan sanangizni kun.oy.yil formatida yuboring. Namuna: 31.12.2000",
            'ru' => "❗️Неверный формат. Пожалуйста, отправьте дату рождения в формате день.месяц.год. Пример: 31.12.2000",
            'kr' => "❗️Нотўғри формат киритилди. Илтимос, туғилган санаингизни кун.ой.йил форматда юборинг. Намуна: 31.12.2000",
            'en' => "❗️ Invalid format. Please send your birth date in the format day.month.year. Example: 31.12.2000",
        ],
        'birthdate_received' => [
            'uz' => "✅ Tug‘ilgan sana qabul qilindi.",
            'ru' => "✅ Дата рождения принята.",
            'kr' => "✅ Туғилган сана қабул қилинди.",
            'en' => "✅ Birth date received.",
        ],
        'ask_offer' => [
            'uz' => '📄 Loyiha ofertasi bilan tanishib chiqing',
            'ru' => '📄 Ознакомьтесь с офертой проекта',
            'kr' => '📄 Лойиҳа офертаси билан танишиб чиқинг',
            'en' => '📄 Please review the project offer',
        ],
        'invalid_offer_format' => [
            'uz' => "❗️ Iltimos, quyidagi tugmani bosing.",
            'ru' => "❗️ Пожалуйста, нажмите на кнопку ниже.",
            'kr' => "❗️ Илтимос, қуйидаги тугмани босинг.",
            'en' => "❗️ Please, click the button below.",
        ],
        'offer_received' => [
            'uz' => "✅ Offertaga rozilik bildirildi.",
            'ru' => "✅ Вы согласились с офертой.",
            'kr' => "✅ Лойиҳа офертига розилик билдирилди.",
            'en' => "✅ You have agreed to the offer.",
        ],
        'offer_button' => [
            'uz' => '📄 Oferta',
            'ru' => '📄 Оферта',
            'kr' => '📄 Оферта',
            'en' => '📄 Offer',
        ],

        'open_main_menu' => [
            'uz' => '📋 Asosiy menyu',
            'ru' => '📋 Главное меню',
            'kr' => '📋 Асосий меню',
            'en' => '📋 Main Menu',
        ],
        'menu_promotions' => [
            'uz' => '🎁 Aksiyalar',
            'ru' => '🎁 Акции',
            'kr' => '🎁 Акциялар',
            'en' => '🎁 Promotions',
        ],
        'menu_games' => [
            'uz' => '🎮 O‘yinlar',
            'ru' => '🎮 Игры',
            'kr' => '🎮 Ўйинлар',
            'en' => '🎮 Games',
        ],
        'menu_news' => [
            'uz' => '📰 Yangiliklar',
            'ru' => '📰 Новости',
            'kr' => '📰 Янгиликлар',
            'en' => '📰 News',
        ],
        'menu_social' => [
            'uz' => '🌐 Tarmoqlar',
            'ru' => '🌐 Сети',
            'kr' => '🌐 Тармоқлар',
            'en' => '🌐 Social Networks',
        ],
        'menu_profile' => [
            'uz' => '👤 Profil Sozlamalari',
            'ru' => '👤 Настройки профиля',
            'kr' => '👤 Профил Созламалари',
            'en' => '👤 Profile Settings',
        ],
        'main_menu_title' => [
            'uz' => "📋 Asosiy menyu. Quyidagilardan birini tanlang:",
            'ru' => "📋 Главное меню. Выберите один из пунктов:",
            'kr' => "📋 Асосий меню. Қуйидагилардан бирини танланг:",
            'en' => "📋 Main menu. Please choose one of the following:",
        ],

        'next' => [
            'uz' => 'O‘tkazib yuborish',
            'ru' => 'Пропустить',
            'kr' => 'Ўтказиб юбориш',
            'en' => 'Skip',
        ],
        'confirm' => [
            'uz' => "✅ Tasdiqlash",
            'ru' => "✅ Подтвердить",
            'kr' => "✅ Тасдиқлаш",
            'en' => "✅ Confirm",
        ],
        // Profile section translations
        'profile_title' => [
            'uz' => "Shaxsiy ma'lumotlaringiz:",
            'ru' => "Ваши персональные данные:",
            'kr' => "Шахсий маълумотларингиз:",
            'en' => "Your personal information:",
        ],
        'profile_name' => [
            'uz' => "Ism",
            'ru' => "Имя",
            'kr' => "Исм",
            'en' => "Name",
        ],
        'profile_phone' => [
            'uz' => "Telefon",
            'ru' => "Телефон",
            'kr' => "Телефон",
            'en' => "Phone",
        ],
        'profile_phone2' => [
            'uz' => "Qo‘shimcha telefon raqami",
            'ru' => "Доп. номер",
            'kr' => "Қўшимча телефон рақами",
            'en' => "Additional Phone Number",
        ],
        'profile_region' => [
            'uz' => "Hudud",
            'ru' => "Регион",
            'kr' => "Ҳудуд",
            'en' => "Region",
        ],
        'profile_district' => [
            'uz' => "Tuman",
            'ru' => "Район",
            'kr' => "Туман",
            'en' => "District",
        ],
        'profile_gender' => [
            'uz' => "Jinsi",
            'ru' => "Пол",
            'kr' => "Жинси",
            'en' => "Gender",
        ],
        'profile_birthdate' => [
            'uz' => "Tug‘ilgan sana",
            'ru' => "Дата рождения",
            'kr' => "Туғилган сана",
            'en' => "Date of Birth",
        ],
        'profile_lang' => [
            'uz' => "Tizim tili",
            'ru' => "Язык системы",
            'kr' => "Тизим тили",
            'en' => "System language",
        ],
        'back' => [
            'uz' => "Ortga",
            'ru' => "Назад",
            'kr' => "Орқага",
            'en' => "Back",
        ],
        'profile_update' => [
            'uz' => "✏️ Ma'lumotlarni tahrirlash",
            'ru' => "✏️ Редактирование данных",
            'kr' => "✏️ Маълумотларни таҳрирлаш",
            'en' => "✏️ Edit Information",
        ],
        'profile_update_welcome' => [
            'uz' => "✏️ Shaxsiy ma'lumotlarni tahrirlash boshlandi",
            'ru' => "✏️ Редактирование личных данных началось",
            'kr' => "✏️ Шахсий маълумотларни таҳрирлаш бошланди",
            'en' => "✏️ Personal information editing has started",
        ],
        "profile_update_success" => [
            'uz' => "✅ Shaxsiy ma'lumotlaringiz muvaffaqiyatli yangilandi.",
            'ru' => "✅ Ваши персональные данные успешно обновлены.",
            'kr' => "✅ Шахсий маълумотларингиз муваффақиятли янгиланди.",
            'en' => "✅ Your personal information has been successfully updated.",
        ],
        'social_follow_prompt' => [
            'uz' => "📱 Bizning ijtimoiy tarmoqlarimizga azo bo'ling va kuzatib boring:",
            'ru' => "📱 Подпишитесь на наши социальные сети и следите за нами:",
            'kr' => "📱 Бизинг ижтимоий тармоқларимизга аъзо бўлинг ва кузатиб боринг:",
            'en' => "📱 Follow us on our social networks and stay updated:",
        ],
        'subscription_prompt' => [
            'uz' => "🔔 Iltimos, yangiliklar va aksiyalar haqida xabardor bo'lish uchun obuna bo'ling:",
            'ru' => "🔔 Пожалуйста, подпишитесь, чтобы быть в курсе новостей и акций:",
            'kr' => "🔔 Илтимос, янгиликлар ва акциялар ҳақида хабардор бўлиш учун обуна бўлинг:",
            'en' => "🔔 Please subscribe to stay informed about news and promotions:",
        ],
        'check' => [
            'uz' => '✅ Tekshirish',
            'ru' => '✅ Проверить',
            'kr' => '✅ Текшириш',
            'en' => '✅ Check',
        ],
        'menu_referral' => [
            'uz' => "🤝 Do'stni taklif qilish",
            'ru' => "🤝 Пригласить друга",
            'kr' => "🤝 Дўстни таклиф қилиш",
            'en' => "🤝 Invite a Friend",
        ],
        'refferral_text' => [
            'uz' => "<b>🎉 PROMOBANK — Sizning yutug‘ingiz shu yerda!</b> 💰🔥

PromoBank — bu O‘zbekiston bo‘ylab eng katta yutuqli platforma!
Har bir ishtirokchi <b>pul mukofotlari</b> 🏆, <b>maishiy texnikalar</b> 🏠 va <b>avtomobillar</b> 🚗 yutib olishi mumkin!

❗️Faqat ro‘yxatdan o‘ting va omadni sinab ko‘ring! 😍

Do‘stlaringizni taklif qiling va har bir taklif uchun bonus oling! 💎

Ushbu havolaga kirib Telegram botda ro‘yxatdan o‘ting 👇
👉 ::refferral_link

<b>Boshlang — Omad siz tomonda!</b> 🚀",

            'ru' => "<b>🎉 PROMOBANK — Ваш шанс выиграть прямо здесь!</b> 💰🔥

PromoBank — крупнейшая призовая платформа по всему Узбекистану!
Каждый участник может выиграть <b>денежные призы</b> 🏆, <b>бытовую технику</b> 🏠 и <b>автомобили</b> 🚗!

❗️Просто зарегистрируйтесь и испытайте удачу! 😍

Приглашайте друзей и получайте бонус за каждого приглашённого! 💎

Перейдите по ссылке и зарегистрируйтесь в Telegram-боте 👇
👉 ::refferral_link

<b>Начните прямо сейчас — удача на вашей стороне!</b> 🚀",

            'kr' => "<b>🎉 PROMOBANK — Сизнинг ютуғингиз шу ерда!</b> 💰🔥

PromoBank — бу Ўзбекистон бўйлаб энг катта ютуқли платформа!
Ҳар бир иштирокчи <b>пул мукофотлари</b> 🏆, <b>маиший техникалар</b> 🏠 ва <b>автомобиллар</b> 🚗 ютиб олиши мумкин!

❗️Фақат рўйхатдан ўтинг ва омадингизни синаб кўринг! 😍

Дўстларингизни таклиф қилинг ва ҳар бир таклиф учун бонус олинг! 💎

Қуйидаги ҳавола орқали Telegram ботда рўйхатдан ўтинг 👇
👉 ::refferral_link

<b>Бошланг — Омад сизнинг томонда!</b> 🚀",

            'en' => "<b>🎉 PROMOBANK — Your winnings start here!</b> 💰🔥

PromoBank is the largest reward platform across Uzbekistan!
Every participant has a chance to win <b>cash prizes</b> 🏆, <b>home appliances</b> 🏠, and <b>cars</b> 🚗!

❗️Just register and try your luck! 😍

Invite your friends and earn a bonus for each one you bring! 💎

Join through this link and register in our Telegram bot 👇
👉 ::refferral_link

<b>Start now — luck is on your side!</b> 🚀",
        ],
        'refferral_get_link' => [
            'uz' => "🔗 Sizning referal havolangiz:",
            'ru' => "🔗 Ваша реферальная ссылка:",
            'kr' => "🔗 Сизнинг реферал ҳаволангиз:",
            'en' => "🔗 Your referral link:",
        ],
        'get_bot' => [
            'uz' => "👉 Botga o‘tish",
            'ru' => "👉 Перейти к боту",
            'kr' => "👉 Ботга ўтиш",
            'en' => "👉 Go to Bot",
        ],
        // 'referral_success_text' => [
        //     'uz' => "🎉 Tabriklaymiz! Siz muvaffaqiyatli ro'yxatdan o'tdingiz!\n\n" .
        //     "Sizni ushbu platformaga taklif qilgan foydalanuvchiga minnatdorchilik bildiramiz.\n\n" .
        //     "Endi siz ham referal tizimidan foydalanib, do'stlaringizni taklif qilib promoball yutishingiz mumkin! 💎",
        //     'ru' => "🎉 Поздравляем! Вы успешно зарегистрировались!\n\n" .
        //     "Благодарим пользователя, который пригласил вас на эту платформу.\n\n" .
        //     "Теперь вы также можете использовать реферальную систему, приглашая своих друзей и зарабатывая промобаллы! 💎",
        //     'kr' => "🎉 Табриклаймиз! Сиз муваффақиятли рўйхатдан ўтдингиз!\n\n" .
        //     "Сизни ушбу платформага таклиф қилган фойдаланувчига миннатдорчилик билдирамиз.\n\n" .
        //     "Энди сиз ҳам реферал тизимидан фойдаланиб, дўстларингизни таклиф қилиб промобалл ютишингиз мумкин! 💎",
        //     'en' => "🎉 Congratulations! You have successfully registered!\n\n" .
        //     "We thank the user who invited you to this platform.\n\n" .
        //     "Now you can also use the referral system to invite your friends and earn promoballs! 💎",
        // ],
        'referral_start_text' => [
            'uz' => "🎉 Ajoyib yangilik! Siz taklif qilgan foydalanuvchi ::username ro‘yxatdan o‘tdi 🎊\n" .
                "Sizning hisobingizga ::promoball promo ball qo‘shildi.",

            'ru' => "🎉 Отличная новость! Пользователь ::username, которого вы пригласили, зарегистрировался 🎊\n" .
                "На ваш счёт добавлено ::promoball промобаллов.",

            'kr' => "🎉 Ажойиб янгилик! Сиз таклиф қилган фойдаланувчи ::username рўйхатдан ўтди 🎊\n" .
                "Ҳисобингизга ::promoball промо балл қўшилди.",

            'en' => "🎉 Great news! The user ::username you invited has registered 🎊\n" .
                "::promoball promo points have been added to your account.",
        ],
        'referral_register_text' => [
            'uz' => "🎉 Ajoyib yangilik! Siz taklif qilgan foydalanuvchi ::username ro‘yxatdan o‘tdi 🎊\n" .
                "Sizning hisobingizga ::promoball promo ball qo‘shildi.",

            'ru' => "🎉 Отличная новость! Пользователь ::username, которого вы пригласили, зарегистрировался 🎊\n" .
                "На ваш счёт добавлено ::promoball промобаллов.",

            'kr' => "🎉 Ажойиб янгилик! Сиз таклиф қилган фойдаланувчи ::username рўйхатдан ўтди 🎊\n" .
                "Ҳисобингизга ::promoball промо балл қўшилди.",

            'en' => "🎉 Great news! The user ::username you invited has registered 🎊\n" .
                "::promoball promo points have been added to your account.",
        ],

    ];

    public function get($chatId, $key)
    {
        $lang = Cache::store('redis')->get("tg_lang:$chatId", 'uz');
        return $this->messages[$key][$lang] ?? $this->messages[$key]['uz'];
    }
    public function getForLang(string $key, string $lang): string
    {
        return $this->messages[$key][$lang] ?? $this->messages[$key][$this->defaultLanguage] ?? '';
    }
}

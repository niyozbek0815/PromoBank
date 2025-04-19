# 🏦 PromoBank — Microservices Based Promotional Platform

**PromoBank** — bu **korporativ aksiyalar, gamifikatsiyalashgan o‘yinlar, media, ovoz berish, to‘lov va bildirishnoma tizimlari**ni o‘z ichiga olgan **mikroxizmatlar asosidagi platforma**dir.

Platforma turli kanallar (web, telegram, mobil) orqali yirik brendlar uchun promolarni boshqaradi. Laravel 11, Docker, PHP-FPM, va Nginx asosida qurilgan.

---

## 🚀 Xizmatlar (Microservices)

| Xizmat nomi            | Port  | Tavsif                                                                 |
|------------------------|-------|------------------------------------------------------------------------|
| `api-gateway`          | 8080  | Barcha so‘rovlarni yo‘naltiruvchi yagona kirish nuqtasi (entrypoint). |
| `auth-service`         | 8081  | Foydalanuvchilarni autentifikatsiya va JWT bilan token boshqaruvi.    |
| `promo-service`        | 8082  | Aksiya (promo)larni yaratish, tahrirlash va boshqarish.                |
| `game-service`         | 8083  | Gamifikatsiyalashgan o‘yin logikasi (2 bosqichli kartochkali o‘yin).  |
| `payment-service`      | 8084  | To‘lov tizimi (billing, cashback, ballar).                             |
| `notification-service` | 8085  | Telegram, email va in-app bildirishnomalar yuborish.                  |
| `web-service`          | 8086  | Web sahifa uchun xizmat (frontend backend api).                        |
| `media-service`        | 8087  | Media fayllarni saqlash (file + base64 support).                       |
| `vote-service`         | 8088  | Foydalanuvchi ovoz berish (rating, likes) xizmatlari.                  |

> 🗂 **Global network**: `promobank`  
> 🧰 **PGAdmin**: `5050` portda  
> 🐘 **PostgreSQL**: default portda (`5432`) ishlaydi.

---

## 🧪 Texnologiyalar

- PHP 8.4
- Laravel 11
- Docker + Docker Compose
- PHP-FPM
- Nginx (global reverse proxy)
- PostgreSQL + PgAdmin
- JWT Auth
- GitHub Actions (CI/CD)

---

## ⚙️ Loyihaning papka tuzilmasi

```bash
~/code/microservices/
├── api-gateway/
├── auth-service/
├── promo-service/
├── game-service/
├── payment-service/
├── notification-service/
├── web-service/
├── media-service/
├── vote-service/
├── nginx/
│   └── conf.d/
│       └── default.conf
├── docker-compose.yml
└── Makefile
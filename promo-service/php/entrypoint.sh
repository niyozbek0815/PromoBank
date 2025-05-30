#!/bin/sh
set -e

# PHP-FPM fon rejimida ishga tushirish
php-fpm &

# RabbitMQ consumer ishga tushirish (blocking jarayon)
php artisan rabbitmq:consume-sms-promo
FROM php:8.4.4-fpm

# 1. Composer o‘rnatish
RUN echo "\e[1;33mInstall COMPOSER\e[0m" \
    && cd /tmp \
    && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# 2. PHP extensionlar va system kutubxonalarini o‘rnatish
RUN apt-get update \
    && apt-get -y install --no-install-recommends \
        apt-utils \
        nano \
        wget \
        dialog \
        vim \
        git \
        curl \
        libcurl4 \
        libcurl4-openssl-dev \
        zlib1g-dev \
        libzip-dev \
        zip \
        libbz2-dev \
        locales \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
        libpq-dev \
        libjpeg-dev \
        libpng-dev \
        libwebp-dev \
        libexif-dev \
        build-essential \
        librabbitmq-dev \
        pkg-config \
        libssl-dev \
        # PCNTL uchun kerakli paketlar
        libevent-dev \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        exif \
        zip \
        intl \
        mbstring \
        bcmath \
        sockets \
        pcntl \
    && docker-php-ext-enable exif

# 3. Redis extension o‘rnatish (phpredis)
RUN pecl install redis \
    && docker-php-ext-enable redis

# 4. AMQP (RabbitMQ) extension o‘rnatish
RUN pecl install amqp \
    && docker-php-ext-enable amqp

# 5. PHP ini sozlash
RUN echo "memory_limit=2048M\nupload_max_filesize=512M\npost_max_size=512M" > /usr/local/etc/php/conf.d/uploads.ini
# 6. Tozalash
RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# 7. Ishlash katalogi
WORKDIR /var/www/html

# 8. PHP-FPM default komandasi (Alohida worker container kerak bo‘lsa override qilinadi)
CMD ["php-fpm"]
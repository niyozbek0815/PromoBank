FROM php:8.4.4-fpm

# Install composer
RUN echo "\e[1;33mInstall COMPOSER\e[0m" \
    && cd /tmp \
    && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Install PostgreSQL PDO and required libraries
RUN apt-get update \
    && apt-get -y install \
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
    libmcrypt-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    build-essential \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql

# Optionally install other PHP extensions
# RUN docker-php-ext-install exif pcntl bcmath ctype iconv xml soap mbstring tokenizer bz2 intl

# Cleanup
RUN rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

CMD ["php-fpm"]
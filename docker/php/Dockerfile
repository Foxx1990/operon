FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpq-dev \
    && docker-php-ext-install zip pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

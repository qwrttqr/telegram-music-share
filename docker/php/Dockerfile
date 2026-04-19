FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev

RUN docker-php-ext-install \
    pdo_mysql \
    zip \
    intl

RUN pecl install xdebug && docker-php-ext-enable xdebug

# Install Composer (copy from official image)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


WORKDIR /var/www/html

COPY ../../backend/composer.json ../../backend/composer.lock ./
RUN composer install
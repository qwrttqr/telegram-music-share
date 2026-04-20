FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libicu-dev

RUN docker-php-ext-install pdo_mysql zip intl

RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]
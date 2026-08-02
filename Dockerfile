FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

COPY vite.config.js ./
COPY resources ./resources
RUN npm run build


FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction \
        --ignore-platform-req=php

COPY . .
RUN composer dump-autoload --optimize --no-dev --no-scripts


FROM php:8.4-apache AS runtime

ENV APP_ENV=production \
    APP_DEBUG=false \
    PORT=10000 \
    COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update && apt-get install -y --no-install-recommends \
        libpq-dev \
        libzip-dev \
        libicu-dev \
        unzip \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        pdo_mysql \
        opcache \
        zip \
        intl \
    && apt-get purge -y --auto-remove \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite headers

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/99-datacore.ini

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build

RUN rm -f public/storage \
    && mkdir -p storage/framework/cache/data \
                storage/framework/sessions \
                storage/framework/views \
                storage/logs \
                storage/app/public \
                storage/app/private \
                bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 10000

ENTRYPOINT ["entrypoint"]
CMD ["apache2-foreground"]

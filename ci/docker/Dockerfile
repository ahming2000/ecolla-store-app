# syntax=docker/dockerfile:1.7

FROM node:24-bookworm-slim AS node-runtime

FROM php:8.4-fpm-bookworm AS php-base

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update \
    && apt-get install --yes --no-install-recommends \
        ca-certificates \
        curl \
        git \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libpq-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_pgsql \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY ci/docker/php.ini /usr/local/etc/php/conf.d/99-ecolla-store.ini

WORKDIR /var/www/html

FROM php-base AS development

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY --from=node-runtime /usr/local/bin/node /usr/local/bin/node
COPY --from=node-runtime /usr/local/lib/node_modules /usr/local/lib/node_modules

RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx \
    && mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

FROM development AS build

ARG PRIMEUI_LICENSE_FINGERPRINT=unlicensed

COPY . .

RUN --mount=type=secret,id=primeui_license,required=false \
    if [ "$PRIMEUI_LICENSE_FINGERPRINT" != "unlicensed" ] \
        && [ -f /run/secrets/primeui_license ]; then \
        export VITE_PRIMEUI_LICENSE_KEY="$(cat /run/secrets/primeui_license)"; \
    fi \
    && composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --optimize-autoloader \
        --prefer-dist \
    && npm ci --no-audit --no-fund \
    && npm run build \
    && rm -rf node_modules \
    && rm -f public/hot

FROM php-base AS production

ENV APP_ENV=production
ENV APP_DEBUG=false

COPY ci/docker/php-production.ini /usr/local/etc/php/conf.d/100-ecolla-store-production.ini
COPY --from=build --chown=www-data:www-data /var/www/html /var/www/html

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && install -d -o www-data -g www-data \
        bootstrap/cache \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs

USER www-data

FROM nginx:stable-alpine AS nginx-production

COPY ci/docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=build /var/www/html/public /var/www/html/public

RUN rm -f /var/www/html/public/hot /var/www/html/public/storage \
    && mkdir -p /var/www/html/public/storage

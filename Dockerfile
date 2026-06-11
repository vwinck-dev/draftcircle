# syntax=docker/dockerfile:1.7

ARG PHP_VERSION=8.3
ARG NODE_VERSION=22
ARG NGINX_VERSION=1.28

FROM php:${PHP_VERSION}-fpm-alpine AS php-base

ARG UID=1000
ARG GID=1000

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer \
    COMPOSER_IPRESOLVE=4 \
    COMPOSER_MAX_PARALLEL_HTTP=2 \
    COMPOSER_PROCESS_TIMEOUT=1200 \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=1

RUN apk add --no-cache \
        curl \
        fcgi \
        freetype \
        git \
        icu-data-full \
        icu-libs \
        libjpeg-turbo \
        libpng \
        libzip \
        shadow \
        sqlite-libs \
        unzip \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        freetype-dev \
        icu-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libzip-dev \
        sqlite-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        pcntl \
        pdo_mysql \
        pdo_sqlite \
        zip \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-draftcircle.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/zz-draftcircle.conf
COPY docker/php/entrypoint.sh /usr/local/bin/draftcircle-entrypoint

RUN groupmod --gid "${GID}" www-data \
    && usermod --uid "${UID}" --gid "${GID}" www-data \
    && chmod +x /usr/local/bin/draftcircle-entrypoint

WORKDIR /var/www/html

ENTRYPOINT ["draftcircle-entrypoint"]

FROM php-base AS composer-development

ARG COMPOSER_INSTALL_PREFERENCE=source

COPY --chown=www-data:www-data . .
RUN --mount=type=cache,id=draftcircle-composer,target=/tmp/composer/cache \
    composer install \
        --no-interaction \
        --no-progress \
        --prefer-install="${COMPOSER_INSTALL_PREFERENCE}" \
    && find vendor -type d -name .git -prune -exec rm -rf '{}' +

FROM php-base AS development

ENV APP_ENV=local \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=1

COPY --chown=www-data:www-data . .
COPY --from=composer-development --chown=www-data:www-data /var/www/html/vendor ./vendor

RUN mkdir -p \
        bootstrap/cache \
        storage/app/private \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
    && chown -R www-data:www-data bootstrap/cache storage

USER www-data

EXPOSE 9000

CMD ["php-fpm", "-F"]

FROM php-base AS composer-production

ARG COMPOSER_INSTALL_PREFERENCE=source

COPY --chown=www-data:www-data . .
RUN --mount=type=cache,id=draftcircle-composer,target=/tmp/composer/cache \
    composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --no-scripts \
        --prefer-install="${COMPOSER_INSTALL_PREFERENCE}" \
        --classmap-authoritative \
    && find vendor -type d -name .git -prune -exec rm -rf '{}' +

FROM node:${NODE_VERSION}-bookworm-slim AS frontend-development

ENV HUSKY=0

WORKDIR /var/www/html

COPY package.json package-lock.json ./
RUN npm ci

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]

FROM frontend-development AS frontend-production

ARG VITE_APP_NAME="DraftCircle"
ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT=443
ARG VITE_REVERB_SCHEME=https

ENV VITE_APP_NAME=${VITE_APP_NAME} \
    VITE_REVERB_APP_KEY=${VITE_REVERB_APP_KEY} \
    VITE_REVERB_HOST=${VITE_REVERB_HOST} \
    VITE_REVERB_PORT=${VITE_REVERB_PORT} \
    VITE_REVERB_SCHEME=${VITE_REVERB_SCHEME}

COPY --chown=node:node . .
COPY --from=composer-production --chown=node:node /var/www/html/vendor ./vendor
RUN npm run build

FROM php-base AS production

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0

COPY --chown=www-data:www-data . .
COPY --from=composer-production --chown=www-data:www-data /var/www/html/vendor ./vendor
COPY --from=frontend-production --chown=www-data:www-data /var/www/html/public/build ./public/build

RUN mkdir -p \
        bootstrap/cache \
        storage/app/private \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
    && php artisan package:discover --ansi \
    && php artisan storage:link \
    && chown -R www-data:www-data bootstrap/cache storage

USER www-data

EXPOSE 9000

CMD ["php-fpm", "-F"]

FROM nginx:${NGINX_VERSION}-alpine AS nginx-production

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=production /var/www/html/public /var/www/html/public

EXPOSE 8080

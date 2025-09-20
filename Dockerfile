
FROM php:8.2.12-fpm-alpine

RUN apk add --no-cache \
    bash curl git tzdata \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    libpng-dev libjpeg-turbo-dev libwebp-dev \
    freetype-dev \
    mysql-client

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install -j$(nproc) \
    bcmath exif gd intl mbstring pcntl pdo_mysql zip opcache


RUN pecl install redis \
 && docker-php-ext-enable redis || true


RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"


ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=1


COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html


RUN addgroup -g 1000 -S laravel && adduser -u 1000 -S laravel -G laravel \
 && chown -R laravel:laravel /var/www/html
USER laravel


EXPOSE 9000


HEALTHCHECK --interval=30s --timeout=3s --retries=3 \
  CMD php -v || exit 1

CMD ["php-fpm"]

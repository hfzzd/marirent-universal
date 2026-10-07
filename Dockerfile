FROM php:8.2-fpm-bookworm

RUN groupadd -g 1000 www && useradd -u 1000 -g www -m www

RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip opcache

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY --chown=www:www . /var/www

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

RUN chown -R www:www /var/www/storage /var/www/bootstrap/cache

USER www

EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD php-fpm-healthcheck || exit 1

CMD ["php-fpm"]

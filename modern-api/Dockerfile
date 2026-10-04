FROM php:8.5-cli

RUN apt-get update && apt-get install -y --no-install-recommends libpq-dev unzip git \
    && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /workspace

COPY . .

RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

CMD ["sh", "-lc", "php artisan storage:link --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]

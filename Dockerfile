FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git unzip curl \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev zip \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql pdo_pgsql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chmod -R 777 storage bootstrap/cache

# 🔥 FINAL FIX (WITH SESSION TABLE)
CMD php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear && php artisan session:table && php artisan migrate --force || true && php artisan serve --host=0.0.0.0 --port=$PORT
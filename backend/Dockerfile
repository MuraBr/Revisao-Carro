FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    unzip \
    git

RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    zip \
    bcmath \
    opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

# Instala as dependências do composer durante o build
RUN composer install --no-interaction --optimize-autoloader

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]

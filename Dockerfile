FROM php:8.2-apache
RUN apt-get update && apt-get install -y \
    unzip \
    zip \
    libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql \
    && a2enmod rewrite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . .
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction
EXPOSE 80
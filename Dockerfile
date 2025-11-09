FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN sed -i 's|/var/www/html|/var/www/public|g' /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www

RUN a2enmod rewrite
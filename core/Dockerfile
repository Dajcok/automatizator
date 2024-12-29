FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libssl-dev \
    libbrotli-dev \
    libpng-dev \
    libpq-dev \
    && docker-php-ext-install zip sockets

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

RUN pecl install swoole && docker-php-ext-enable swoole
RUN docker-php-ext-install gd && docker-php-ext-enable gd
RUN docker-php-ext-install pcntl && docker-php-ext-enable pcntl
RUN docker-php-ext-install pdo_pgsql && docker-php-ext-enable pdo_pgsql

WORKDIR /var/www/html

COPY . .

RUN composer update
RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

ENV OCTANE_SERVER=swoole
ENV OCTANE_PORT=8001

EXPOSE 8001

CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8001"]


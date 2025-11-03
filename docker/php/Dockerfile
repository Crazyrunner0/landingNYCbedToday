FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    curl \
    git \
    unzip \
    libzip-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    icu-dev \
    oniguruma-dev \
    mysql-client \
    bash

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        gd \
        mysqli \
        pdo_mysql \
        zip \
        opcache \
        exif \
        intl \
        bcmath

# Install Redis extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Configure PHP-FPM
RUN sed -i 's/listen = 127.0.0.1:9000/listen = 9000/g' /usr/local/etc/php-fpm.d/www.conf

# Create user for running PHP-FPM
RUN addgroup -g 1000 wordpress && adduser -D -u 1000 -G wordpress wordpress

# Change current user to wordpress
USER wordpress

EXPOSE 9000

CMD ["php-fpm"]

FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    zip \
    unzip \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    mysql-client \
    oniguruma-dev \
    procps \
    bash

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Ensure the application directory exists
WORKDIR /var/www

# Thay đổi quyền sở hữu thư mục
RUN chown -R www-data:www-data /var/www

# Expose port 9000
EXPOSE 9000

# Đảm bảo PHP-FPM chạy trong foreground để container không tự tắt
CMD ["php-fpm", "-F"]
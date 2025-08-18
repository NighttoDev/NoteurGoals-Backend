FROM php:8.2-fpm-alpine

# Cài đặt các gói hệ thống và extension PHP
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    zip unzip git curl \
    libpng-dev libzip-dev mysql-client oniguruma-dev \
    procps bash nodejs npm
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl gd

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Sao chép toàn bộ mã nguồn vào trước
COPY src/. ./

# Sao chép file .env.example để Artisan có thể chạy
RUN cp .env.example .env

# Tạo các thư mục mà Laravel cần để ghi VÀ cấp quyền
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Cài đặt các gói phụ thuộc
RUN composer install --no-dev --optimize-autoloader
RUN npm install

# Build frontend assets
RUN npm run build

# === THAY ĐỔI: LOẠI BỎ CÁC LỆNH CACHE GÂY LỖI ===
# RUN php artisan config:cache
# RUN php artisan route:cache
# RUN php artisan view:cache
# ===============================================

# Tạo symlink cho storage. Lệnh này an toàn.
RUN php artisan storage:link

# Cấp lại quyền cho toàn bộ thư mục sau khi build
RUN chown -R www-data:www-data /var/www

EXPOSE 9000
CMD ["php-fpm", "-F"]
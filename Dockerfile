# Use the official PHP image with FPM
FROM php:8.4-fpm-alpine

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apk update && apk add \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm \
    dcron \
    mariadb-client \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip redis

# Clear cache
RUN rm -rf /var/cache/apk/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www

# Copy PHP-FPM configuration
COPY docker/php/www.conf /etc/php84/php-fpm.d/www.conf

# Make backup script executable
RUN chmod +x /var/www/scripts/backup.sh

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev

# Install Node dependencies and build assets
RUN npm install && npm run build

# Create Laravel storage link
RUN php artisan storage:link

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
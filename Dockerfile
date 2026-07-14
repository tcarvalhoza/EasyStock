FROM php:8.4-fpm

# Install system dependencies, Nginx, Supervisor and envsubst support
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    gettext-base \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . /var/www

# Install PHP dependencies (production optimized)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Create required directories and set permissions
RUN mkdir -p storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/framework/testing \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

# Copy Koyeb configuration files
COPY docker/koyeb/nginx.conf /etc/nginx/sites-available/default
COPY docker/koyeb/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/koyeb/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Default Koyeb port (overridden by the PORT environment variable at runtime)
EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

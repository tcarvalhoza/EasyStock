#!/bin/bash
set -e

# Default Koyeb port
export PORT=${PORT:-8080}

# Sensible Laravel defaults when no .env file is bundled in the image
export SESSION_DRIVER=${SESSION_DRIVER:-file}
export CACHE_STORE=${CACHE_STORE:-file}
export QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}

cd /var/www

# Generate an application key if one is not provided.
# For production, configure APP_KEY as an environment variable in Koyeb.
if [ -z "$APP_KEY" ]; then
    echo "APP_KEY not set. Generating a temporary application key..."
    php artisan key:generate --force --ansi
fi

# Ensure required Laravel storage/cache directories exist.
mkdir -p storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/testing \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Run database migrations when a database is configured.
if [ "$DB_CONNECTION" = "sqlite" ] && [ -n "$DB_DATABASE" ]; then
    echo "Configuring SQLite database..."
    mkdir -p "$(dirname "$DB_DATABASE")"
    touch "$DB_DATABASE"
    php artisan migrate --force --ansi || echo "Migration step failed. Continuing startup..."
    php artisan db:seed --force --ansi || echo "Seeding step failed. Continuing startup..."
elif [ -n "$DB_HOST" ]; then
    echo "Running database migrations..."
    php artisan migrate --force --ansi || echo "Migration step failed or database is unreachable. Continuing startup..."
    php artisan db:seed --force --ansi || echo "Seeding step failed. Continuing startup..."
fi

# Cache Laravel configuration in production for better performance.
if [ "$APP_ENV" = "production" ]; then
    echo "Optimizing Laravel for production..."
    php artisan config:cache --ansi
    php artisan route:cache --ansi
    php artisan view:cache --ansi
fi

# Render the Nginx configuration using the dynamic $PORT.
rm -f /etc/nginx/sites-enabled/default
mkdir -p /etc/nginx/sites-enabled
envsubst '${PORT}' < /etc/nginx/sites-available/default > /etc/nginx/sites-enabled/default

echo "Starting services on port ${PORT}..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

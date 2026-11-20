#!/usr/bin/env bash
set -e

PORT="${PORT:-10000}"

# Configure Apache to listen on Render's dynamic $PORT
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# Ensure SQLite file exists if using sqlite
if [ "${DB_CONNECTION}" = "sqlite" ]; then
    DB_FILE="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
    mkdir -p "$(dirname "$DB_FILE")"
    touch "$DB_FILE"
    chown www-data:www-data "$DB_FILE"
fi

# Ensure writable storage and cache directories
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Run migrations automatically (set RUN_SEED=true in Render env for first deploy)
php artisan migrate --force || true
if [ "${RUN_SEED}" = "true" ]; then
    php artisan db:seed --force || true
fi

# Cache configuration, routes, and views for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground

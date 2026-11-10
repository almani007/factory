#!/bin/bash
set -e

echo "========================================"
echo "Brixel FactoryOS - Container Starting"
echo "========================================"

# Wait for database to be ready
echo "Waiting for database connection..."
for i in {1..30}; do
    if php -r "new PDO('pgsql:host=${DB_HOST:-postgres};port=${DB_PORT:-5432};dbname=${DB_DATABASE:-factory}', '${DB_USERNAME:-factory}', '${DB_PASSWORD:-factory}');" 2>/dev/null; then
        echo "Database is ready!"
        break
    fi
    echo "Database not ready, retrying in 2s... ($i/30)"
    sleep 2
done

# Install PHP dependencies if vendor doesn't exist
if [ ! -d "vendor" ]; then
    echo "Installing PHP dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Install Node dependencies if node_modules doesn't exist
if [ ! -d "node_modules" ]; then
    echo "Installing Node dependencies..."
    npm install
fi

# Generate application key if not set
if [ -z "${APP_KEY}" ] || [ "${APP_KEY}" = "base64:" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Clear and cache configuration
echo "Optimizing Laravel..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force --no-interaction 2>/dev/null || echo "Migrations may have already been run"

# Seed database if tables are empty (first run)
echo "Checking if seeding is needed..."
TABLE_COUNT=$(php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);
\$kernel->bootstrap();
\$count = DB::table('tenants')->count();
echo \$count;
" 2>/dev/null || echo "0")

if [ "${TABLE_COUNT}" = "0" ]; then
    echo "Seeding database..."
    php artisan db:seed --force --no-interaction 2>/dev/null || echo "Seeding skipped"
fi

# Create storage link
php artisan storage:link 2>/dev/null || true

# Cache config and routes for production
if [ "${APP_ENV}" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Fix permissions
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "========================================"
echo "Brixel FactoryOS - Ready!"
echo "========================================"

# Execute the original command (php-fpm)
exec "$@"
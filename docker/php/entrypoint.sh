#!/bin/bash
set -e

echo "========================================"
echo "Brixel FactoryOS - Container Starting"
echo "========================================"

# Queue workers and the scheduler share this image, but only the app container
# should install dependencies, migrate, seed and sync assets. They also wait
# for app health in compose, so the vendor volume is ready by the time they run.
if [ "${RUN_BOOTSTRAP:-true}" != "true" ]; then
    echo "Worker container — skipping bootstrap."
    exec "$@"
fi

# Wait for database to be ready (optional - continue even if it fails)
echo "Waiting for database connection..."
for i in {1..15}; do
    if php -r "new PDO('pgsql:host=${DB_HOST:-postgres};port=${DB_PORT:-5432};dbname=${DB_DATABASE:-factory}', '${DB_USERNAME:-factory}', '${DB_PASSWORD:-factory_secret}');" 2>/dev/null; then
        echo "Database is ready!"
        DB_READY=1
        break
    fi
    echo "Database not ready, retrying in 2s... ($i/15)"
    sleep 2
done

# Install PHP dependencies if vendor doesn't exist
if [ ! -d "vendor" ]; then
    echo "Installing PHP dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader || true
fi

# Install Node dependencies if node_modules doesn't exist
if [ ! -d "node_modules" ]; then
    echo "Installing Node dependencies..."
    npm install || true
fi

# Sync compiled assets from the image into the shared build volume.
# The named volume survives rebuilds, so refresh it on every boot.
if [ -f /opt/factory-assets/manifest.json ]; then
    mkdir -p public/build
    cp -a /opt/factory-assets/. public/build/ 2>/dev/null || true
fi

# Generate application key if not set
if [ -z "${APP_KEY}" ] || [ "${APP_KEY}" = "base64:" ]; then
    echo "Generating application key..."
    php artisan key:generate --force 2>/dev/null || echo "Key generation skipped (will use default)"
fi

# Clear and cache configuration
echo "Optimizing Laravel..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true

# Run database migrations (if DB is ready)
if [ "${DB_READY}" = "1" ]; then
    echo "Running database migrations..."
    php artisan migrate --force --no-interaction 2>/dev/null || echo "Migrations may have already been run"
    
    # Seed database if tables are empty
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
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache 2>/dev/null || true
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "========================================"
echo "Brixel FactoryOS - Ready!"
echo "========================================"

# Execute the original command (php-fpm)
exec "$@"
#!/bin/bash
# ============================================================================
# Brixel FactoryOS - Complete Setup Script
# ============================================================================
# Sets up everything using Docker (no PHP needed on host):
#   1. Creates artisan file if missing
#   2. Builds Docker image (composer install happens inside Docker)
#   3. Starts Docker containers
# ============================================================================

set -e

echo "========================================"
echo "Brixel FactoryOS - Complete Setup"
echo "========================================"

# --- Detect Docker ---
if command -v docker &> /dev/null; then
    echo "✓ Docker found"
else
    echo "✗ Docker not found. Please install Docker"
    exit 1
fi

if command -v docker compose &> /dev/null || docker compose version &> /dev/null 2>&1; then
    echo "✓ Docker Compose found"
else
    echo "✗ Docker Compose not found. Please install Docker Compose"
    exit 1
fi

# --- Step 1: Create .env from .env.example ---
echo ""
echo "--- Step 1: Environment Configuration ---"
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✓ Created .env from .env.example"
else
    echo "✓ .env already exists"
fi

# --- Step 2: Create artisan if missing ---
echo ""
echo "--- Step 2: Laravel Artisan ---"
if [ ! -f artisan ]; then
    cat > artisan << 'ARTISAN'
#!/usr/bin/env php
<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

define('LARAVEL_START', microtime(true));

// Register the Composer autoloader...
if (file_exists(__DIR__.'/vendor/autoload.php')) {
    require __DIR__.'/vendor/autoload.php';
}

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$input = new ArgvInput();
$output = new ConsoleOutput();

$status = $kernel->handle($input, $output);

$kernel->terminate($input, $status);

exit($status);
ARTISAN
    chmod +x artisan
    echo "✓ Created artisan"
else
    echo "✓ artisan already exists"
fi

# --- Step 3: Start Docker ---
echo ""
echo "--- Step 3: Docker Containers ---"
echo "Building and starting Docker containers..."
docker compose up -d --build 2>&1 || {
    echo "✗ Docker Compose failed"
    exit 1
}

echo ""
echo "========================================"
echo "Setup Complete!"
echo "========================================"
echo ""
echo "  Application: ${APP_URL:-http://localhost:${APP_PORT:-8088}}"
echo "  Health:      ${APP_URL:-http://localhost:${APP_PORT:-8088}}/up"
echo "  Database:    localhost:5432 (PostgreSQL)"
echo "  Cache:       localhost:6379 (Redis)"
echo ""
echo "  Useful commands:"
echo "    docker compose logs -f        # View logs"
echo "    docker compose down           # Stop services"
echo "    docker compose restart        # Restart services"
echo ""
echo "========================================"

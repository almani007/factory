#!/bin/bash
# ============================================================================
# Brixel FactoryOS - Setup Script
# ============================================================================
# Run this on a fresh server to get everything running:
#   chmod +x scripts/setup.sh
#   ./scripts/setup.sh
# ============================================================================

set -e

echo "========================================"
echo "Brixel FactoryOS - Server Setup"
echo "========================================"

# Check for Docker
if ! command -v docker &> /dev/null; then
    echo "Docker is not installed. Please install Docker first."
    echo "Visit: https://docs.docker.com/engine/install/"
    exit 1
fi

if ! command -v docker compose &> /dev/null; then
    echo "Docker Compose is not installed. Please install Docker Compose plugin."
    exit 1
fi

# Copy environment file if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

# Create required directories
mkdir -p storage bootstrap/cache docker/postgres/init

# Set permissions
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
chmod -R 775 docker 2>/dev/null || true

# Pull latest images
echo "Pulling Docker images..."
docker compose pull

# Build and start all services
echo "Starting Brixel FactoryOS..."
docker compose up -d --build

echo ""
echo "========================================"
echo "Brixel FactoryOS is running!"
echo "========================================"
echo ""
echo "  Application: http://localhost:8000"
echo "  Database:    localhost:5432 (PostgreSQL)"
echo "  Cache:       localhost:6379 (Redis)"
echo ""
echo "  To view logs:   docker compose logs -f"
echo "  To stop:        docker compose down"
echo "  To restart:     docker compose restart"
echo ""
echo "  The application will auto-run migrations on first start."
echo "========================================"
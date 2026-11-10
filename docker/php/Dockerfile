# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Frontend assets (Vite + React + Tailwind)
# ---------------------------------------------------------------------------
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY vite.config.js tsconfig.json ./
COPY resources ./resources
RUN npm run build

# ---------------------------------------------------------------------------
# Application (PHP 8.5 + Laravel)
# ---------------------------------------------------------------------------
FROM php:8.5-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    libicu-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy entrypoint script
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Copy application files
COPY . /var/www/html

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Copy compiled frontend assets from the build stage
COPY --from=assets /app/public/build /var/www/html/public/build

# Fix php-fpm to listen on all interfaces
RUN sed -i 's/;listen = 127.0.0.1:9000/listen = 9000/' /usr/local/etc/php-fpm.d/www.conf

# Expose port 9000 and start php-fpm server
EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
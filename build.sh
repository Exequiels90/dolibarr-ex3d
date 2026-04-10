#!/bin/bash

# Build script for Render.com deployment
# This script runs during the build phase on Render

set -e  # Exit on any error

echo "Starting Ex3D Production Management build process..."

# Set environment variables for production
export APP_ENV=production
export APP_DEBUG=false
export APP_KEY=${APP_KEY:-$(php artisan key:generate --show --no-interaction)}

# Install PHP dependencies
echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Clear and cache Laravel configurations
echo "Optimizing Laravel configuration..."
php artisan config:clear
php artisan config:cache

# Clear and cache routes
echo "Optimizing routes..."
php artisan route:clear
php artisan route:cache

# Clear and cache views
echo "Optimizing views..."
php artisan view:clear
php artisan view:cache

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force --no-interaction

# Optimize for production
echo "Optimizing for production..."
php artisan optimize

# Create storage directories if they don't exist
echo "Setting up storage directories..."
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Set proper permissions
echo "Setting permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Publish Filament assets
echo "Publishing Filament assets..."
php artisan filament:assets

# Create symbolic link for storage
echo "Creating storage symbolic link..."
php artisan storage:link

# Clear any remaining caches
echo "Final cache cleanup..."
php artisan cache:clear
php artisan config:clear

echo "Build process completed successfully!"
echo "Ex3D Production Management is ready for deployment."

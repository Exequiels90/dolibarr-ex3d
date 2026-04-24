#!/bin/sh

echo "Starting Ex3D Production Management System..."

# Change to application directory
cd /var/www/html

# Run migrations and cache in one command
php artisan migrate --force && php artisan config:cache

echo "Starting services..."

# Start supervisor to manage all services
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

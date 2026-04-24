#!/bin/sh

echo "Starting Ex3D Production Management System..."

# Run migrations and cache with full absolute paths
/var/www/html/artisan migrate --force && /var/www/html/artisan config:cache

echo "Starting services..."

# Start supervisor to manage all services
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

#!/bin/sh

echo "Starting Ex3D Production Management System..."

# Try to run migrations but continue if database is not available
echo "Attempting database migration..."
/var/www/html/artisan migrate --force || echo "Migration skipped - database not available"

# Cache configuration
echo "Caching configuration..."
/var/www/html/artisan config:cache

echo "Starting services..."

# Start supervisor to manage all services
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

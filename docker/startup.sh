#!/bin/sh

echo "Starting Ex3D Production Management System..."

# Wait for database to be ready
echo "Waiting for database connection..."
MAX_RETRIES=30
RETRY_COUNT=0

while [ $RETRY_COUNT -lt $MAX_RETRIES ]; do
    if php artisan db:show --quiet 2>/dev/null; then
        echo "Database connection established!"
        break
    fi
    
    RETRY_COUNT=$((RETRY_COUNT + 1))
    echo "Database not ready (attempt $RETRY_COUNT/$MAX_RETRIES), waiting 5 seconds..."
    sleep 5
done

if [ $RETRY_COUNT -eq $MAX_RETRIES ]; then
    echo "ERROR: Could not connect to database after $MAX_RETRIES attempts"
    exit 1
fi

echo "Running database migrations..."

# Run migrations with force flag for production
if php artisan migrate --force; then
    echo "Migrations completed successfully!"
else
    echo "ERROR: Migration failed!"
    exit 1
fi

# Cache configuration for production
echo "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting services..."

# Start supervisor to manage all services
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

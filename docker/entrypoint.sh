#!/bin/sh
set -e

# Cache configuration, routes and views
echo "Caching configurations for Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations (set RUN_MIGRATIONS=true in Render environment variables)
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force
fi

# Link storage folder
echo "Creating storage symlink..."
php artisan storage:link --force || true

# Execute the CMD passed to docker container
exec "$@"

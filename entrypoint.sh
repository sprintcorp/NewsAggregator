#!/bin/bash
set -e  # Exit immediately if a command exits with a non-zero status

# Ensure Composer dependencies are installed
if [ ! -f "vendor/autoload.php" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-progress --no-interaction
fi

# Create .env file if it doesn't exist
if [ ! -f ".env" ]; then
    echo "Creating .env file for environment: $APP_ENV"
    cp .env.example .env
else
    echo ".env file already exists."
fi

# Determine the container role
role=${CONTAINER_ROLE:-app}
echo "Starting container with role: $role"

case "$role" in
    app)
        echo "Running migrations and starting Laravel app..."
        php artisan migrate --force
        php artisan key:generate --force
        php artisan cache:clear
        php artisan config:clear
        php artisan route:clear
        php artisan serve --host=0.0.0.0 --port=$PORT --env=.env
        ;;
    queue)
        echo "Running queue worker..."
        php artisan queue:work --verbose --tries=3 --timeout=180
        ;;
    websocket)
        echo "Running WebSocket server..."
        php artisan websockets:serve
        ;;
    *)
        echo "Invalid container role: $role"
        exit 1
        ;;
esac

# Ensure the default PHP entrypoint is executed
exec docker-php-entrypoint "$@"

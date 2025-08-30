#!/bin/bash
set -e

echo "Deployment started ..."

# Pull the latest version of the app
git pull

# Enter maintenance mode or return true
# if already is in maintenance mode
cd api
(php artisan down) || true


# Install composer dependencies
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Clear the old cache
php artisan clear-compiled

# Recreate cache
php artisan optimize


# Run database migrations
php artisan migrate 

cd ../frontend
# Compile npm assets
npm run prod


cd ../api
# Exit maintenance mode
php artisan up

echo "Deployment finished!"
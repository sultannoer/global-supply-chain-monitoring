#!/usr/bin/env sh

# Run by the Railway App service as its Pre-Deploy Command:
# sh railway/init-app.sh
set -eu

php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache

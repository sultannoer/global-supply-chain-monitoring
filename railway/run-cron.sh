#!/usr/bin/env sh

# Run by the Railway Scheduler service as its Start Command:
# sh railway/run-cron.sh
set -eu

while true; do
    php artisan schedule:run --verbose --no-interaction
    sleep 60
done

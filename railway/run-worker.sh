#!/usr/bin/env sh

# Run by the Railway Worker service as its Start Command:
# sh railway/run-worker.sh
set -eu

exec php artisan queue:work --tries=3 --timeout=120 --sleep=3 --no-interaction

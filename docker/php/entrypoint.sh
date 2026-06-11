#!/bin/sh

set -eu

mkdir -p \
    bootstrap/cache \
    storage/app/private \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs

if [ ! -L public/storage ] && [ -w public ]; then
    php artisan storage:link || [ -L public/storage ]
fi

if [ "${APP_OPTIMIZE:-false}" = "true" ]; then
    php artisan optimize
fi

exec "$@"

#!/bin/sh
set -e

if [ -f composer.json ]; then
    composer install --no-interaction --optimize-autoloader
fi

exec "$@"
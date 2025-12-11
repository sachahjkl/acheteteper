#!/bin/sh
set -e

mkdir -p "$(dirname "$DB_PATH")"
mkdir -p "$UPLOADS_PATH"
mkdir -p /run/nginx

chown -R www-data:www-data /data "$UPLOADS_PATH"

php-fpm -D
exec nginx -g "daemon off;"


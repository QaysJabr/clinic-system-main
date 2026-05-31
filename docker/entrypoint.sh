#!/bin/sh
set -e

mkdir -p \
  /var/www/html/storage/logs \
  /var/www/html/storage/framework/cache/data \
  /var/www/html/storage/framework/sessions \
  /var/www/html/storage/framework/views \
  /var/www/html/storage/app/public \
  /var/www/html/storage/fonts \
  /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R ug+rwx /var/www/html/storage /var/www/html/bootstrap/cache

if [ "$1" = "php-fpm" ]; then
  exec docker-php-entrypoint php-fpm
fi

exec runuser -u www-data -- "$@"

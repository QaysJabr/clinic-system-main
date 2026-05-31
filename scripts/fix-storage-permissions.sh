#!/usr/bin/env bash
# Quick fix: storage permission denied on VPS Docker deploy
set -euo pipefail
cd /var/www/clinic-system-main

mkdir -p storage/logs \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/app/public \
  bootstrap/cache

chown -R 33:33 storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

COMPOSE="docker compose -f docker-compose.yml -f docker-compose.prod.yml"
${COMPOSE} exec -T -u root app chown -R www-data:www-data storage bootstrap/cache
${COMPOSE} exec -T -u root app chmod -R ug+rwx storage bootstrap/cache

echo "Done. Now run: bash scripts/server-deploy-all.sh"
echo "Or continue artisan:"
echo "  ${COMPOSE} exec -T app php artisan migrate --force"

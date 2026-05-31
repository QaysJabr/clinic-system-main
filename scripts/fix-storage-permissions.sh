#!/usr/bin/env bash
# Fix storage permission denied on VPS Docker deploy
set -euo pipefail
cd /var/www/clinic-system-main

DEFAULT_APK_URL="https://expo.dev/artifacts/eas/hQ1eYZyCn7kbN12unXkDcN.apk"
if ! grep -q '^MOBILE_ANDROID_APK_URL=.\+' .env 2>/dev/null; then
  if grep -q '^MOBILE_ANDROID_APK_URL=' .env 2>/dev/null; then
    sed -i "s|^MOBILE_ANDROID_APK_URL=.*|MOBILE_ANDROID_APK_URL=${DEFAULT_APK_URL}|" .env
  else
    echo "MOBILE_ANDROID_APK_URL=${DEFAULT_APK_URL}" >> .env
  fi
  echo "==> Set MOBILE_ANDROID_APK_URL in .env"
fi

echo "==> Create storage dirs on host"
mkdir -p storage/logs \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/app/public \
  storage/fonts \
  bootstrap/cache

chown -R 33:33 storage bootstrap/cache 2>/dev/null || chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

COMPOSE="docker compose -f docker-compose.yml -f docker-compose.prod.yml"

echo "==> Rebuild & restart (entrypoint fixes perms on start)"
${COMPOSE} up -d --build

sleep 5

echo "==> Fix perms inside container as root"
${COMPOSE} exec -T -u root app sh -c 'chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && chmod -R ug+rwx /var/www/html/storage /var/www/html/bootstrap/cache'

echo "==> Seed subscription plans"
${COMPOSE} exec -T app php artisan db:seed --class=PlanSeeder --force
${COMPOSE} exec -T app php artisan db:seed --class=ExpenseCategorySeeder --force

echo "==> Clear caches"
${COMPOSE} exec -T app php artisan config:clear
${COMPOSE} exec -T app php artisan view:clear
${COMPOSE} exec -T app php artisan cache:clear

echo "==> Rebuild caches"
${COMPOSE} exec -T app php artisan config:cache
${COMPOSE} exec -T app php artisan route:cache
${COMPOSE} exec -T app php artisan view:cache

echo ""
echo "Done. Test:"
echo "  curl -I http://127.0.0.1/up"
echo "  curl -I http://127.0.0.1/"

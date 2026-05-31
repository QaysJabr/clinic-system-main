#!/usr/bin/env bash
# Sync production content with local: git pull, plans seed, APK URL, caches.
# Usage: bash scripts/sync-server-content.sh
set -euo pipefail
cd /var/www/clinic-system-main

echo "==> git pull"
git pull origin main 2>/dev/null || git pull

DEFAULT_APK_URL="https://expo.dev/artifacts/eas/oDShSAmKzS7hL3whoqCXLZ.apk"
if ! grep -q '^MOBILE_ANDROID_APK_URL=.\+' .env 2>/dev/null; then
  if grep -q '^MOBILE_ANDROID_APK_URL=' .env 2>/dev/null; then
    sed -i "s|^MOBILE_ANDROID_APK_URL=.*|MOBILE_ANDROID_APK_URL=${DEFAULT_APK_URL}|" .env
  else
    echo "MOBILE_ANDROID_APK_URL=${DEFAULT_APK_URL}" >> .env
  fi
  echo "    Set MOBILE_ANDROID_APK_URL in .env"
fi

COMPOSE="docker compose -f docker-compose.yml -f docker-compose.prod.yml"

echo "==> Seed subscription plans (matches local PlanSeeder)"
${COMPOSE} exec -T app php artisan db:seed --class=PlanSeeder --force
${COMPOSE} exec -T app php artisan db:seed --class=ExpenseCategorySeeder --force

echo "==> Clear & rebuild Laravel caches"
${COMPOSE} exec -T app php artisan config:clear
${COMPOSE} exec -T app php artisan view:clear
${COMPOSE} exec -T app php artisan cache:clear
${COMPOSE} exec -T app php artisan route:clear
${COMPOSE} exec -T app php artisan config:cache
${COMPOSE} exec -T app php artisan route:cache
${COMPOSE} exec -T app php artisan view:cache

echo ""
echo "=============================================="
echo " DONE — Server content synced"
echo "=============================================="
echo " Landing:  http://31.97.61.205/"
echo " Pricing:  http://31.97.61.205/pricing"
echo ""
echo " Expected plans: Basic \$39 | Pro \$79 | Premium \$129"
echo " Hard-refresh browser on phone/desktop after sync."

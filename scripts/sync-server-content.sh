#!/usr/bin/env bash
# Sync production content with local: git pull, plans seed, APK URL, caches.
# Usage: bash scripts/sync-server-content.sh
set -euo pipefail
cd /var/www/clinic-system-main

echo "==> git pull"
git pull origin main 2>/dev/null || git pull

DEFAULT_APK_URL="https://expo.dev/artifacts/eas/wU8eaJvpgiZwDcBG9Si28e.apk"
DEFAULT_APK_VERSION="1.0.2"
if grep -q '^MOBILE_ANDROID_APK_URL=' .env 2>/dev/null; then
  sed -i "s|^MOBILE_ANDROID_APK_URL=.*|MOBILE_ANDROID_APK_URL=${DEFAULT_APK_URL}|" .env
else
  echo "MOBILE_ANDROID_APK_URL=${DEFAULT_APK_URL}" >> .env
fi
echo "    Set MOBILE_ANDROID_APK_URL=${DEFAULT_APK_URL}"
if grep -q '^MOBILE_ANDROID_APK_VERSION=' .env 2>/dev/null; then
  sed -i "s|^MOBILE_ANDROID_APK_VERSION=.*|MOBILE_ANDROID_APK_VERSION=${DEFAULT_APK_VERSION}|" .env
else
  echo "MOBILE_ANDROID_APK_VERSION=${DEFAULT_APK_VERSION}" >> .env
fi
echo "    Set MOBILE_ANDROID_APK_VERSION=${DEFAULT_APK_VERSION}"

COMPOSE="docker compose -f docker-compose.yml -f docker-compose.prod.yml"

echo "==> Rebuild Docker (git pull updates host files; app code lives in the image)"
${COMPOSE} up -d --build

sleep 5

echo "==> Seed subscription plans (matches local PlanSeeder)"
${COMPOSE} exec -T app php artisan db:seed --class=PlanSeeder --force

echo "==> Seed expense categories (skip if already present)"
${COMPOSE} exec -T app php artisan db:seed --class=ExpenseCategorySeeder --force \
  || echo "    ExpenseCategorySeeder skipped — categories already exist (OK)"

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

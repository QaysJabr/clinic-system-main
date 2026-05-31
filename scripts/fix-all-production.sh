#!/usr/bin/env bash
# ═══════════════════════════════════════════════════════════════════════════════
#  أمر واحد على السيرفر — يصلّح كل شيء (Docker, .env, DB, كاش, APK, مستخدمين)
#  Usage: bash scripts/fix-all-production.sh
# ═══════════════════════════════════════════════════════════════════════════════
set -euo pipefail
cd /var/www/clinic-system-main

SERVER_IP="${SERVER_IP:-31.97.61.205}"
APK_URL="${APK_URL:-https://expo.dev/artifacts/eas/hQ1eYZyCn7kbN12unXkDcN.apk}"
APK_VERSION="${APK_VERSION:-1.0.2}"

echo ""
echo "══════════════════════════════════════════════════════════════"
echo " Clinic System — إصلاح شامل للإنتاج"
echo "══════════════════════════════════════════════════════════════"

echo "==> git pull"
git pull origin main 2>/dev/null || git pull

if [[ ! -f .env ]]; then
  cp deploy/env.production.example .env
fi

set_env() {
  local key="$1"
  local val="$2"
  if grep -q "^${key}=" .env 2>/dev/null; then
    sed -i "s|^${key}=.*|${key}=${val}|" .env
  else
    echo "${key}=${val}" >> .env
  fi
}

echo "==> Fix .env (URL, port, mobile APK, Redis/DB)"
set_env APP_ENV production
set_env APP_DEBUG false
set_env APP_URL "http://${SERVER_IP}"
set_env APP_PORT 80
set_env DB_CONNECTION pgsql
set_env DB_HOST postgres
set_env DB_PORT 5432
set_env DB_DATABASE clinic
set_env DB_USERNAME clinic
set_env SESSION_DRIVER redis
set_env CACHE_STORE redis
set_env QUEUE_CONNECTION redis
set_env REDIS_HOST redis
set_env PUSH_ENABLED true
set_env MOBILE_ANDROID_APK_URL "${APK_URL}"
set_env MOBILE_ANDROID_APK_VERSION "${APK_VERSION}"

COMPOSE="docker compose -f docker-compose.yml -f docker-compose.prod.yml"

echo "==> Docker rebuild & start"
${COMPOSE} up -d --build
echo "    Waiting for services..."
sleep 10

echo "==> Storage permissions"
mkdir -p storage/logs storage/framework/{cache/data,sessions,views} storage/app/public bootstrap/cache
chown -R 33:33 storage bootstrap/cache 2>/dev/null || chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache
${COMPOSE} exec -T -u root app sh -c 'chown -R www-data:www-data storage bootstrap/cache && chmod -R ug+rwx storage bootstrap/cache' 2>/dev/null || true

echo "==> Laravel migrate & seed"
${COMPOSE} exec -T app php artisan key:generate --force 2>/dev/null || true
${COMPOSE} exec -T app php artisan migrate --force
${COMPOSE} exec -T app php artisan db:seed --class=RolePermissionSeeder --force
${COMPOSE} exec -T app php artisan db:seed --class=PlanSeeder --force
${COMPOSE} exec -T app php artisan db:seed --class=ExpenseCategorySeeder --force || true

echo "==> Clear & rebuild caches"
${COMPOSE} exec -T app php artisan optimize:clear
${COMPOSE} exec -T app php artisan config:cache
${COMPOSE} exec -T app php artisan route:cache
${COMPOSE} exec -T app php artisan view:cache

if [[ -f storage/app/firebase-credentials.json ]]; then
  echo "==> Firebase push verify"
  ${COMPOSE} exec -T app php artisan push:verify || true
fi

echo ""
echo "==> Health checks"
PORT="$(grep '^APP_PORT=' .env | cut -d= -f2- || echo 80)"
check() {
  local path="$1"
  local code
  code="$(curl -s -o /dev/null -w '%{http_code}' "http://127.0.0.1:${PORT}${path}" || echo 000)"
  if [[ "${code}" == "200" ]] || [[ "${code}" == "302" ]]; then
    echo "    OK  ${path} → HTTP ${code}"
  else
    echo "    FAIL ${path} → HTTP ${code}"
    return 1
  fi
}
check /up
check /api/v1/meta
check /login

echo ""
echo "══════════════════════════════════════════════════════════════"
echo " DONE"
echo "══════════════════════════════════════════════════════════════"
echo " Web:        http://${SERVER_IP}/"
echo " Login:      http://${SERVER_IP}/login"
echo " Mobile API: http://${SERVER_IP}/api/v1"
echo " APK v${APK_VERSION}: ${APK_URL}"
echo ""
echo " Mobile login (after installing APK v${APK_VERSION}):"
echo "   admin@gmail.com          — clinic app"
echo "   qsam0592801756@gmail.com — platform app"
echo "   (password from SEED_ADMIN_PASSWORD / PLATFORM_OWNER_PASSWORD in .env)"
echo ""
echo " On phone: DELETE old app → install APK from landing page → login"
echo "══════════════════════════════════════════════════════════════"

#!/usr/bin/env bash
# Deploy clinic-system-main from GitHub to /var/www
# Run on the VPS as root: bash deploy-vps.sh

set -euo pipefail

WEB_ROOT="/var/www"
APP_DIR="${WEB_ROOT}/clinic-system-main"
REPO="https://github.com/QaysJabr/clinic-system-main.git"
BACKUP_DIR="/root/clinic-backups/$(date +%Y%m%d-%H%M%S)"

echo "==> Backup .env files"
mkdir -p "${BACKUP_DIR}"
for f in "${WEB_ROOT}/.env" "${WEB_ROOT}/clinic-system-main/.env" "${WEB_ROOT}/clinic-system/.env"; do
  if [[ -f "${f}" ]]; then
    cp "${f}" "${BACKUP_DIR}/$(basename "$(dirname "${f}")")-.env"
    echo "    saved ${f}"
  fi
done

echo "==> Remove old project folders"
rm -rf "${WEB_ROOT}/clinic-system-main" "${WEB_ROOT}/clinic-system"

echo "==> Clone fresh from GitHub"
git clone "${REPO}" "${APP_DIR}"

echo "==> Restore .env"
if [[ -f "${BACKUP_DIR}/clinic-system-main-.env" ]]; then
  cp "${BACKUP_DIR}/clinic-system-main-.env" "${APP_DIR}/.env"
elif [[ -f "${BACKUP_DIR}/www-.env" ]]; then
  cp "${BACKUP_DIR}/www-.env" "${APP_DIR}/.env"
elif [[ -f "${BACKUP_DIR}/clinic-system-.env" ]]; then
  cp "${BACKUP_DIR}/clinic-system-.env" "${APP_DIR}/.env"
else
  cp "${APP_DIR}/.env.example" "${APP_DIR}/.env"
  echo "    WARNING: no old .env found — copied .env.example (edit before going live)"
fi

cd "${APP_DIR}"

echo "==> Install PHP dependencies"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Build frontend assets"
if command -v npm >/dev/null 2>&1; then
  npm ci
  npm run build
else
  echo "    npm not found — skip build (use pre-built public/build if committed)"
fi

echo "==> Laravel optimize"
php artisan storage:link --force 2>/dev/null || true
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Permissions"
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || \
  chown -R nginx:nginx storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache

echo ""
echo "Done. App path: ${APP_DIR}"
echo "Nginx/Apache document root should point to: ${APP_DIR}/public"
echo ".env backup: ${BACKUP_DIR}"

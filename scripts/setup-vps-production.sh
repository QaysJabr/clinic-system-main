#!/usr/bin/env bash
# Full production setup for clinic-system on a VPS (Docker).
# Run as root from project root:
#   cd /var/www/clinic-system-main
#   bash scripts/setup-vps-production.sh

set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${APP_DIR}"

SERVER_IP="${SERVER_IP:-31.97.61.205}"
APP_PORT="${APP_PORT:-80}"

echo "==> Clinic System — production setup"
echo "    Path: ${APP_DIR}"
echo "    URL:  http://${SERVER_IP}:${APP_PORT}"

if ! command -v docker >/dev/null 2>&1; then
  echo "ERROR: Docker not installed. Install Docker + Docker Compose plugin first."
  exit 1
fi

if [[ ! -f .env ]]; then
  echo "==> Creating .env from deploy/env.production.example"
  cp deploy/env.production.example .env
fi

# Ensure production values
sed -i "s|^APP_ENV=.*|APP_ENV=production|" .env
sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|" .env
sed -i "s|^APP_URL=.*|APP_URL=http://${SERVER_IP}|" .env
sed -i "s|^APP_PORT=.*|APP_PORT=${APP_PORT}|" .env || echo "APP_PORT=${APP_PORT}" >> .env

if grep -q "CHANGE_ME_DB_PASSWORD" .env; then
  DB_PASS="$(openssl rand -base64 24 | tr -dc 'a-zA-Z0-9' | head -c 24)"
  sed -i "s|CHANGE_ME_DB_PASSWORD|${DB_PASS}|" .env
  echo "    Generated DB_PASSWORD"
fi

if grep -q "CHANGE_ME_STRONG_PASSWORD" .env; then
  OWNER_PASS="$(openssl rand -base64 16 | tr -dc 'a-zA-Z0-9' | head -c 16)"
  sed -i "s|CHANGE_ME_STRONG_PASSWORD|${OWNER_PASS}|" .env
  echo ""
  echo "    Platform owner password: ${OWNER_PASS}"
  echo "    Email: $(grep '^PLATFORM_OWNER_EMAIL=' .env | cut -d= -f2-)"
  echo ""
fi

echo "==> Firebase / Push check"
if [[ -f storage/app/firebase-credentials.json ]]; then
  sed -i 's|^PUSH_ENABLED=.*|PUSH_ENABLED=true|' .env
  if ! grep -q '^FIREBASE_PROJECT_ID=.\+' .env 2>/dev/null; then
    PROJECT_ID=""
    if command -v python3 >/dev/null 2>&1; then
      PROJECT_ID="$(python3 -c "import json; print(json.load(open('storage/app/firebase-credentials.json'))['project_id'])" 2>/dev/null || true)"
    fi
    if [[ -n "${PROJECT_ID}" ]]; then
      sed -i "s|^FIREBASE_PROJECT_ID=.*|FIREBASE_PROJECT_ID=${PROJECT_ID}|" .env
      echo "    Set FIREBASE_PROJECT_ID=${PROJECT_ID}"
    fi
  fi
  echo "    Firebase credentials found — Push enabled"
else
  sed -i 's|^PUSH_ENABLED=.*|PUSH_ENABLED=false|' .env
  echo "    WARNING: storage/app/firebase-credentials.json missing — Push disabled"
  echo "    See docs/FIREBASE-PUSH-AR.md before going live with notifications"
fi

echo "==> Build frontend assets (required for nginx public volume)"
if [[ -f public/build/manifest.json ]]; then
  echo "    Using pre-built public/build from git (skip npm)"
elif command -v npm >/dev/null 2>&1; then
  export PUPPETEER_SKIP_DOWNLOAD=true
  export PUPPETEER_SKIP_CHROME_DOWNLOAD=true
  npm ci --ignore-scripts
  npm run build
else
  echo "ERROR: public/build missing and npm not installed."
  echo "Either commit public/build or install Node.js 20+."
  exit 1
fi

echo "==> Docker build & start"
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build

echo "==> Wait for postgres"
sleep 8

COMPOSE="docker compose -f docker-compose.yml -f docker-compose.prod.yml"

echo "==> Laravel bootstrap (inside app container)"
${COMPOSE} exec -T app php artisan key:generate --force
${COMPOSE} exec -T app php artisan storage:link --force || true
${COMPOSE} exec -T app php artisan migrate --force
${COMPOSE} exec -T app php artisan db:seed --class=RolePermissionSeeder --force
${COMPOSE} exec -T app php artisan config:cache
${COMPOSE} exec -T app php artisan route:cache
${COMPOSE} exec -T app php artisan view:cache

if [[ -f storage/app/firebase-credentials.json ]]; then
  echo "==> Verify Firebase Push"
  ${COMPOSE} exec -T app php artisan push:verify || true
fi

echo ""
echo "=============================================="
echo " DONE — Backend is live"
echo "=============================================="
echo " Web:        http://${SERVER_IP}:${APP_PORT}"
echo " Health:     http://${SERVER_IP}:${APP_PORT}/up"
echo " Mobile API: http://${SERVER_IP}:${APP_PORT}/api/v1"
echo " Login:      http://${SERVER_IP}:${APP_PORT}/login"
echo ""
echo " Next (mobile app):"
echo "  EXPO_PUBLIC_API_URL=http://${SERVER_IP}:${APP_PORT}/api/v1"
echo "  Update mobile/.env + eas.json, then: npm run build:apk:cloud"
echo ""
echo " Optional later:"
echo "  - Point a domain + certbot (HTTPS)"
echo "  - Firebase for push notifications"
echo "=============================================="

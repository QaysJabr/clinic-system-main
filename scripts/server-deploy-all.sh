#!/usr/bin/env bash
# Run on VPS after git pull (MobaXterm / SSH as root)
# Usage: bash scripts/server-deploy-all.sh
set -euo pipefail

APP_DIR="/var/www/clinic-system-main"
cd "${APP_DIR}"

echo "==> git pull"
git pull origin main 2>/dev/null || git pull

if ! command -v docker >/dev/null 2>&1; then
  echo ""
  echo "Docker not installed — installing now..."
  bash scripts/install-docker-vps.sh
fi

CREDS="storage/app/firebase-credentials.json"
if [[ ! -f "${CREDS}" ]]; then
  ADMINSDK="$(find storage/app -maxdepth 1 -name '*firebase-adminsdk*.json' -type f 2>/dev/null | head -1)"
  if [[ -n "${ADMINSDK}" ]]; then
    cp "${ADMINSDK}" "${CREDS}"
    echo "    Renamed $(basename "${ADMINSDK}") → firebase-credentials.json"
  fi
fi

if [[ ! -f "${CREDS}" ]]; then
  echo ""
  echo "ERROR: storage/app/firebase-credentials.json missing on server."
  echo "Upload from your PC (MobaXterm SFTP) — any name is OK if it contains 'firebase-adminsdk':"
  echo "  Local:  clinic-system-main/storage/app/firebase-credentials.json"
  echo "  Remote: /var/www/clinic-system-main/storage/app/"
  echo ""
  echo "Or rename on server:"
  echo "  cp storage/app/clinic-system-*-firebase-adminsdk-*.json storage/app/firebase-credentials.json"
  echo ""
  exit 1
fi

bash scripts/setup-vps-production.sh

echo ""
echo "==> Push verify"
COMPOSE="docker compose -f docker-compose.yml -f docker-compose.prod.yml"
${COMPOSE} exec -T app php artisan push:verify || true

echo ""
echo "Backend live: http://31.97.61.205"
echo "API:          http://31.97.61.205/api/v1"

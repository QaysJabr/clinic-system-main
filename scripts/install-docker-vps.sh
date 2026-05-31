#!/usr/bin/env bash
# Install Docker + Node.js on Ubuntu/Debian VPS (run as root once)
set -euo pipefail

echo "==> Install Docker"
if ! command -v docker >/dev/null 2>&1; then
  apt-get update
  apt-get install -y ca-certificates curl gnupg
  install -m 0755 -d /etc/apt/keyrings
  curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
  chmod a+r /etc/apt/keyrings/docker.gpg
  echo \
    "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
    $(. /etc/os-release && echo "${VERSION_CODENAME:-jammy}") stable" \
    > /etc/apt/sources.list.d/docker.list
  apt-get update
  apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
  systemctl enable docker
  systemctl start docker
  echo "    Docker installed"
else
  echo "    Docker already installed: $(docker --version)"
fi

if ! docker compose version >/dev/null 2>&1; then
  echo "ERROR: docker compose plugin missing"
  exit 1
fi
echo "    $(docker compose version)"

echo "==> Install Node.js 20 (for npm run build)"
if ! command -v npm >/dev/null 2>&1; then
  curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
  apt-get install -y nodejs
  echo "    Node $(node --version), npm $(npm --version)"
else
  echo "    npm already installed: $(npm --version)"
fi

echo ""
echo "Done. Run: bash scripts/server-deploy-all.sh"

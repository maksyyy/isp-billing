#!/bin/bash

# ================================================================
# ISP BILLING & MONITORING AUTOMATIC UPDATER
# Versi: 2.1 — Stable (fix: chmod tidak menyentuh node_modules)
# ================================================================

set -e
clear

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}=================================================================${NC}"
echo -e "${BLUE}        💻 ISP BILLING & MONITORING AUTOMATIC UPDATER 💻         ${NC}"
echo -e "${BLUE}=================================================================${NC}"
echo -e " Script ini akan memperbarui aplikasi ISP Billing Anda ke versi terbaru."
echo -e "${BLUE}=================================================================${NC}"
echo ""

# ── 0. Harus dijalankan sebagai root ──────────────────────────────
if [ "$EUID" -ne 0 ]; then
  echo -e "${RED}❌ Harap jalankan script ini sebagai root (sudo ./update.sh)${NC}"
  exit 1
fi

# ── Tentukan direktori aplikasi ───────────────────────────────────
APP_PATH="/var/www/isp-billing"
if [ ! -d "$APP_PATH" ]; then
  echo -e "${YELLOW}⚠️  Direktori $APP_PATH tidak ditemukan. Menggunakan direktori saat ini.${NC}"
  APP_PATH=$(pwd)
fi
cd "$APP_PATH"

echo -e "${GREEN}🚀 Memulai pembaruan di: $APP_PATH${NC}"
echo "================================================================="

# ── 1. Maintenance Mode (Down) ────────────────────────────────────
echo -e "${YELLOW}🚧 1. Mengaktifkan Mode Perbaikan...${NC}"
if [ -f artisan ]; then
  php artisan down --retry=60 || echo "⚠️  Gagal mengaktifkan mode perbaikan, melanjutkan..."
fi

# ── 2. Git Pull ───────────────────────────────────────────────────
echo -e "${YELLOW}📥 2. Menarik pembaruan dari Git...${NC}"
if [ -d .git ]; then
  git stash || true
  BRANCH=$(git rev-parse --abbrev-ref HEAD)
  echo "Branch aktif: $BRANCH"
  git pull origin "$BRANCH"
  git stash pop || true
else
  echo -e "${RED}⚠️  Bukan repositori Git. Melewati git pull...${NC}"
fi

# ── 3. Composer Install ───────────────────────────────────────────
echo -e "${YELLOW}⚡ 3. Memperbarui dependensi Composer...${NC}"
if [ -f composer.json ]; then
  export COMPOSER_ALLOW_SUPERUSER=1
  composer install --no-dev --optimize-autoloader
else
  echo -e "${RED}❌ composer.json tidak ditemukan!${NC}"; exit 1
fi

# ── 4. Migrasi Database ───────────────────────────────────────────
echo -e "${YELLOW}🗄️  4. Menjalankan migrasi database...${NC}"
if [ -f artisan ]; then
  php artisan migrate --force
else
  echo -e "${RED}❌ artisan tidak ditemukan!${NC}"; exit 1
fi

# ── 5. Build Frontend (React/Vite) ────────────────────────────────
echo -e "${YELLOW}📦 5. Membangun ulang asset frontend (React/Vite)...${NC}"
if [ -f package.json ]; then

  echo "Menginstal npm packages..."
  npm install --legacy-peer-deps

  # Pastikan semua binary node_modules executable (bisa rusak karena chmod di bawah)
  echo "Memastikan execute permission node_modules/.bin..."
  chmod +x node_modules/.bin/* 2>/dev/null || true

  echo "Kompilasi asset Vite..."
  # Jalankan lewat node langsung — 100% immune terhadap masalah execute-bit
  node node_modules/vite/bin/vite.js build

  echo -e "${GREEN}✅ Asset frontend berhasil dikompilasi.${NC}"
else
  echo -e "${YELLOW}⚠️  package.json tidak ditemukan. Melewati kompilasi frontend.${NC}"
fi

# ── 6. Optimasi Cache Laravel ─────────────────────────────────────
echo -e "${YELLOW}⚙️  6. Mengoptimalkan cache Laravel...${NC}"
if [ -f artisan ]; then
  php artisan clear-compiled
  php artisan optimize
  php artisan view:cache
  php artisan event:cache || true
fi

# ── 7. Permissions (KECUALI node_modules) ────────────────────────
echo -e "${YELLOW}🔒 7. Mengatur ulang hak akses file & folder...${NC}"

# Ubah kepemilikan ke www-data, kecuali node_modules (tidak perlu & lambat)
chown -R www-data:www-data "$APP_PATH" --exclude="$APP_PATH/node_modules" 2>/dev/null \
  || chown -R www-data:www-data "$APP_PATH"

# Set permission file: kecualikan node_modules agar binary tidak kehilangan +x
find "$APP_PATH" \
  -not -path "*/node_modules/*" \
  -type f \
  -exec chmod 644 {} \;

# Set permission direktori: kecualikan node_modules
find "$APP_PATH" \
  -not -path "*/node_modules/*" \
  -type d \
  -exec chmod 755 {} \;

# Folder yang butuh write permission
chmod -R 775 "$APP_PATH/storage"
chmod -R 775 "$APP_PATH/bootstrap/cache"

# Pastikan storage & public/build bisa dibaca web server
chmod -R 755 "$APP_PATH/public/build" 2>/dev/null || true

echo -e "${GREEN}✅ Permission berhasil diatur.${NC}"

# ── 8. Restart Layanan ────────────────────────────────────────────
echo -e "${YELLOW}🔄 8. Memulai ulang layanan server...${NC}"

# PHP-FPM (deteksi otomatis versi)
for PHP_FPM in php8.3-fpm php8.2-fpm php8.1-fpm php-fpm; do
  if systemctl list-unit-files 2>/dev/null | grep -q "^${PHP_FPM}"; then
    echo "Merestart ${PHP_FPM}..."
    systemctl restart "$PHP_FPM"
    break
  fi
done

# Nginx (reload config tanpa downtime)
if systemctl list-unit-files 2>/dev/null | grep -q "^nginx"; then
  echo "Mereload nginx..."
  systemctl reload nginx || systemctl restart nginx
fi

# Backbone Monitor Daemon
if systemctl list-unit-files 2>/dev/null | grep -q "^backbone-monitor"; then
  echo "Merestart backbone-monitor..."
  systemctl restart backbone-monitor
fi

# ── 9. Matikan Maintenance Mode ───────────────────────────────────
echo -e "${YELLOW}🚀 9. Menonaktifkan Mode Perbaikan...${NC}"
if [ -f artisan ]; then
  php artisan up
fi

echo ""
echo -e "${BLUE}=================================================================${NC}"
echo -e "${GREEN}🎉 PEMBARUAN SISTEM BERHASIL DISELESAIKAN! 🎉${NC}"
echo -e "${BLUE}=================================================================${NC}"
echo " Aplikasi telah diperbarui ke versi terbaru dan siap digunakan kembali."
echo -e "${BLUE}=================================================================${NC}"
echo ""

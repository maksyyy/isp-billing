#!/bin/bash

# Exit immediately if a command exits with a non-zero status
set -e

# Clear screen for professional installer feel
clear

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}=================================================================${NC}"
echo -e "${BLUE}        💻 ISP BILLING & MONITORING AUTOMATIC UPDATER 💻         ${NC}"
echo -e "${BLUE}=================================================================${NC}"
echo -e " Script ini akan memperbarui aplikasi ISP Billing Anda ke versi terbaru."
echo -e "${BLUE}=================================================================${NC}"
echo ""

# 1. Pastikan dijalankan sebagai root / sudo
if [ "$EUID" -ne 0 ]; then
  echo -e "${RED}❌ Harap jalankan script ini sebagai root (Gunakan: sudo ./update.sh)${NC}"
  exit 1
fi

# 2. Tentukan target direktori aplikasi
APP_PATH="/var/www/isp-billing"

if [ ! -d "$APP_PATH" ]; then
  echo -e "${YELLOW}⚠️ Direktori $APP_PATH tidak ditemukan.${NC}"
  echo -e "Menggunakan direktori saat ini: $(pwd)"
  APP_PATH=$(pwd)
fi

cd "$APP_PATH"

echo -e "${GREEN}🚀 Memulai proses pembaruan di: $APP_PATH...${NC}"
echo "================================================================="

# 3. Mode Maintenance (Down)
echo -e "${YELLOW}🚧 1. Mengaktifkan Mode Perbaikan (Maintenance Mode)...${NC}"
if [ -f artisan ]; then
  php artisan down --retry=60 || echo "⚠️ Gagal mengaktifkan mode perbaikan, melanjutkan proses..."
else
  echo "⚠️ artisan tidak ditemukan, melewati mode perbaikan..."
fi

# 4. Ambil kode terbaru dari Git
echo -e "${YELLOW}📥 2. Menarik pembaruan dari Git...${NC}"
if [ -d .git ]; then
  # Simpan perubahan lokal jika ada untuk menghindari konflik pull
  echo "Mengamankan perubahan lokal sementara (git stash)..."
  git stash || true
  
  # Ambil branch saat ini
  CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
  echo "Menarik kode terbaru dari branch: $CURRENT_BRANCH..."
  git pull origin "$CURRENT_BRANCH"
  
  # Kembalikan perubahan lokal jika sebelumnya di-stash
  git stash pop || true
else
  echo -e "${RED}⚠️ Direktori bukan repositori Git (.git tidak ditemukan). Melewati Git pull...${NC}"
fi

# 5. Instal/Perbarui Dependensi Composer
echo -e "${YELLOW}⚡ 3. Memperbarui dependensi Composer...${NC}"
if [ -f composer.json ]; then
  export COMPOSER_ALLOW_SUPERUSER=1
  composer install --no-dev --optimize-autoloader
else
  echo -e "${RED}❌ composer.json tidak ditemukan! Gagal memperbarui dependensi PHP.${NC}"
  exit 1
fi

# 6. Jalankan Migrasi Database
echo -e "${YELLOW}🗄️ 4. Menjalankan migrasi database...${NC}"
if [ -f artisan ]; then
  php artisan migrate --force
else
  echo -e "${RED}❌ artisan tidak ditemukan! Gagal menjalankan migrasi database.${NC}"
  exit 1
fi

# 7. Instal Dependensi NPM dan Build Frontend Assets
echo -e "${YELLOW}📦 5. Membangun ulang asset frontend (React)...${NC}"
if [ -f package.json ]; then
  echo "Menginstal npm packages..."
  npm install --legacy-peer-deps

  # Pulihkan execute permission pada semua binary node_modules
  # (chmod 644 dari run sebelumnya bisa men-strip execute bit)
  echo "Memulihkan execute permission node_modules/.bin..."
  find node_modules/.bin -maxdepth 1 \( -type f -o -type l \) -exec chmod +x {} \; 2>/dev/null || true

  echo "Kompilasi asset frontend..."
  # Gunakan 'node' langsung ke entrypoint Vite — tidak bergantung execute-bit
  node node_modules/vite/bin/vite.js build
else
  echo -e "${YELLOW}⚠️ package.json tidak ditemukan. Melewati langkah kompilasi frontend.${NC}"
fi

# 8. Bersihkan dan Optimalkan Cache Laravel
echo -e "${YELLOW}⚙️ 6. Mengoptimalkan cache Laravel...${NC}"
if [ -f artisan ]; then
  php artisan clear-compiled
  php artisan optimize
  php artisan view:cache
  php artisan event:cache || true
fi

# 9. Atur Ulang Izin Folder (Permissions)
echo -e "${YELLOW}🔒 7. Mengatur ulang hak akses file & folder...${NC}"
chown -R www-data:www-data "$APP_PATH"
find "$APP_PATH" -type f -exec chmod 644 {} \;
find "$APP_PATH" -type d -exec chmod 755 {} \;
chmod -R 775 "$APP_PATH/storage"
chmod -R 775 "$APP_PATH/bootstrap/cache"
# Pastikan node_modules binary tetap executable setelah chmod
if [ -d "$APP_PATH/node_modules/.bin" ]; then
  chmod -R +x "$APP_PATH/node_modules/.bin"
fi

# 10. Restart Layanan Terkait
echo -e "${YELLOW}🔄 8. Memulai ulang layanan server...${NC}"

# Restart PHP-FPM untuk membersihkan OPcache
if systemctl list-unit-files | grep -q "php8.2-fpm"; then
  echo "Merestart php8.2-fpm..."
  systemctl restart php8.2-fpm
elif systemctl list-unit-files | grep -q "php8.3-fpm"; then
  echo "Merestart php8.3-fpm..."
  systemctl restart php8.3-fpm
else
  echo "⚠️ PHP-FPM tidak ditemukan di systemd, silakan restart web server/PHP-FPM secara manual jika OPcache aktif."
fi

# Restart Daemon Monitor Backbone
if systemctl list-unit-files | grep -q "backbone-monitor"; then
  echo "Merestart layanan backbone-monitor..."
  systemctl restart backbone-monitor
else
  echo "⚠️ Layanan backbone-monitor tidak ditemukan di systemd. Melewati..."
fi

# 11. Matikan Mode Maintenance (Up)
echo -e "${YELLOW}🚀 9. Menonaktifkan Mode Perbaikan (Bringing App Online)...${NC}"
if [ -f artisan ]; then
  php artisan up
fi

echo -e "${BLUE}=================================================================${NC}"
echo -e "${GREEN}🎉 PEMBARUAN SISTEM BERHASIL DISELESAIKAN! 🎉${NC}"
echo -e "${BLUE}=================================================================${NC}"
echo " Aplikasi telah diperbarui ke versi terbaru dan siap digunakan kembali."
echo -e "${BLUE}=================================================================${NC}"
echo ""

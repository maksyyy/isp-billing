#!/bin/bash

# Exit immediately if a command exits with a non-zero status
set -e

# Clear screen for professional installer feel
clear || true

echo "================================================================="
echo "        💻 ISP BILLING & MONITORING AUTOMATIC INSTALLER 💻       "
echo "================================================================="
echo " Installer ini akan mengonfigurasi Ubuntu Server Anda untuk"
echo " menjalankan aplikasi ISP Billing (Laravel + React) secara otomatis."
echo "================================================================="
echo ""

# 1. Pastikan dijalankan sebagai root / sudo
if [ "$EUID" -ne 0 ]; then
  echo "❌ Harap jalankan script ini sebagai root (Gunakan: sudo ./install.sh)"
  exit 1
fi

# 2. Input Interaktif dari Pengguna
read -p "🌐 Masukkan IP Lokal atau Domain untuk akses aplikasi (contoh: 192.168.1.100 atau billing.local): " APP_DOMAIN
if [ -z "$APP_DOMAIN" ]; then
  echo "❌ IP/Domain tidak boleh kosong!"
  exit 1
fi

read -sp "🔑 Masukkan Password baru untuk Database MySQL (isp_user): " DB_PASSWORD
echo ""
if [ -z "$DB_PASSWORD" ]; then
  echo "❌ Password database tidak boleh kosong!"
  exit 1
fi

echo ""
echo "🚀 Memulai instalasi. Silakan tunggu..."
echo "================================================================="

# 3. Update Package Repository & Upgrade
echo "📦 1. Memperbarui sistem..."
apt update && apt upgrade -y

# 4. Instal PHP, Nginx, MySQL, Node.js, Composer
echo "📦 2. Menginstal Nginx, MySQL, PHP 8.2, Node.js, & Composer..."
# Tambahkan PPA PHP untuk memastikan ketersediaan PHP 8.2/8.3
apt install software-properties-common -y
add-apt-repository ppa:ondrej/php -y
apt update

apt install -y nginx mysql-server php8.2-fpm php8.2-cli php8.2-mysql php8.2-xml php8.2-curl php8.2-mbstring php8.2-zip php8.2-gd php8.2-bcmath php8.2-intl php8.2-sqlite3 curl git unzip

# Aktifkan Nginx & MySQL
systemctl enable nginx
systemctl start nginx
systemctl enable mysql
systemctl start mysql

# Instal Composer secara global
echo "📦 Mengunduh Composer..."
if ! curl -sS https://getcomposer.org/installer | php; then
  echo "⚠️ Installer PHP Composer gagal decode zlib, mengunduh binary composer.phar secara langsung..."
  curl -sS https://getcomposer.org/composer-stable.phar -o composer.phar
fi
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Instal Node.js 20 & NPM
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs

# 5. Konfigurasi MySQL Database
echo "🗄️ 3. Membuat Database & Pengguna MySQL..."
mysql -u root -e "CREATE DATABASE IF NOT EXISTS isp_billing;"
mysql -u root -e "CREATE USER IF NOT EXISTS 'isp_user'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
mysql -u root -e "GRANT ALL PRIVILEGES ON isp_billing.* TO 'isp_user'@'localhost';"
mysql -u root -e "FLUSH PRIVILEGES;"

# 6. Salin File Proyek & Setup Direktori
echo "📂 4. Menyalin berkas aplikasi ke /var/www/isp-billing..."
mkdir -p /var/www/isp-billing
# Salin semua isi direktori saat ini (tempat script install.sh berada) ke target
cp -R . /var/www/isp-billing/

# Atur kepemilikan dan hak akses
chown -R www-data:www-data /var/www/isp-billing
find /var/www/isp-billing -type f -exec chmod 644 {} \;
find /var/www/isp-billing -type d -exec chmod 755 {} \;
chmod -R 775 /var/www/isp-billing/storage
chmod -R 775 /var/www/isp-billing/bootstrap/cache

# 7. Konfigurasi Laravel .env
echo "⚙️ 5. Mengonfigurasi berkas .env Laravel..."
cd /var/www/isp-billing
cp .env.example .env

# Gunakan sed untuk mengganti nilai konfigurasi .env
sed -i "s|APP_ENV=local|APP_ENV=production|g" .env
sed -i "s|APP_DEBUG=true|APP_DEBUG=false|g" .env
sed -i "s|APP_URL=http://localhost|APP_URL=http://${APP_DOMAIN}|g" .env
sed -i "s|DB_DATABASE=laravel|DB_DATABASE=isp_billing|g" .env
sed -i "s|DB_USERNAME=root|DB_USERNAME=isp_user|g" .env
sed -i "s|DB_PASSWORD=|DB_PASSWORD=${DB_PASSWORD}|g" .env

# 8. Jalankan Build dependencies Laravel & Frontend
echo "⚡ 6. Menginstal dependensi Composer & Build frontend asset..."
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader

php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link --force

# Hapus node_modules lama untuk menghindari error permission binary/symlink Linux
rm -rf node_modules
npm install --legacy-peer-deps
npm run build

# 9. Konfigurasi Nginx Server Block
echo "🌐 7. Mengonfigurasi Nginx Server Block..."
cat <<EOF > /etc/nginx/sites-available/isp-billing
server {
    listen 80;
    server_name ${APP_DOMAIN};
    root /var/www/isp-billing/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php\$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Aktifkan site dan reload Nginx
ln -sf /etc/nginx/sites-available/isp-billing /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default || true
nginx -t
systemctl restart nginx

# 10. Konfigurasi Cron Job Laravel Scheduler
echo "⏱️ 8. Mengonfigurasi Cron Job Scheduler..."
(crontab -l 2>/dev/null; echo "* * * * * cd /var/www/isp-billing && php artisan schedule:run >> /dev/null 2>&1") | crontab -

# 11. Konfigurasi Systemd Service untuk Backbone Monitor Daemon
echo "⚙️ 9. Mengonfigurasi Systemd Service untuk Backbone Monitor Daemon..."
cat <<EOF > /etc/systemd/system/backbone-monitor.service
[Unit]
Description=ISP Billing Backbone Monitor Daemon
After=network.target mysql.service

[Service]
User=root
WorkingDirectory=/var/www/isp-billing
ExecStart=/usr/bin/php artisan monitor:backbone
Restart=always
RestartSec=5
StandardOutput=syslog
StandardError=syslog
SyslogIdentifier=backbone-monitor

[Install]
WantedBy=multi-user.target
EOF

# Aktifkan dan jalankan service
systemctl daemon-reload
systemctl enable backbone-monitor
systemctl restart backbone-monitor

echo "================================================================="
echo "🎉 INSTALASI OTOMATIS BERHASIL DISELESAIKAN! 🎉"
echo "================================================================="
echo " Aplikasi dapat diakses di browser melalui URL:"
echo " ➡️  http://${APP_DOMAIN}"
echo "================================================================="
echo " Catatan Keamanan:"
echo " - Direktori web: /var/www/isp-billing"
echo " - Database MySQL: isp_billing (User: isp_user)"
echo " - Daemon Monitor: backbone-monitor (systemd service)"
echo "================================================================="

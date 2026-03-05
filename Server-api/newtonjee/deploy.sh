#!/bin/bash
# ============================================================
# NewtonJEE — AWS EC2 Deployment Script
# Ubuntu 24.04 LTS | PHP 8.2 | MySQL 8 | Nginx
# Region: ap-south-1 (Mumbai)
#
# Usage: chmod +x deploy.sh && sudo ./deploy.sh
# ============================================================

set -e

echo "================================================"
echo "  NewtonJEE Portal — Server Setup"
echo "  AWS EC2 ap-south-1 | Ubuntu 24.04"
echo "================================================"

# ── 1. System update ──────────────────────────────────────────
echo "[1/9] Updating system packages..."
apt-get update -qq && apt-get upgrade -y -qq

# ── 2. Install PHP 8.2 + extensions ──────────────────────────
echo "[2/9] Installing PHP 8.2 + extensions..."
add-apt-repository ppa:ondrej/php -y -qq
apt-get update -qq
apt-get install -y \
  php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml \
  php8.2-curl php8.2-zip php8.2-gd php8.2-intl \
  php8.2-bcmath php8.2-opcache php8.2-redis -qq

# ── 3. Install Nginx ──────────────────────────────────────────
echo "[3/9] Installing Nginx..."
apt-get install -y nginx -qq

# ── 4. Install MySQL 8 ───────────────────────────────────────
echo "[4/9] Installing MySQL 8..."
apt-get install -y mysql-server -qq

# ── 5. Install Composer ──────────────────────────────────────
echo "[5/9] Installing Composer..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# ── 6. Create app directories ────────────────────────────────
echo "[6/9] Creating application directories..."

# Project root
mkdir -p /var/www/newtonjee
chown -R www-data:www-data /var/www/newtonjee

# Private storage (OUTSIDE web root — for notebooks, certs, uploads)
mkdir -p /var/www/private/notebooks/starters
mkdir -p /var/www/private/notebooks/submissions
mkdir -p /var/www/private/certificates
chown -R www-data:www-data /var/www/private
chmod -R 750 /var/www/private

echo "  Created /var/www/private (outside web root) ✓"

# ── 7. PHP config hardening ───────────────────────────────────
echo "[7/9] Hardening PHP config..."
PHP_INI=/etc/php/8.2/fpm/php.ini

sed -i 's/expose_php = On/expose_php = Off/'                 $PHP_INI
sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 50M/' $PHP_INI
sed -i 's/post_max_size = 8M/post_max_size = 55M/'           $PHP_INI
sed -i 's/memory_limit = 128M/memory_limit = 256M/'          $PHP_INI
sed -i 's/max_execution_time = 30/max_execution_time = 60/'  $PHP_INI

# OPcache for performance
cat >> /etc/php/8.2/fpm/conf.d/10-opcache.ini << 'EOF'
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=0
opcache.validate_timestamps=0
opcache.enable_cli=0
EOF

# ── 8. Nginx config ───────────────────────────────────────────
echo "[8/9] Configuring Nginx..."
cp /var/www/newtonjee/nginx.conf /etc/nginx/sites-available/newtonjee.com
ln -sf /etc/nginx/sites-available/newtonjee.com /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

nginx -t && systemctl reload nginx

# ── 9. SSL with Certbot ───────────────────────────────────────
echo "[9/9] Setting up SSL with Certbot..."
apt-get install -y certbot python3-certbot-nginx -qq
certbot --nginx -d newtonjee.com -d www.newtonjee.com \
  --non-interactive --agree-tos --email admin@newtonjee.com

# ── Start services ────────────────────────────────────────────
systemctl enable php8.2-fpm nginx mysql
systemctl restart php8.2-fpm nginx

echo ""
echo "================================================"
echo "  Deployment setup complete! ✓"
echo ""
echo "  Next steps:"
echo "  1. Clone repo to /var/www/newtonjee/"
echo "  2. Run: cd /var/www/newtonjee && composer install --no-dev --optimize-autoloader"
echo "  3. Copy .env.example to .env and fill in values"
echo "  4. Import database: mysql -u root -p newtonjee < sql/001_initial_schema.sql"
echo "  5. Set ownership: chown -R www-data:www-data /var/www/newtonjee"
echo ""
echo "  Portal URLs:"
echo "  Student:  https://newtonjee.com"
echo "  Admin:    https://newtonjee.com/admin"
echo "  Mentor:   https://newtonjee.com/mentor"
echo "================================================"

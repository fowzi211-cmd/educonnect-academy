#!/usr/bin/env bash
# Deploy the latest code. Run as root on the server:  bash /var/www/nadacenter/deploy/update.sh
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_DIR"

git pull --ff-only
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R www-data:www-data "$APP_DIR"
systemctl reload php8.4-fpm
echo "Deployed."

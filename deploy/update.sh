#!/usr/bin/env bash
# Deploy the latest code. Run as root on the server:  bash /var/www/nadacenter/deploy/update.sh
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_DIR"

# The app folder is owned by www-data, so root needs Git's safe.directory exception.
git config --global --add safe.directory "$APP_DIR" 2>/dev/null || true

git pull --ff-only
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R www-data:www-data "$APP_DIR"
PHPV="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
systemctl reload "php${PHPV}-fpm"
echo "Deployed."

#!/usr/bin/env bash
# One-time setup for Ubuntu 24.04. Run as root from the cloned repo:
#   DOMAIN=nadacenter.example.com ADMIN_EMAIL=you@example.com bash deploy/setup-server.sh
set -euo pipefail

: "${DOMAIN:?Set DOMAIN, e.g. DOMAIN=nadacenter.example.com}"
: "${ADMIN_EMAIL:?Set ADMIN_EMAIL, email of the first administrator}"

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
export DEBIAN_FRONTEND=noninteractive

echo "==> Installing system packages"
apt-get update -y
apt-get install -y software-properties-common curl git unzip ufw nginx sqlite3 certbot python3-certbot-nginx
add-apt-repository -y ppa:ondrej/php
apt-get update -y
apt-get install -y php8.4-cli php8.4-fpm php8.4-mbstring php8.4-xml php8.4-curl php8.4-zip \
    php8.4-sqlite3 php8.4-intl php8.4-bcmath php8.4-gd

if ! command -v composer >/dev/null; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi
if ! command -v node >/dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
    apt-get install -y nodejs
fi

echo "==> PHP upload limits (lesson videos)"
cat > /etc/php/8.4/fpm/conf.d/99-app.ini <<'EOF'
upload_max_filesize = 600M
post_max_size = 610M
max_execution_time = 300
memory_limit = 512M
EOF
systemctl restart php8.4-fpm

echo "==> Firewall"
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

echo "==> Application"
cd "$APP_DIR"
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build

if [ ! -f .env ]; then
    cp .env.production.example .env
    sed -i "s|^APP_URL=.*|APP_URL=https://${DOMAIN}|" .env
    # No SMTP yet: log mail instead of failing. Switch to smtp once credentials exist.
    sed -i "s|^MAIL_MAILER=.*|MAIL_MAILER=log|" .env
    php artisan key:generate --force
fi
touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --class=RoleSeeder --force
php artisan db:seed --class=SettingSeeder --force
php artisan db:seed --class=StaticContentSeeder --force
php artisan storage:link || true

chown -R www-data:www-data "$APP_DIR"
chmod -R ug+rwX storage bootstrap/cache database

echo "==> First administrator"
ADMIN_PASSWORD="$(head -c 24 /dev/urandom | base64 | tr -dc 'A-Za-z0-9' | head -c 16)Aa1!"
ADMIN_EMAIL="$ADMIN_EMAIL" ADMIN_PASSWORD="$ADMIN_PASSWORD" php artisan tinker --execute='
$email = getenv("ADMIN_EMAIL");
if (\App\Models\User::where("email", $email)->exists()) { echo "Admin already exists, skipped.\n"; return; }
$u = \App\Models\User::create(["name" => "Administrator", "email" => $email, "password" => \Illuminate\Support\Facades\Hash::make(getenv("ADMIN_PASSWORD"))]);
$u->forceFill(["email_verified_at" => now()])->save();
$u->assignRole("super_administrator");
$u->profile()->create(["mobile_number" => "-", "country" => "-", "preferred_language" => "en"]);
echo "Admin created.\n";
'
chown -R www-data:www-data "$APP_DIR"

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> nginx"
cat > /etc/nginx/sites-available/nadacenter <<EOF
server {
    listen 80;
    server_name ${DOMAIN};
    root ${APP_DIR}/public;
    index index.php;
    client_max_body_size 610M;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    location ~ \.php\$ {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF
ln -sf /etc/nginx/sites-available/nadacenter /etc/nginx/sites-enabled/nadacenter
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx

echo "==> Cron (scheduler + nightly backup)"
CRON_FILE=/etc/cron.d/nadacenter
cat > "$CRON_FILE" <<EOF
* * * * * www-data cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1
0 3 * * * www-data cd ${APP_DIR} && php artisan app:backup >> storage/logs/backup.log 2>&1
EOF
chmod 644 "$CRON_FILE"

echo "==> HTTPS certificate"
set +e
certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos -m "$ADMIN_EMAIL" --redirect
CERT_STATUS=$?
set -e

echo
echo "=============================================="
echo " Site:           https://${DOMAIN}"
echo " Admin email:    ${ADMIN_EMAIL}"
echo " Admin password: ${ADMIN_PASSWORD}   (shown once - save it now, then change it)"
if [ "$CERT_STATUS" -ne 0 ]; then
    echo " WARNING: HTTPS certificate failed. Check the DNS A record for ${DOMAIN}"
    echo "          points to this server, then run: certbot --nginx -d ${DOMAIN}"
fi
echo " Mail is set to 'log' until you add SMTP settings to ${APP_DIR}/.env"
echo "=============================================="

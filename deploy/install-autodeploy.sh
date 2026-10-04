#!/usr/bin/env bash
# One-time setup of automatic deploys. Run as root on the server, from the repo:
#   bash deploy/install-autodeploy.sh
# Re-run it after changing deploy/auto-update.sh (the installed copy is separate on purpose).
set -euo pipefail

[ "$(id -u)" -eq 0 ] || { echo "Run this as root."; exit 1; }

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

install -m 755 "$APP_DIR/deploy/auto-update.sh" /usr/local/sbin/nadacenter-autodeploy

cat > /etc/cron.d/nadacenter-autodeploy <<EOF
# Checks GitHub every 2 minutes and deploys new commits (see deploy/auto-update.sh).
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
HOME=/root
COMPOSER_ALLOW_SUPERUSER=1
APP_DIR=${APP_DIR}
*/2 * * * * root /usr/local/sbin/nadacenter-autodeploy
EOF
chmod 644 /etc/cron.d/nadacenter-autodeploy

touch /var/log/nadacenter-deploy.log
cat > /etc/logrotate.d/nadacenter-deploy <<'EOF'
/var/log/nadacenter-deploy.log {
    weekly
    rotate 8
    compress
    missingok
    notifempty
}
EOF

# Pasting into this server from the Windows SSH window inserts hidden characters
# that break commands; turning bracketed paste off for root fixes that.
grep -qs 'enable-bracketed-paste' /root/.inputrc 2>/dev/null \
    || echo 'set enable-bracketed-paste off' >> /root/.inputrc

# Run once now so a problem shows up immediately instead of at the next push.
/usr/local/sbin/nadacenter-autodeploy || true

echo
echo "=============================================="
echo " Automatic deploys are ON (checks every 2 minutes)."
echo " Log:   tail -n 20 /var/log/nadacenter-deploy.log"
echo " Pause: rm /etc/cron.d/nadacenter-autodeploy"
echo " Resume: bash ${APP_DIR}/deploy/install-autodeploy.sh"
echo "=============================================="

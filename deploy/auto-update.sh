#!/usr/bin/env bash
# Polls GitHub and deploys new commits on the branch. Installed to
# /usr/local/sbin/nadacenter-autodeploy by deploy/install-autodeploy.sh and run
# by cron every 2 minutes. Safe to run often: it does nothing unless GitHub has a
# new commit.
set -uo pipefail

APP_DIR="${APP_DIR:-/var/www/nadacenter}"
BRANCH="${BRANCH:-master}"
STATE_DIR="${STATE_DIR:-/var/lib/nadacenter-deploy}"
LOG="${LOG:-/var/log/nadacenter-deploy.log}"

mkdir -p "$STATE_DIR"

# Never run two deploys at once (a deploy can take longer than the 2-minute interval).
exec 9>"$STATE_DIR/lock"
flock -n 9 || exit 0

log() { echo "$(date '+%F %T') $*" >> "$LOG"; }

cd "$APP_DIR" || exit 1

# The checkout is owned by www-data; root needs Git's safe.directory exception.
git config --global --get-all safe.directory | grep -qxF "$APP_DIR" \
    || git config --global --add safe.directory "$APP_DIR"

if ! git fetch --quiet origin "$BRANCH" 2>>"$LOG"; then
    log "fetch from GitHub failed; will retry"
    exit 1
fi

LOCAL="$(git rev-parse HEAD)"
REMOTE="$(git rev-parse "origin/$BRANCH")"

[ "$LOCAL" = "$REMOTE" ] && exit 0

# A commit that already failed (and was rolled back) is not retried until a newer one arrives.
if [ -f "$STATE_DIR/failed-sha" ] && [ "$(cat "$STATE_DIR/failed-sha")" = "$REMOTE" ]; then
    exit 0
fi

health_ok() {
    local url
    url="$(grep -E '^APP_URL=' .env 2>/dev/null | head -n1 | cut -d= -f2- | tr -d '"')"
    [ -n "$url" ] || return 0
    for _ in 1 2 3 4 5; do
        curl -fsS -o /dev/null -m 15 "${url%/}/up" && return 0
        sleep 3
    done
    return 1
}

log "new commit on $BRANCH: ${LOCAL:0:7} -> ${REMOTE:0:7}; deploying"

# Move to the new commit here, so update.sh's own "git pull" is a no-op and the
# script is never rewritten while it is running.
if ! git merge --ff-only "origin/$BRANCH" >>"$LOG" 2>&1; then
    log "cannot fast-forward (local changes on the server, or diverged history); skipping ${REMOTE:0:7}"
    echo "$REMOTE" > "$STATE_DIR/failed-sha"
    exit 1
fi

if bash deploy/update.sh >>"$LOG" 2>&1 && health_ok; then
    rm -f "$STATE_DIR/failed-sha"
    log "deployed ${REMOTE:0:7} OK"
    exit 0
fi

log "DEPLOY FAILED for ${REMOTE:0:7}; rolling back to ${LOCAL:0:7}"
echo "$REMOTE" > "$STATE_DIR/failed-sha"
git reset --hard "$LOCAL" >>"$LOG" 2>&1
if bash deploy/update.sh >>"$LOG" 2>&1 && health_ok; then
    log "rolled back to ${LOCAL:0:7}; site is healthy. Database migrations, if any, were not reverted."
else
    log "ROLLBACK ALSO FAILED - the site needs manual attention"
fi
exit 1

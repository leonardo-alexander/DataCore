#!/usr/bin/env bash
set -euo pipefail

log() { echo "==> $*"; }

: "${PORT:=10000}"
export PORT

sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf

normalise_app_key() {
    if [ -z "${APP_KEY:-}" ]; then
        log "APP_KEY is empty - generating an ephemeral one. Set it in Render, or restarts drop all sessions."
        APP_KEY="base64:$(head -c 32 /dev/urandom | base64)"
        return
    fi

    case "$APP_KEY" in
        base64:*) return ;;
    esac

    if [ "${#APP_KEY}" -eq 32 ]; then
        return
    fi

    if printf '%s' "$APP_KEY" | base64 -d 2>/dev/null | wc -c | grep -qx 32; then
        log "APP_KEY looked like raw base64 - adding the base64: prefix Laravel expects."
        APP_KEY="base64:${APP_KEY}"
        return
    fi

    log "APP_KEY is not a valid 32-byte key - deriving a stable one from it."
    APP_KEY="base64:$(php -r 'echo base64_encode(hash("sha256", $argv[1], true));' "$APP_KEY")"
}

normalise_app_key
export APP_KEY

if [ -z "${APP_URL:-}" ] && [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
    APP_URL="$RENDER_EXTERNAL_URL"
    export APP_URL
    log "APP_URL taken from RENDER_EXTERNAL_URL: ${APP_URL}"
fi

php artisan storage:link --force >/dev/null 2>&1 || true

log "Running migrations..."
migrated=false
for attempt in $(seq 1 10); do
    if php artisan migrate --force --no-interaction; then
        migrated=true
        break
    fi
    log "Database not ready (attempt ${attempt}/10) - retrying in 5s..."
    sleep 5
done

if [ "$migrated" != "true" ]; then
    log "ERROR: could not run migrations. Check DB_* settings and that the database is running."
    exit 1
fi

if [ "${RUN_SEEDERS:-true}" = "true" ]; then
    log "Seeding (demo data is inserted only when the database has no users)..."
    if ! php artisan db:seed --force --no-interaction; then
        log "WARNING: seeding failed. Continuing - the app runs fine without demo data."
    fi
else
    log "RUN_SEEDERS is not 'true' - skipping seeders."
fi

if [ "${AUTO_VERIFY_USERS:-false}" = "true" ]; then
    log "AUTO_VERIFY_USERS is on - identity review is skipped and existing accounts are marked verified."
    php artisan users:verify-all --no-interaction || log "WARNING: could not auto-verify existing accounts."
fi

log "Caching configuration, routes and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Anything dispatched to a queue sits in the jobs table until something drains it.
# Render's free plan has no separate Background Worker service, so the worker runs
# beside Apache in this container. With QUEUE_CONNECTION=sync there is no queue to
# drain — jobs run inline in the web request — so no worker is started.
if [ "${QUEUE_CONNECTION:-sync}" != "sync" ]; then
    log "Starting queue worker for the '${QUEUE_CONNECTION}' connection..."
    (
        while true; do
            php artisan queue:work \
                --queue="${QUEUE_NAME:-default}" \
                --tries=1 \
                --timeout="${QUEUE_TIMEOUT:-300}" \
                --sleep=3 \
                --max-time=3600 \
                --no-interaction || log "Queue worker exited - restarting in 5s..."
            sleep 5
        done
    ) &
else
    log "QUEUE_CONNECTION is 'sync' - jobs run inline in the web request, no worker started."
fi

log "DataCore is ready on port ${PORT}."

exec "$@"

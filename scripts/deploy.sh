#!/usr/bin/env bash

set -Eeuo pipefail

SCRIPT_NAME="$(basename "$0")"
readonly SCRIPT_NAME
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd -P)"
MAINTENANCE_MODE=false

log() {
    printf '\n[%s] %s\n' "$SCRIPT_NAME" "$*"
}

fail() {
    printf '\n[%s] ERROR: %s\n' "$SCRIPT_NAME" "$*" >&2
    exit 1
}

restore_application() {
    if [[ "$MAINTENANCE_MODE" == true ]]; then
        php artisan up --no-interaction >/dev/null 2>&1 || true
    fi
}

trap restore_application EXIT

cd "$PROJECT_ROOT"
[[ -f .env ]] || fail "Create and configure .env before deploying."
grep -Eq '^VITE_PRIMEUI_LICENSE_KEY=.+$' .env \
    || fail "PrimeUI is not configured. Run bash scripts/configure-primeui.sh."
command -v composer >/dev/null || fail "Composer is required."
command -v npm >/dev/null || fail "npm is required."

if command -v flock >/dev/null; then
    exec 9>"$PROJECT_ROOT/storage/framework/deploy.lock"
    flock --nonblock 9 || fail "Another deployment is already running."
fi

log "Installing production PHP dependencies"
composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --prefer-dist

log "Installing frontend dependencies and building assets"
npm ci --no-audit --no-fund
npm run build
if [[ -f public/hot ]]; then
    rm -f public/hot
fi

log "Enabling maintenance mode"
php artisan down --retry=60 --no-interaction
MAINTENANCE_MODE=true

log "Running database migrations"
php artisan migrate --force --no-interaction

log "Preparing storage and production caches"
php artisan storage:link --no-interaction
php artisan optimize --no-interaction

log "Reloading Laravel's long-running services"
php artisan reload --no-interaction
php artisan schedule:interrupt --no-interaction

if command -v supervisorctl >/dev/null; then
    SUPERVISOR_COMMAND=(supervisorctl)

    if [[ "$(id -u)" -ne 0 ]]; then
        if command -v sudo >/dev/null && sudo -n true >/dev/null 2>&1; then
            SUPERVISOR_COMMAND=(sudo -n supervisorctl)
        else
            SUPERVISOR_COMMAND=()
            log "Supervisor is installed, but passwordless sudo is unavailable; start the worker manually if needed."
        fi
    fi

    if [[ ${#SUPERVISOR_COMMAND[@]} -gt 0 ]]; then
        "${SUPERVISOR_COMMAND[@]}" reread
        "${SUPERVISOR_COMMAND[@]}" update
        "${SUPERVISOR_COMMAND[@]}" restart "ecolla-store-app-worker:*" \
            || "${SUPERVISOR_COMMAND[@]}" start "ecolla-store-app-worker:*"
    fi
fi

php artisan up --no-interaction
MAINTENANCE_MODE=false

if [[ -n "${DEPLOY_HEALTH_URL:-}" ]]; then
    log "Checking ${DEPLOY_HEALTH_URL}"
    curl --fail --silent --show-error \
        --retry 5 \
        --retry-all-errors \
        --retry-delay 2 \
        "$DEPLOY_HEALTH_URL" >/dev/null
fi

log "Deployment complete"

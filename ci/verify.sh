#!/usr/bin/env bash

set -Eeuo pipefail

SCRIPT_NAME="$(basename "$0")"
readonly SCRIPT_NAME
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd -P)"

log() {
    printf '\n[%s] %s\n' "$SCRIPT_NAME" "$*"
}

fail() {
    printf '\n[%s] ERROR: %s\n' "$SCRIPT_NAME" "$*" >&2
    exit 1
}

cd "$PROJECT_ROOT"
command -v composer >/dev/null || fail "Composer is required."
command -v npm >/dev/null || fail "npm is required."

if [[ ! -f .env ]]; then
    cp .env.example .env
fi

log "Validating and installing locked dependencies"
composer validate --strict
composer install --no-interaction --no-progress --prefer-dist
npm ci --no-audit --no-fund

log "Checking scripts and configuration formatting"
bash -n scripts/*.sh ci/*.sh
npx prettier --check \
    README.md \
    compose.yaml \
    compose.production.yaml \
    composer.json \
    package.json

if ! grep -Eq '^APP_KEY=.+$' .env; then
    php artisan key:generate --no-interaction
fi

if [[ "${CI_POSTGRES_CHECK:-false}" == "true" ]]; then
    log "Verifying migrations and ProdSeeder against PostgreSQL"
    env \
        APP_ENV=testing \
        CACHE_STORE=array \
        DB_CONNECTION=pgsql \
        DB_HOST="${DB_HOST:-127.0.0.1}" \
        DB_PORT="${DB_PORT:-5432}" \
        DB_DATABASE="${DB_DATABASE:-ecolla_ci}" \
        DB_USERNAME="${DB_USERNAME:-postgres}" \
        DB_PASSWORD="${DB_PASSWORD:-postgres}" \
        QUEUE_CONNECTION=sync \
        SESSION_DRIVER=array \
        TELESCOPE_ENABLED=false \
        php artisan migrate:fresh --force --no-interaction

    env \
        APP_ENV=testing \
        CACHE_STORE=array \
        DB_CONNECTION=pgsql \
        DB_HOST="${DB_HOST:-127.0.0.1}" \
        DB_PORT="${DB_PORT:-5432}" \
        DB_DATABASE="${DB_DATABASE:-ecolla_ci}" \
        DB_USERNAME="${DB_USERNAME:-postgres}" \
        DB_PASSWORD="${DB_PASSWORD:-postgres}" \
        QUEUE_CONNECTION=sync \
        SESSION_DRIVER=array \
        TELESCOPE_ENABLED=false \
        php artisan db:seed --class=ProdSeeder --force --no-interaction
fi

log "Running PHP formatting checks, static analysis, and PHPUnit"
composer test

log "Running frontend type checks and production build"
npm run check

log "Auditing locked production PHP dependencies"
composer audit --locked --no-dev --no-interaction

log "Verification complete"

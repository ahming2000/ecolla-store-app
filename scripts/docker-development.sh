#!/usr/bin/env bash

set -Eeuo pipefail

SCRIPT_NAME="$(basename "$0")"
readonly SCRIPT_NAME
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd -P)"
SEEDER="none"
FRESH=false
ASSUME_YES=false

log() {
    printf '\n[%s] %s\n' "$SCRIPT_NAME" "$*"
}

fail() {
    printf '\n[%s] ERROR: %s\n' "$SCRIPT_NAME" "$*" >&2
    exit 1
}

usage() {
    cat <<'EOF'
Start the local Docker development environment.

Usage:
  scripts/docker-development.sh [options]

Options:
  --seed none|dev|prod   Optional database seeder (default: none)
  --fresh                Drop all tables before migrating
  --yes                  Confirm the destructive --fresh operation
  -h, --help             Show this help
EOF
}

while [[ $# -gt 0 ]]; do
    case "$1" in
        --seed)
            [[ -n "${2:-}" ]] || fail "--seed requires a value."
            SEEDER="$2"
            shift 2
            ;;
        --seed=*)
            SEEDER="${1#*=}"
            shift
            ;;
        --fresh)
            FRESH=true
            shift
            ;;
        --yes)
            ASSUME_YES=true
            shift
            ;;
        -h|--help)
            usage
            exit 0
            ;;
        *)
            fail "Unknown option: $1"
            ;;
    esac
done

[[ "$SEEDER" =~ ^(none|dev|prod)$ ]] || fail "--seed must be none, dev, or prod."
command -v docker >/dev/null || fail "Docker is required."
docker compose version >/dev/null 2>&1 || fail "Docker Compose v2 is required."

cd "$PROJECT_ROOT"

if [[ ! -f .env ]]; then
    cp .env.example .env
    chmod 0600 .env
fi

if ! grep -Eq '^VITE_PRIMEUI_LICENSE_KEY=.+$' .env; then
    log "Configuring the PrimeUI license"
    bash scripts/configure-primeui.sh
fi

if [[ "$FRESH" == true && "$ASSUME_YES" != true ]]; then
    if [[ ! -t 0 ]]; then
        fail "--fresh requires --yes when running non-interactively."
    fi

    printf 'This will drop every table in the local Docker database. Type "docker" to continue: '
    read -r confirmation
    [[ "$confirmation" == "docker" ]] || fail "Fresh migration cancelled."
fi

if [[ ! -x vendor/bin/sail ]]; then
    log "Installing Composer dependencies so Laravel Sail is available"

    if command -v composer >/dev/null; then
        composer install --no-interaction --no-progress --prefer-dist
    else
        docker run --rm \
            --user "$(id -u):$(id -g)" \
            --volume "$PROJECT_ROOT:/var/www/html" \
            --workdir /var/www/html \
            laravelsail/php84-composer:latest \
            composer install \
                --ignore-platform-reqs \
                --no-interaction \
                --no-progress \
                --prefer-dist
    fi
fi

SAIL=(./vendor/bin/sail)

log "Building Laravel Sail's PHP 8.4 / Node.js 24 image"
"${SAIL[@]}" build --pull

log "Starting the Sail application and PostgreSQL 18"
"${SAIL[@]}" up --detach laravel.test pgsql

log "Installing locked PHP and Node.js dependencies inside Sail"
"${SAIL[@]}" composer install --no-interaction --no-progress --prefer-dist
"${SAIL[@]}" npm ci --no-audit --no-fund

if ! grep -Eq '^APP_KEY=.+$' .env; then
    log "Generating the Laravel application key"
    "${SAIL[@]}" artisan key:generate --no-interaction
fi

if [[ "$FRESH" == true ]]; then
    log "Running fresh PostgreSQL migrations"
    "${SAIL[@]}" artisan migrate:fresh --force --no-interaction
else
    log "Running PostgreSQL migrations"
    "${SAIL[@]}" artisan migrate --force --no-interaction
fi

case "$SEEDER" in
    dev)
        "${SAIL[@]}" artisan db:seed --class=DevSeeder --force --no-interaction
        ;;
    prod)
        "${SAIL[@]}" artisan db:seed --class=ProdSeeder --force --no-interaction
        ;;
esac

log "Starting Laravel Sail, Vite, and the queue worker"
"${SAIL[@]}" up --detach

printf '\nApplication: http://localhost'
if [[ -n "${APP_PORT:-}" && "${APP_PORT}" != "80" ]]; then
    printf ':%s' "$APP_PORT"
fi
printf '\n'
printf 'Vite:       http://localhost:%s\n' "${VITE_PORT:-5173}"
printf 'Logs:       ./vendor/bin/sail logs --follow\n'

#!/usr/bin/env bash

set -Eeuo pipefail

SCRIPT_NAME="$(basename "$0")"
readonly SCRIPT_NAME
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd -P)"
SEEDER="none"

log() {
    printf '\n[%s] %s\n' "$SCRIPT_NAME" "$*"
}

fail() {
    printf '\n[%s] ERROR: %s\n' "$SCRIPT_NAME" "$*" >&2
    exit 1
}

usage() {
    cat <<'EOF'
Build and deploy the production Docker Compose stack.

Usage:
  scripts/deploy-docker.sh [options]

Options:
  --seed none|prod   Run ProdSeeder on a first deployment (default: none)
  -h, --help         Show this help
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
        -h|--help)
            usage
            exit 0
            ;;
        *)
            fail "Unknown option: $1"
            ;;
    esac
done

[[ "$SEEDER" =~ ^(none|prod)$ ]] || fail "--seed must be none or prod."
command -v docker >/dev/null || fail "Docker is required."
docker compose version >/dev/null 2>&1 || fail "Docker Compose v2 is required."

cd "$PROJECT_ROOT"
[[ -f .env ]] || fail "Create and configure .env before deploying."
grep -Eq '^APP_KEY=.+$' .env || fail "APP_KEY must be set in .env."
grep -Eq '^DB_DATABASE=.+$' .env || fail "DB_DATABASE must be set in .env."
grep -Eq '^DB_USERNAME=.+$' .env || fail "DB_USERNAME must be set in .env."
grep -Eq '^DB_PASSWORD=.+$' .env || fail "DB_PASSWORD must be set in .env."
grep -Eq '^VITE_PRIMEUI_LICENSE_KEY=.+$' .env \
    || fail "PrimeUI is not configured. Run bash scripts/configure-primeui.sh."

VITE_PRIMEUI_LICENSE_KEY="$(sed -n 's/^VITE_PRIMEUI_LICENSE_KEY=//p' .env | tail -n 1)"
PRIMEUI_LICENSE_FINGERPRINT="$(
    printf '%s' "$VITE_PRIMEUI_LICENSE_KEY" | sha256sum | cut -d' ' -f1
)"
export VITE_PRIMEUI_LICENSE_KEY
export PRIMEUI_LICENSE_FINGERPRINT

COMPOSE=(docker compose --file compose.production.yaml)

if command -v flock >/dev/null; then
    exec 9>"$PROJECT_ROOT/storage/framework/docker-deploy.lock"
    flock --nonblock 9 || fail "Another Docker deployment is already running."
fi

log "Building production application and Nginx images"
"${COMPOSE[@]}" build --pull app nginx

log "Starting PostgreSQL"
"${COMPOSE[@]}" up --detach postgres

log "Running database migrations"
"${COMPOSE[@]}" run --rm app php artisan migrate --force --no-interaction

if [[ "$SEEDER" == "prod" ]]; then
    log "Running ProdSeeder"
    "${COMPOSE[@]}" run --rm app php artisan db:seed --class=ProdSeeder --force --no-interaction
fi

log "Refreshing Laravel's production caches"
"${COMPOSE[@]}" run --rm app php artisan optimize:clear --no-interaction
"${COMPOSE[@]}" run --rm app php artisan optimize --no-interaction

log "Recreating production services"
"${COMPOSE[@]}" up --detach --remove-orphans app nginx queue scheduler

HEALTH_URL="${DEPLOY_HEALTH_URL:-http://127.0.0.1:${DOCKER_HTTP_PORT:-8080}/up}"
log "Checking ${HEALTH_URL}"
curl --fail --silent --show-error \
    --retry 10 \
    --retry-all-errors \
    --retry-delay 2 \
    "$HEALTH_URL" >/dev/null

log "Docker deployment complete"

#!/usr/bin/env bash

set -Eeuo pipefail

SCRIPT_NAME="$(basename "$0")"
readonly SCRIPT_NAME
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd -P)"

APP_DATABASE="${APP_DATABASE:-ecolla_store_app}"
APP_DATABASE_USER="${APP_DATABASE_USER:-ecolla_app}"
APP_DATABASE_PASSWORD="${APP_DATABASE_PASSWORD:-}"
ADMIN_HOST="${POSTGRES_ADMIN_HOST:-}"
ADMIN_PORT="${POSTGRES_ADMIN_PORT:-5432}"
ADMIN_USER="${POSTGRES_ADMIN_USER:-postgres}"
ADMIN_PASSWORD="${POSTGRES_ADMIN_PASSWORD:-}"
readonly ENV_FILE="$PROJECT_ROOT/.env"
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
Create the PostgreSQL role/database, update Laravel's .env, and run migrations.

Usage:
  scripts/bootstrap-database.sh [options]

Options:
  --database NAME          Application database (default: ecolla_store_app)
  --username NAME          Application role (default: ecolla_app)
  --password PASSWORD      Application password; prompt when omitted
  --admin-host HOST        PostgreSQL admin host; local peer auth when omitted
  --admin-port PORT        PostgreSQL admin port (default: 5432)
  --admin-user USER        PostgreSQL admin role (default: postgres)
  --admin-password PASS    PostgreSQL admin password; psql prompt when omitted
  --seed none|dev|prod     Optional seeder after migration (default: none)
  --fresh                  Drop all application tables before migrating
  --yes                    Confirm the destructive --fresh operation
  -h, --help               Show this help

Passwords may also be supplied through APP_DATABASE_PASSWORD and
POSTGRES_ADMIN_PASSWORD to avoid storing them in shell history.
EOF
}

read_option_value() {
    local option="$1"
    local value="${2:-}"

    [[ -n "$value" ]] || fail "$option requires a value."
    printf '%s' "$value"
}

while [[ $# -gt 0 ]]; do
    case "$1" in
        --database)
            APP_DATABASE="$(read_option_value "$1" "${2:-}")"
            shift 2
            ;;
        --database=*)
            APP_DATABASE="${1#*=}"
            shift
            ;;
        --username)
            APP_DATABASE_USER="$(read_option_value "$1" "${2:-}")"
            shift 2
            ;;
        --username=*)
            APP_DATABASE_USER="${1#*=}"
            shift
            ;;
        --password)
            APP_DATABASE_PASSWORD="$(read_option_value "$1" "${2:-}")"
            shift 2
            ;;
        --password=*)
            APP_DATABASE_PASSWORD="${1#*=}"
            shift
            ;;
        --admin-host)
            ADMIN_HOST="$(read_option_value "$1" "${2:-}")"
            shift 2
            ;;
        --admin-host=*)
            ADMIN_HOST="${1#*=}"
            shift
            ;;
        --admin-port)
            ADMIN_PORT="$(read_option_value "$1" "${2:-}")"
            shift 2
            ;;
        --admin-port=*)
            ADMIN_PORT="${1#*=}"
            shift
            ;;
        --admin-user)
            ADMIN_USER="$(read_option_value "$1" "${2:-}")"
            shift 2
            ;;
        --admin-user=*)
            ADMIN_USER="${1#*=}"
            shift
            ;;
        --admin-password)
            ADMIN_PASSWORD="$(read_option_value "$1" "${2:-}")"
            shift 2
            ;;
        --admin-password=*)
            ADMIN_PASSWORD="${1#*=}"
            shift
            ;;
        --seed)
            SEEDER="$(read_option_value "$1" "${2:-}")"
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

[[ "$APP_DATABASE" =~ ^[A-Za-z_][A-Za-z0-9_]*$ ]] || fail "Invalid database name."
[[ "$APP_DATABASE_USER" =~ ^[A-Za-z_][A-Za-z0-9_]*$ ]] || fail "Invalid database username."
[[ "$ADMIN_USER" =~ ^[A-Za-z_][A-Za-z0-9_]*$ ]] || fail "Invalid admin username."
[[ "$ADMIN_PORT" =~ ^[0-9]+$ ]] || fail "Invalid PostgreSQL port."
[[ "$SEEDER" =~ ^(none|dev|prod)$ ]] || fail "--seed must be none, dev, or prod."
command -v psql >/dev/null || fail "psql is required. Install PostgreSQL first."
command -v php >/dev/null || fail "PHP is required."
[[ -f "$PROJECT_ROOT/vendor/autoload.php" ]] || fail "Run composer install before bootstrapping the database."

if [[ -z "$APP_DATABASE_PASSWORD" ]]; then
    if [[ ! -t 0 ]]; then
        fail "Set APP_DATABASE_PASSWORD when running non-interactively."
    fi

    read -r -s -p "New password for PostgreSQL role ${APP_DATABASE_USER}: " APP_DATABASE_PASSWORD
    printf '\n'
    [[ -n "$APP_DATABASE_PASSWORD" ]] || fail "The application database password cannot be empty."
fi

if [[ "$FRESH" == true && "$ASSUME_YES" != true ]]; then
    if [[ ! -t 0 ]]; then
        fail "--fresh requires --yes when running non-interactively."
    fi

    printf 'This will drop every table in database "%s". Type the database name to continue: ' "$APP_DATABASE"
    read -r confirmation
    [[ "$confirmation" == "$APP_DATABASE" ]] || fail "Fresh migration cancelled."
fi

if [[ ! -f "$ENV_FILE" ]]; then
    [[ -f "$PROJECT_ROOT/.env.example" ]] || fail ".env.example was not found."
    cp "$PROJECT_ROOT/.env.example" "$ENV_FILE"
    chmod 0600 "$ENV_FILE"
fi

PSQL_COMMAND=(psql --no-psqlrc --set=ON_ERROR_STOP=1)
if [[ -n "$ADMIN_HOST" ]]; then
    PSQL_COMMAND+=(--host="$ADMIN_HOST" --port="$ADMIN_PORT" --username="$ADMIN_USER")
elif [[ "$(uname -s)" == "Linux" ]] && id postgres >/dev/null 2>&1 && command -v sudo >/dev/null; then
    PSQL_COMMAND=(sudo -u postgres psql --no-psqlrc --set=ON_ERROR_STOP=1)
else
    PSQL_COMMAND+=(--host=127.0.0.1 --port="$ADMIN_PORT" --username="$ADMIN_USER")
fi

log "Creating or updating the PostgreSQL role and database"
APP_DATABASE_PASSWORD="$APP_DATABASE_PASSWORD" \
    PGPASSWORD="$ADMIN_PASSWORD" \
    "${PSQL_COMMAND[@]}" \
    --dbname=postgres \
    --set="app_database=$APP_DATABASE" \
    --set="app_user=$APP_DATABASE_USER" <<'SQL'
\getenv app_password APP_DATABASE_PASSWORD

SELECT format('CREATE ROLE %I LOGIN PASSWORD %L', :'app_user', :'app_password')
WHERE NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = :'app_user')
\gexec

SELECT format('ALTER ROLE %I WITH LOGIN PASSWORD %L', :'app_user', :'app_password')
\gexec

SELECT format(
    'CREATE DATABASE %I OWNER %I ENCODING %L TEMPLATE template0',
    :'app_database',
    :'app_user',
    'UTF8'
)
WHERE NOT EXISTS (SELECT 1 FROM pg_database WHERE datname = :'app_database')
\gexec

SELECT format('ALTER DATABASE %I OWNER TO %I', :'app_database', :'app_user')
\gexec
SQL

dotenv_value() {
    # shellcheck disable=SC2016
    php -r 'echo json_encode($argv[1], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);' "$1"
}

set_env_value() {
    local key="$1"
    local value="$2"

    # shellcheck disable=SC2016
    php -r '
        [$script, $path, $key, $value] = $argv;
        $contents = file_get_contents($path);
        if ($contents === false) {
            fwrite(STDERR, "Unable to read environment file.\n");
            exit(1);
        }

        $line = $key."=".$value;
        $pattern = "/^".preg_quote($key, "/")."=.*$/m";

        if (preg_match($pattern, $contents) === 1) {
            $contents = preg_replace_callback(
                $pattern,
                static fn (): string => $line,
                $contents,
                1,
            );
        } else {
            $contents = rtrim($contents).PHP_EOL.$line.PHP_EOL;
        }

        if (file_put_contents($path, $contents) === false) {
            fwrite(STDERR, "Unable to update environment file.\n");
            exit(1);
        }
    ' "$ENV_FILE" "$key" "$value"
}

log "Updating PostgreSQL settings in $ENV_FILE"
set_env_value DB_CONNECTION pgsql
set_env_value DB_HOST "$(dotenv_value "${ADMIN_HOST:-127.0.0.1}")"
set_env_value DB_PORT "$ADMIN_PORT"
set_env_value DB_DATABASE "$(dotenv_value "$APP_DATABASE")"
set_env_value DB_USERNAME "$(dotenv_value "$APP_DATABASE_USER")"
set_env_value DB_PASSWORD "$(dotenv_value "$APP_DATABASE_PASSWORD")"
chmod 0600 "$ENV_FILE"

cd "$PROJECT_ROOT"
php artisan config:clear --no-interaction

if ! grep -Eq '^APP_KEY=.+$' "$ENV_FILE"; then
    log "Generating the Laravel application key"
    php artisan key:generate --no-interaction
fi

if [[ "$FRESH" == true ]]; then
    log "Running a fresh migration"
    php artisan migrate:fresh --force --no-interaction
else
    log "Running outstanding migrations"
    php artisan migrate --force --no-interaction
fi

case "$SEEDER" in
    dev)
        log "Running DevSeeder"
        php artisan db:seed --class=DevSeeder --force --no-interaction
        ;;
    prod)
        log "Running ProdSeeder"
        php artisan db:seed --class=ProdSeeder --force --no-interaction
        ;;
esac

log "Database bootstrap complete"

#!/usr/bin/env bash

set -Eeuo pipefail

SCRIPT_NAME="$(basename "$0")"
readonly SCRIPT_NAME
readonly DEFAULT_PHP_VERSION="8.4"
readonly DEFAULT_NODE_MAJOR="24"
readonly DEFAULT_SITE_NAME="ecolla-store-app"

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd -P)"
APP_PATH="$PROJECT_ROOT"
APP_USER="${SUDO_USER:-www-data}"
DEPLOYMENT_METHOD="native"
DOMAIN=""
EMAIL=""
PHP_VERSION="$DEFAULT_PHP_VERSION"
NODE_MAJOR="$DEFAULT_NODE_MAJOR"
INSTALL_NODE=true

log() {
    printf '\n[%s] %s\n' "$SCRIPT_NAME" "$*"
}

fail() {
    printf '\n[%s] ERROR: %s\n' "$SCRIPT_NAME" "$*" >&2
    exit 1
}

usage() {
    cat <<'EOF'
Bootstrap a Debian-based host for this Laravel application.

Usage:
  sudo scripts/bootstrap-debian.sh [options]

Options:
  --app-path PATH              Application path (default: repository root)
  --app-user USER              Deployment/worker user (default: invoking sudo user)
  --deployment native|docker   Host services or Docker Compose (default: native)
  --domain DOMAIN              Configure Nginx and request a TLS certificate
  --email EMAIL                Certbot registration email (optional)
  --php-version VERSION        Native PHP version (default: 8.4)
  --node-major VERSION         Native Node.js LTS major (default: 24)
  --skip-node                  Skip system Node.js; use NVM for development
  -h, --help                   Show this help

Examples:
  sudo scripts/bootstrap-debian.sh --app-path /var/www/ecolla-store-app
  sudo scripts/bootstrap-debian.sh --app-path "$PWD" --skip-node
  sudo scripts/bootstrap-debian.sh --domain shop.example.com --email admin@example.com
  sudo scripts/bootstrap-debian.sh --deployment docker --domain shop.example.com
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
        --app-path)
            APP_PATH="$(read_option_value "$1" "${2:-}")"
            shift 2
            ;;
        --app-path=*)
            APP_PATH="${1#*=}"
            shift
            ;;
        --app-user)
            APP_USER="$(read_option_value "$1" "${2:-}")"
            shift 2
            ;;
        --app-user=*)
            APP_USER="${1#*=}"
            shift
            ;;
        --deployment)
            DEPLOYMENT_METHOD="$(read_option_value "$1" "${2:-}")"
            shift 2
            ;;
        --deployment=*)
            DEPLOYMENT_METHOD="${1#*=}"
            shift
            ;;
        --domain)
            DOMAIN="$(read_option_value "$1" "${2:-}")"
            shift 2
            ;;
        --domain=*)
            DOMAIN="${1#*=}"
            shift
            ;;
        --email)
            EMAIL="$(read_option_value "$1" "${2:-}")"
            shift 2
            ;;
        --email=*)
            EMAIL="${1#*=}"
            shift
            ;;
        --php-version)
            PHP_VERSION="$(read_option_value "$1" "${2:-}")"
            shift 2
            ;;
        --php-version=*)
            PHP_VERSION="${1#*=}"
            shift
            ;;
        --node-major)
            NODE_MAJOR="$(read_option_value "$1" "${2:-}")"
            shift 2
            ;;
        --node-major=*)
            NODE_MAJOR="${1#*=}"
            shift
            ;;
        --skip-node)
            INSTALL_NODE=false
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

[[ "$(id -u)" -eq 0 ]] || fail "Run this script with sudo or as root."
[[ "$DEPLOYMENT_METHOD" =~ ^(native|docker)$ ]] || fail "--deployment must be native or docker."
[[ "$APP_USER" =~ ^[a-z_][a-z0-9_-]*$ ]] || fail "Invalid application user: $APP_USER"
getent passwd "$APP_USER" >/dev/null || fail "Application user does not exist: $APP_USER"
[[ "$PHP_VERSION" =~ ^8\.[4-9]$ ]] || fail "PHP must be version 8.4 or newer in the 8.x series."
[[ "$NODE_MAJOR" =~ ^[0-9]+$ ]] || fail "Node.js major version must be numeric."

if [[ -n "$DOMAIN" ]]; then
    [[ "$DOMAIN" =~ ^([A-Za-z0-9]([A-Za-z0-9-]{0,61}[A-Za-z0-9])?\.)+[A-Za-z]{2,63}$ ]] \
        || fail "Invalid domain: $DOMAIN"
fi

[[ -d "$APP_PATH" ]] || fail "Application path does not exist: $APP_PATH"
APP_PATH="$(cd "$APP_PATH" && pwd -P)"
[[ "$APP_PATH" != *[[:space:]]* ]] || fail "Application paths containing whitespace are not supported."
[[ -f "$APP_PATH/artisan" ]] || fail "No Laravel artisan file found at $APP_PATH."

[[ -r /etc/os-release ]] || fail "Unable to identify this operating system."
# shellcheck disable=SC1091
source /etc/os-release

ID_LIKE="${ID_LIKE:-}"
if [[ "${ID:-}" != "debian" && "${ID:-}" != "ubuntu" && "$ID_LIKE" != *debian* && "$ID_LIKE" != *ubuntu* ]]; then
    fail "This script supports Debian, Ubuntu, and their derivatives."
fi

readonly DISTRO_CODENAME="${UBUNTU_CODENAME:-${VERSION_CODENAME:-}}"
[[ -n "$DISTRO_CODENAME" ]] || fail "Unable to determine the base distribution codename."

export DEBIAN_FRONTEND=noninteractive

log "Installing common operating-system packages"
apt-get update
apt-get install --yes \
    ca-certificates \
    curl \
    git \
    gnupg \
    nginx \
    sudo \
    unzip

install -d -m 0755 /etc/apt/keyrings

configure_php_repository() {
    if apt-cache show "php${PHP_VERSION}-fpm" >/dev/null 2>&1; then
        return
    fi

    log "Adding a PHP repository for PHP ${PHP_VERSION}"

    if [[ "${ID:-}" == "ubuntu" || "$ID_LIKE" == *ubuntu* ]]; then
        apt-get install --yes software-properties-common
        add-apt-repository --yes ppa:ondrej/php
    else
        curl --fail --silent --show-error --location \
            https://packages.sury.org/php/apt.gpg \
            --output /etc/apt/keyrings/sury-php.gpg

        cat >/etc/apt/sources.list.d/sury-php.list <<EOF
deb [signed-by=/etc/apt/keyrings/sury-php.gpg] https://packages.sury.org/php/ ${DISTRO_CODENAME} main
EOF
    fi

    apt-get update
}

configure_postgresql_repository() {
    log "Adding the PostgreSQL Apt repository"
    install -d -m 0755 /usr/share/postgresql-common/pgdg
    curl --fail --silent --show-error --location \
        https://www.postgresql.org/media/keys/ACCC4CF8.asc \
        --output /usr/share/postgresql-common/pgdg/apt.postgresql.org.asc

    cat >/etc/apt/sources.list.d/pgdg.sources <<EOF
Types: deb
URIs: https://apt.postgresql.org/pub/repos/apt
Suites: ${DISTRO_CODENAME}-pgdg
Components: main
Signed-By: /usr/share/postgresql-common/pgdg/apt.postgresql.org.asc
EOF
}

configure_node_repository() {
    local node_architecture

    node_architecture="$(dpkg --print-architecture)"
    [[ "$node_architecture" =~ ^(amd64|arm64)$ ]] \
        || fail "NodeSource supports amd64 and arm64; detected ${node_architecture}."

    log "Adding the dedicated NodeSource Apt repository for Node.js ${NODE_MAJOR}"
    install -d -m 0755 /usr/share/keyrings
    rm -f \
        /etc/apt/keyrings/nodesource.gpg \
        /etc/apt/sources.list.d/nodesource.list \
        /etc/apt/sources.list.d/nodesource.sources \
        /usr/share/keyrings/nodesource.gpg

    curl --fail --silent --show-error --location \
        https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key \
        | gpg --dearmor --yes --output /usr/share/keyrings/nodesource.gpg
    chmod 0644 /usr/share/keyrings/nodesource.gpg

    cat >/etc/apt/sources.list.d/nodesource.sources <<EOF
Types: deb
URIs: https://deb.nodesource.com/node_${NODE_MAJOR}.x
Suites: nodistro
Components: main
Architectures: ${node_architecture}
Signed-By: /usr/share/keyrings/nodesource.gpg
EOF

    cat >/etc/apt/preferences.d/nodejs <<'EOF'
Package: nodejs
Pin: origin deb.nodesource.com
Pin-Priority: 600
EOF
}

install_composer() {
    local installer
    local expected_checksum
    local actual_checksum

    log "Installing the latest stable Composer 2"
    installer="$(mktemp /tmp/composer-setup.XXXXXX.php)"
    expected_checksum="$(curl --fail --silent --show-error https://composer.github.io/installer.sig)"
    curl --fail --silent --show-error https://getcomposer.org/installer --output "$installer"
    # shellcheck disable=SC2016
    actual_checksum="$(php -r 'echo hash_file("sha384", $argv[1]);' "$installer")"

    if [[ "$expected_checksum" != "$actual_checksum" ]]; then
        rm -f "$installer"
        fail "Composer installer checksum verification failed."
    fi

    php "$installer" --quiet --install-dir=/usr/local/bin --filename=composer --2
    rm -f "$installer"
}

configure_docker_repository() {
    local docker_distribution

    if [[ "${ID:-}" == "ubuntu" || "$ID_LIKE" == *ubuntu* ]]; then
        docker_distribution="ubuntu"
    else
        docker_distribution="debian"
    fi

    log "Adding Docker's stable Apt repository"
    curl --fail --silent --show-error --location \
        "https://download.docker.com/linux/${docker_distribution}/gpg" \
        --output /etc/apt/keyrings/docker.asc
    chmod a+r /etc/apt/keyrings/docker.asc

    cat >/etc/apt/sources.list.d/docker.sources <<EOF
Types: deb
URIs: https://download.docker.com/linux/${docker_distribution}
Suites: ${DISTRO_CODENAME}
Components: stable
Architectures: $(dpkg --print-architecture)
Signed-By: /etc/apt/keyrings/docker.asc
EOF

    apt-get update
    apt-get install --yes \
        containerd.io \
        docker-buildx-plugin \
        docker-ce \
        docker-ce-cli \
        docker-compose-plugin

    systemctl enable --now docker

    if [[ "$APP_USER" != "root" ]]; then
        usermod --append --groups docker "$APP_USER"
    fi
}

if [[ "$DEPLOYMENT_METHOD" == "native" ]]; then
    configure_php_repository
    configure_postgresql_repository

    if [[ "$INSTALL_NODE" == true ]]; then
        configure_node_repository
    fi

    apt-get update

    native_packages=(
        "php${PHP_VERSION}-bcmath"
        "php${PHP_VERSION}-cli"
        "php${PHP_VERSION}-curl"
        "php${PHP_VERSION}-fpm"
        "php${PHP_VERSION}-gd"
        "php${PHP_VERSION}-intl"
        "php${PHP_VERSION}-mbstring"
        "php${PHP_VERSION}-opcache"
        "php${PHP_VERSION}-pgsql"
        "php${PHP_VERSION}-xml"
        "php${PHP_VERSION}-zip"
        cron
        postgresql
        postgresql-client
        supervisor
    )

    if [[ "$INSTALL_NODE" == true ]]; then
        native_packages+=(nodejs)
    fi

    log "Installing PHP, PHP-FPM, PostgreSQL, and process services"
    apt-get install --yes "${native_packages[@]}"

    update-alternatives --set php "/usr/bin/php${PHP_VERSION}"
    install_composer

    if [[ "$INSTALL_NODE" == true ]]; then
        [[ "$(/usr/bin/node --version)" == "v${NODE_MAJOR}."* ]] \
            || fail "NodeSource did not install Node.js ${NODE_MAJOR}."
        command -v npm >/dev/null || fail "The NodeSource nodejs package did not provide npm."
    else
        log "Skipping system Node.js; install Node ${NODE_MAJOR} with NVM as the application user."
    fi
else
    configure_docker_repository
fi

if [[ -n "$DOMAIN" ]]; then
    log "Installing Certbot"
    apt-get install --yes certbot python3-certbot-nginx
fi

readonly NGINX_SITE="/etc/nginx/sites-available/${DEFAULT_SITE_NAME}"
readonly NGINX_ENABLED="/etc/nginx/sites-enabled/${DEFAULT_SITE_NAME}"
SERVER_NAME="${DOMAIN:-_}"

log "Writing the Nginx site configuration"
if [[ "$DEPLOYMENT_METHOD" == "native" ]]; then
    cat >"$NGINX_SITE" <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name ${SERVER_NAME};
    root ${APP_PATH}/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF
else
    cat >"$NGINX_SITE" <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name ${SERVER_NAME};

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF
fi

ln -sfn "$NGINX_SITE" "$NGINX_ENABLED"
if [[ -e /etc/nginx/sites-enabled/default || -L /etc/nginx/sites-enabled/default ]]; then
    rm -f /etc/nginx/sites-enabled/default
fi

if [[ "$DEPLOYMENT_METHOD" == "native" ]]; then
    readonly SUPERVISOR_CONFIG="/etc/supervisor/conf.d/${DEFAULT_SITE_NAME}-worker.conf"
    readonly CRON_CONFIG="/etc/cron.d/${DEFAULT_SITE_NAME}-scheduler"

    log "Configuring the Laravel queue worker"
    cat >"$SUPERVISOR_CONFIG" <<EOF
[program:${DEFAULT_SITE_NAME}-worker]
process_name=%(program_name)s_%(process_num)02d
directory=${APP_PATH}
command=/usr/bin/php${PHP_VERSION} ${APP_PATH}/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=false
autorestart=true
stopasgroup=true
killasgroup=true
user=${APP_USER}
numprocs=1
redirect_stderr=true
stdout_logfile=${APP_PATH}/storage/logs/worker.log
stopwaitsecs=3600
EOF

    log "Configuring the Laravel scheduler"
    cat >"$CRON_CONFIG" <<EOF
* * * * * ${APP_USER} cd ${APP_PATH} && /usr/bin/php${PHP_VERSION} artisan schedule:run >> /dev/null 2>&1
EOF
    chmod 0644 "$CRON_CONFIG"

    usermod --append --groups www-data "$APP_USER"
    chown -R "$APP_USER:www-data" "$APP_PATH/storage" "$APP_PATH/bootstrap/cache"
    find "$APP_PATH/storage" "$APP_PATH/bootstrap/cache" -type d -exec chmod 2775 {} +
    find "$APP_PATH/storage" "$APP_PATH/bootstrap/cache" -type f -exec chmod 0664 {} +

    systemctl enable --now \
        cron \
        "php${PHP_VERSION}-fpm" \
        postgresql \
        supervisor
    supervisorctl reread
    supervisorctl update
fi

nginx -t
systemctl enable --now nginx
systemctl reload nginx

if [[ -n "$DOMAIN" ]]; then
    log "Requesting a Let's Encrypt certificate for ${DOMAIN}"
    if [[ -n "$EMAIL" ]]; then
        certbot --nginx \
            --non-interactive \
            --agree-tos \
            --redirect \
            --keep-until-expiring \
            --email "$EMAIL" \
            --domain "$DOMAIN"
    else
        certbot --nginx \
            --non-interactive \
            --agree-tos \
            --redirect \
            --keep-until-expiring \
            --register-unsafely-without-email \
            --domain "$DOMAIN"
    fi
fi

log "Bootstrap complete"
printf 'Deployment method: %s\n' "$DEPLOYMENT_METHOD"
printf 'Application path:  %s\n' "$APP_PATH"
printf 'Application user:  %s\n' "$APP_USER"
printf 'Nginx server name: %s\n' "$SERVER_NAME"

if [[ "$DEPLOYMENT_METHOD" == "native" ]]; then
    printf 'PHP:               %s\n' "$(php --version | head -n 1)"

    if [[ "$INSTALL_NODE" == true ]]; then
        printf 'Node.js:           %s\n' "$(/usr/bin/node --version)"
        printf 'npm:               %s\n' "$(/usr/bin/npm --version)"
    else
        printf 'Node.js:           skipped (install with NVM for development)\n'
    fi

    printf 'Composer:          %s\n' "$(composer --version --no-ansi)"
    printf 'PostgreSQL:        %s\n' "$(psql --version)"
else
    printf 'Docker:            %s\n' "$(docker --version)"
    printf 'Docker Compose:    %s\n' "$(docker compose version)"
    printf '\nLog out and back in before running Docker without sudo.\n'
fi

printf '\nNext: configure .env, then run the matching deployment script documented in README.md.\n'

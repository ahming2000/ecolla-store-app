# Ecolla Store

Ecolla Store is a Laravel 13, Inertia 3, and Vue 3 application for storefront browsing, ordering, and administration. This repository is the refactored successor to `ecolla-store-app`.

## Features

- Storefront item listing, variations, cart, checkout, and payment instructions
- Admin authentication and role-based management
- Item, image, variation, category, origin, order, user, and setting management
- English and Chinese interfaces
- PostgreSQL-backed sessions, cache, and queues

## Required software

The checked-in lock files are the source of truth. The supported development and production baseline is:

| Software        | Version or role                                                                              |
| --------------- | -------------------------------------------------------------------------------------------- |
| PHP             | 8.4 or newer 8.x, with FPM in native production                                              |
| PHP extensions  | bcmath, cURL, DOM/XML, fileinfo, GD, intl, mbstring, OpenSSL, PDO PostgreSQL, tokenizer, ZIP |
| Node.js         | 24 LTS: NVM for local development; NodeSource Apt for native production                      |
| Composer        | Latest Composer 2                                                                            |
| PostgreSQL      | Latest stable release; PostgreSQL 18 is pinned in Docker and CI                              |
| Nginx           | Web server and reverse proxy                                                                 |
| Git             | Source checkout and deployment                                                               |
| Supervisor/cron | Keep the native queue worker and scheduler running                                           |
| Certbot         | Optional; installed when a deployment domain is supplied                                     |
| Docker          | Laravel Sail for local development; optimized custom images for production                   |

Node.js is required even though Laravel is primarily PHP: Vite, Vue, TypeScript, Wayfinder, and the production frontend build all use Node and npm.

The native Debian production bootstrap installs all of the above. Local Linux and macOS development can use NVM instead. In Docker mode, PHP, Node.js, PostgreSQL, and their extensions are supplied by the containers.

## Choose a local development method

| Environment                          | Recommended method                                                        |
| ------------------------------------ | ------------------------------------------------------------------------- |
| Debian, Ubuntu, Zorin OS, Linux Mint | Native bootstrap plus `composer run dev`, or Laravel Sail                 |
| macOS                                | Laravel Herd plus PostgreSQL, or Laravel Sail with Docker Desktop         |
| Windows                              | Laravel Herd plus PostgreSQL, or Laravel Sail through Docker Desktop/WSL2 |

Laravel Sail is the standardized Docker development option. Herd is the simplest native option on macOS and Windows.

## Common repository setup

Clone the repository and enter it. The checked-in `.nvmrc` pins Node.js 24 for NVM and CI:

```bash
git clone <repository-url> ecolla-store-app
cd ecolla-store-app
```

Never commit `.env`. Production secrets should be managed on the server or through a secret manager.

## PrimeUI license setup

PrimeVue 5 requires a PrimeUI license at application startup. Store the license in the ignored `.env` file after running `npm ci`:

```bash
bash scripts/configure-primeui.sh
```

The interactive prompt does not echo the key. For CI or other non-interactive automation, provide it through a secret manager:

```bash
PRIMEUI_LICENSE_KEY='your-license-key' bash scripts/configure-primeui.sh
```

The script validates the signed key when the installed PrimeUI license manager is available and writes `VITE_PRIMEUI_LICENSE_KEY` without printing it. Restart `npm run dev` or rebuild assets after changing the key. Vite intentionally embeds this value in the client bundle so PrimeUI can validate it in the browser; never hard-code or commit it in source files.

## Linux development

The native script supports current Debian/Ubuntu releases and common derivatives. For development, let it install the backend services while leaving Node.js to the current user's NVM installation:

```bash
sudo bash scripts/bootstrap-debian.sh \
    --app-path "$PWD" \
    --app-user "$USER" \
    --skip-node
```

Install NVM as the normal development user, never with `sudo`, then install the Node.js version from `.nvmrc`:

```bash
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.4/install.sh | bash

export NVM_DIR="$([ -z "${XDG_CONFIG_HOME-}" ] && printf %s "$HOME/.nvm" || printf %s "$XDG_CONFIG_HOME/nvm")"
[ -s "$NVM_DIR/nvm.sh" ] && . "$NVM_DIR/nvm.sh"

nvm install
nvm use
node --version
npm --version
```

Install project dependencies and create the application environment:

```bash
composer install
npm ci
cp .env.example .env
bash scripts/configure-primeui.sh
```

Create a dedicated PostgreSQL role and database, update `.env`, migrate, and load development data:

```bash
APP_DATABASE_PASSWORD='choose-a-local-password' \
    bash scripts/bootstrap-database.sh --seed=dev
```

The password environment variable avoids putting the password in shell history. If omitted in an interactive terminal, the script prompts for it.

Start Laravel's current starter-project development processes:

```bash
composer run dev
```

This starts the Laravel development server, queue listener, Pail log output, and Vite. Open `http://localhost:8000`.

To develop through the Nginx site installed by the bootstrap instead:

```bash
npm run build
sudo supervisorctl start "ecolla-store-app-worker:*"
```

Open `http://localhost`. Run `npm run dev` in a separate terminal when Vite hot reload is needed.

## macOS development with Laravel Herd

### Install required applications

1. Download the [Laravel Herd DMG](https://herd.laravel.com/docs/macos/getting-started/installation).
2. Double-click the DMG, drag Herd to **Applications**, open Herd, and complete onboarding.
3. In Herd, install/select PHP 8.4 for this site. Herd already supplies Nginx, PHP, Composer, Node.js, npm, and the Laravel CLI; separate DMGs for those tools are not required.
4. Download the PostgreSQL 18 interactive installer from the [official PostgreSQL macOS page](https://www.postgresql.org/download/macosx/). Open the downloaded installer, keep port `5432`, install the server and command-line tools, and remember the `postgres` administrator password.
5. Install Git if `git --version` is unavailable. On macOS, `xcode-select --install` installs Apple's Command Line Tools and Git.

Herd already includes Node.js. If you prefer the same project-specific Node selection used on Linux and CI, install NVM as your normal macOS user and run `nvm install` in the repository:

```bash
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.4/install.sh | bash

export NVM_DIR="$([ -z "${XDG_CONFIG_HOME-}" ] && printf %s "$HOME/.nvm" || printf %s "$XDG_CONFIG_HOME/nvm")"
[ -s "$NVM_DIR/nvm.sh" ] && . "$NVM_DIR/nvm.sh"

nvm install
nvm use
```

If the PostgreSQL installer does not add `psql` to the shell path, add its binary directory:

```bash
export PATH="/Library/PostgreSQL/18/bin:$PATH"
```

Persist that line in `~/.zshrc` after confirming the installed version directory.

### Set up the project

Projects inside `~/Herd` are served automatically as `.test` domains:

```bash
cd ~/Herd/ecolla-store-app
composer install
npm ci
cp .env.example .env
bash scripts/configure-primeui.sh
bash scripts/bootstrap-database.sh \
    --admin-host=127.0.0.1 \
    --admin-user=postgres \
    --seed=dev
```

The scripts prompt for the PrimeUI key, the new application-role password, and the PostgreSQL administrator password.

Herd serves the Laravel application, so only start Vite for frontend hot reload:

```bash
npm run dev
```

Open `http://ecolla-store-app.test`. Run `php artisan queue:work` in a separate terminal when testing queued jobs.

## Windows development with Laravel Herd

The recommended beginner-friendly native setup is:

1. Install [Git for Windows](https://git-scm.com/download/win). Keep **Git Bash** enabled; it runs this repository's Bash scripts.
2. Install [Laravel Herd for Windows](https://herd.laravel.com/windows). Complete onboarding and select PHP 8.4. Herd supplies PHP, Composer, Node.js, npm, Nginx, and the Laravel CLI.
3. Install PostgreSQL 18 using the [official Windows installer](https://www.postgresql.org/download/windows/). Keep port `5432`, install the command-line tools and pgAdmin, and remember the `postgres` password.
4. Add `C:\Program Files\PostgreSQL\18\bin` to the Windows user `PATH` if `psql --version` is not found in a new Git Bash terminal.

From Git Bash:

```bash
cd /c/Users/<your-user>/Herd/ecolla-store-app
composer install
npm ci
cp .env.example .env
bash scripts/configure-primeui.sh
bash scripts/bootstrap-database.sh \
    --admin-host=127.0.0.1 \
    --admin-user=postgres \
    --seed=dev
npm run dev
```

Open the `.test` site shown by Herd. Run `php artisan queue:work` in a separate terminal when testing queued jobs.

The `nvm-sh/nvm` project supports Linux, macOS, and Windows through WSL; it does not manage native Windows Node installations from Git Bash. For the native Herd workflow, use Herd's bundled Node.js. If developing inside WSL, use the Linux NVM instructions above.

For a development environment closer to the Debian production server, use the Docker method below instead of installing PostgreSQL locally.

## Docker development with Laravel Sail

[Laravel Sail](https://laravel.com/docs/13.x/sail) is installed as a development dependency and is the supported local Docker workflow. Sail supplies PHP 8.4, Composer, Node.js 24/npm, PostgreSQL 18, and Laravel's development server. This project extends the standard Sail composition with dedicated Vite and queue-worker services.

Install Docker Engine with Compose v2 on Linux, or install Docker Desktop on [macOS](https://docs.docker.com/desktop/setup/install/mac-install/) or [Windows](https://docs.docker.com/desktop/setup/install/windows-install/). Sail supports native Linux, macOS, and Windows through WSL2. On Windows, enable Docker Desktop's WSL integration and run Sail commands inside the WSL distribution rather than Git Bash.

No host PHP, Composer, Node.js, NVM, or PostgreSQL installation is required for this method. When `vendor/bin/sail` is missing, the bootstrap wrapper uses Sail's PHP 8.4 Composer image to install the locked dependencies first.

Create the local environment and start the complete stack with development seed data:

```bash
cp .env.example .env
bash scripts/configure-primeui.sh
bash scripts/docker-development.sh --seed=dev
```

The Compose environment overrides the native `.env` database host with the Sail service name `pgsql`, so `.env.example` remains suitable for both native and Sail development.

- Application: `http://localhost`
- Vite: `http://localhost:5173`
- PostgreSQL from the host: `127.0.0.1:5432`
- Logs: `./vendor/bin/sail logs --follow`
- Stop without deleting data: `./vendor/bin/sail stop`

The PostgreSQL database is stored in the `sail-pgsql` named volume and survives `sail stop` and `sail down`.

### Sail commands

Run application commands through Sail:

```bash
./vendor/bin/sail artisan migrate
./vendor/bin/sail composer install
./vendor/bin/sail npm run build
./vendor/bin/sail psql
./vendor/bin/sail test
./vendor/bin/sail shell
```

For a shorter command, add this alias to `~/.bashrc` or `~/.zshrc`:

```bash
alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'
```

You may then use commands such as `sail up -d`, `sail artisan`, and `sail npm`.

To enable Xdebug, add the following to `.env`, rebuild, and restart Sail:

```dotenv
SAIL_XDEBUG_MODE=develop,debug
```

```bash
./vendor/bin/sail build --no-cache
./vendor/bin/sail up -d
```

To deliberately reset the Sail development database:

```bash
bash scripts/docker-development.sh --fresh --yes --seed=dev
```

To remove every Sail container and its local PostgreSQL volume:

```bash
./vendor/bin/sail down --volumes
```

The last command permanently deletes the local Docker database.

## Database bootstrap options

The native cross-platform database script is safe to re-run: it creates or updates the PostgreSQL role, creates the database only when absent, runs outstanding migrations, and does not seed unless requested.

```bash
bash scripts/bootstrap-database.sh --help
```

Common modes:

```bash
# Development data
bash scripts/bootstrap-database.sh --seed=dev

# First production initialization
bash scripts/bootstrap-database.sh --seed=prod

# Destructive local reset; requires explicit confirmation
bash scripts/bootstrap-database.sh --fresh --seed=dev
```

Do not use `--fresh` on production. `ProdSeeder` is intended for the first initialization only; it is not run during normal deployments.

At present, `ProdSeeder` creates an `admin` account with the temporary password `password`. Change it immediately through admin user management before making the site public.

## Tests and quality checks

Run the PHP checks and tests:

```bash
composer test
```

Run frontend type checks and a production build:

```bash
npm run check
```

Run browser tests:

```bash
npm run test:e2e
```

When developing through Sail, equivalent containerized commands are:

```bash
./vendor/bin/sail test
./vendor/bin/sail npm run check
./vendor/bin/sail npm run test:e2e
```

The reusable CI entry point installs locked dependencies, verifies the schema and `ProdSeeder` against PostgreSQL when configured, runs PHP formatting/static analysis/PHPUnit, builds the frontend, and audits Composer dependencies:

```bash
bash ci/verify.sh
```

## Compute Engine deployment

The default production target is a single Google Compute Engine VM running Debian 13. Ubuntu and supported derivatives also work, but Debian is the reference environment.

Before provisioning:

- Reserve a static external IP for the VM.
- Allow inbound TCP `80` and `443` in the Google Cloud firewall.
- If using a domain, point its DNS `A`/`AAAA` record to the VM before requesting the certificate.
- Add a deploy key or another read-only Git credential on the VM so it can fetch this repository.

### Native deployment

Clone the project as the deployment user:

```bash
sudo install -d -o "$USER" -g "$USER" /var/www/ecolla-store-app
git clone <repository-url> /var/www/ecolla-store-app
cd /var/www/ecolla-store-app
```

Provision the host. Omit `--domain` and `--email` to use plain HTTP by IP during initial testing:

```bash
sudo bash scripts/bootstrap-debian.sh \
    --deployment=native \
    --app-path="$PWD" \
    --app-user="$USER" \
    --domain=shop.example.com \
    --email=admin@example.com
```

The certificate step uses Certbot and only succeeds after DNS points to the VM and ports 80/443 are reachable.

For native production, do not pass `--skip-node`. The bootstrap configures the dedicated signed NodeSource `node_24.x` Apt repository, pins the `nodejs` package to that repository, installs it with `apt`, and verifies that Node.js 24 and npm are available system-wide. Production does not depend on a deployment user's NVM shell configuration.

Prepare the application:

```bash
composer install --no-dev --no-interaction --prefer-dist
cp .env.example .env
bash scripts/configure-primeui.sh
nano .env
```

At minimum, set:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://shop.example.com
DB_CONNECTION=pgsql
SESSION_SECURE_COOKIE=true
```

Create the database and seed the first production records:

```bash
APP_DATABASE_PASSWORD='use-a-long-random-password' \
    bash scripts/bootstrap-database.sh --seed=prod
```

Build and deploy:

```bash
bash scripts/deploy.sh
```

Normal native deployments install locked production dependencies, build frontend assets, enable maintenance mode, run non-destructive migrations, optimize Laravel, reload workers/scheduled tasks, and restore the application. Seeding is intentionally separate.

### Docker deployment

Docker deployment keeps only Git, Docker Engine/Compose, host Nginx, and optional Certbot on the VM. PHP/FPM, Node.js, PostgreSQL, Nginx application serving, queues, and scheduling run in containers. Host Nginx proxies to the container on `127.0.0.1:8080`.

Production does not use Sail. Sail remains a development-only Composer dependency; `compose.production.yaml` builds the optimized production PHP-FPM and Nginx images without development packages.

Provision Docker mode:

```bash
sudo bash scripts/bootstrap-debian.sh \
    --deployment=docker \
    --app-path="$PWD" \
    --app-user="$USER" \
    --domain=shop.example.com \
    --email=admin@example.com
```

Log out and reconnect once so the deployment user receives Docker group membership. Membership in the Docker group grants root-equivalent control of the host; restrict it to trusted deployment users.

Create and configure `.env`:

```bash
cp .env.example .env
bash scripts/configure-primeui.sh
nano .env
```

Set production values and a strong PostgreSQL password:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://shop.example.com
APP_KEY=
DB_DATABASE=ecolla_store_app
DB_USERNAME=ecolla_app
DB_PASSWORD=use-a-long-random-password
SESSION_SECURE_COOKIE=true
```

Generate the key without installing PHP on the host:

```bash
docker compose -f compose.production.yaml build app
docker compose -f compose.production.yaml run --rm app php artisan key:generate --show
```

Copy the returned value into `APP_KEY`, then perform the first deployment and seed:

```bash
bash scripts/deploy-docker.sh --seed=prod
```

Subsequent deployments must not seed:

```bash
bash scripts/deploy-docker.sh
```

The PostgreSQL data, Laravel storage, uploaded files, and optimized cache use named Docker volumes. Back up the PostgreSQL and uploaded-file volumes before operating-system or destructive database maintenance.

## Manual verification and deployment

GitHub Actions CI and deployment workflows are intentionally disabled. Pushing a branch or opening a pull request will not run checks or deploy the application.

Run the reusable checks locally when needed:

```bash
bash ci/verify.sh
bash ci/build-containers.sh
```

Deploy directly from the Compute Engine VM using `scripts/deploy.sh` for native deployments or `scripts/deploy-docker.sh` for Docker deployments, following the preceding Compute Engine instructions.

## Operational notes

- Laravel's health endpoint is `/up`.
- Native queue workers are managed by Supervisor.
- Native scheduled tasks run through `/etc/cron.d/ecolla-store-app-scheduler`.
- Native production installs system-wide Node.js 24 from the dedicated NodeSource Apt repository; local development may use `.nvmrc` through NVM.
- Local Docker development uses Sail with dedicated Vite and queue services.
- Production Docker uses the separate optimized Compose stack with dedicated queue and scheduler services.
- `APP_DEBUG` must always be `false` in production.
- The native PostgreSQL Apt meta-package follows the latest stable PostgreSQL major. Docker configurations and local container verification are pinned to PostgreSQL 18 for reproducible builds; update the image deliberately after testing a major upgrade and planning a database migration.
- If a frontend change is not visible, run `npm run build` for production or confirm `npm run dev` is running locally.

## Useful links

- [Laravel 13 deployment documentation](https://laravel.com/docs/13.x/deployment)
- [Laravel 13 development command](https://laravel.com/docs/13.x/artisan#the-dev-command)
- [Laravel Sail](https://laravel.com/docs/13.x/sail)
- [Laravel Herd](https://herd.laravel.com/)
- [PrimeVue Vite installation and licensing](https://primevue.dev/vite)
- [PrimeUI Community license](https://primeui.dev/licenses/community)
- [NVM installation and usage](https://github.com/nvm-sh/nvm)
- [NodeSource Debian/Ubuntu packages](https://github.com/nodesource/distributions)
- [PostgreSQL Apt repository](https://www.postgresql.org/download/linux/debian/)
- [Official PostgreSQL Docker image](https://hub.docker.com/_/postgres)
- [Docker Engine installation](https://docs.docker.com/engine/install/)
- [Docker Compose in production](https://docs.docker.com/compose/how-tos/production/)

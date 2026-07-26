#!/usr/bin/env bash

set -Eeuo pipefail

SCRIPT_NAME="$(basename "$0")"
readonly SCRIPT_NAME
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd -P)"
IMAGE_PREFIX="${CI_IMAGE_PREFIX:-ecolla-store-ci}"
BUILD_ARGUMENTS=(--build-arg PRIMEUI_LICENSE_FINGERPRINT=unlicensed)
BUILD_SECRET_ARGUMENTS=()

if [[ -n "${VITE_PRIMEUI_LICENSE_KEY:-}" ]]; then
    if command -v sha256sum >/dev/null; then
        PRIMEUI_LICENSE_FINGERPRINT="$(
            printf '%s' "$VITE_PRIMEUI_LICENSE_KEY" | sha256sum | cut -d' ' -f1
        )"
    else
        PRIMEUI_LICENSE_FINGERPRINT="$(
            printf '%s' "$VITE_PRIMEUI_LICENSE_KEY" | shasum -a 256 | cut -d' ' -f1
        )"
    fi

    BUILD_ARGUMENTS=(--build-arg "PRIMEUI_LICENSE_FINGERPRINT=$PRIMEUI_LICENSE_FINGERPRINT")
    BUILD_SECRET_ARGUMENTS+=(--secret "id=primeui_license,env=VITE_PRIMEUI_LICENSE_KEY")
fi

log() {
    printf '\n[%s] %s\n' "$SCRIPT_NAME" "$*"
}

cd "$PROJECT_ROOT"

log "Building the production PHP-FPM image"
docker build \
    "${BUILD_ARGUMENTS[@]}" \
    "${BUILD_SECRET_ARGUMENTS[@]}" \
    --file ci/docker/Dockerfile \
    --target production \
    --tag "${IMAGE_PREFIX}:application" \
    .

log "Building the production Nginx image"
docker build \
    "${BUILD_ARGUMENTS[@]}" \
    "${BUILD_SECRET_ARGUMENTS[@]}" \
    --file ci/docker/Dockerfile \
    --target nginx-production \
    --tag "${IMAGE_PREFIX}:nginx" \
    .

log "Container builds complete"

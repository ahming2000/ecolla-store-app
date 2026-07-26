#!/usr/bin/env bash

set -Eeuo pipefail

SCRIPT_NAME="$(basename "$0")"
readonly SCRIPT_NAME

log() {
    printf '\n[%s] %s\n' "$SCRIPT_NAME" "$*"
}

fail() {
    printf '\n[%s] ERROR: %s\n' "$SCRIPT_NAME" "$*" >&2
    exit 1
}

required_environment=(
    GCP_PROJECT_ID
    GCE_INSTANCE
    GCE_ZONE
    GCE_APP_PATH
)

for variable_name in "${required_environment[@]}"; do
    [[ -n "${!variable_name:-}" ]] || fail "$variable_name is required."
done

DEPLOY_SHA="${DEPLOY_SHA:-${GITHUB_SHA:-}}"
DEPLOYMENT_METHOD="${DEPLOYMENT_METHOD:-native}"
GCE_USE_IAP="${GCE_USE_IAP:-false}"

[[ "$DEPLOY_SHA" =~ ^[0-9a-f]{40}$ ]] || fail "DEPLOY_SHA must be a full Git commit SHA."
[[ "$DEPLOYMENT_METHOD" =~ ^(native|docker)$ ]] || fail "DEPLOYMENT_METHOD must be native or docker."
[[ "$GCP_PROJECT_ID" =~ ^[a-z][a-z0-9-]{4,28}[a-z0-9]$ ]] || fail "Invalid GCP_PROJECT_ID."
[[ "$GCE_INSTANCE" =~ ^[a-z]([-a-z0-9]{0,61}[a-z0-9])?$ ]] || fail "Invalid GCE_INSTANCE."
[[ "$GCE_ZONE" =~ ^[a-z0-9-]+$ ]] || fail "Invalid GCE_ZONE."
[[ "$GCE_APP_PATH" == /* && "$GCE_APP_PATH" =~ ^/[A-Za-z0-9_./-]+$ ]] || fail "Invalid GCE_APP_PATH."
[[ "$GCE_USE_IAP" =~ ^(true|false)$ ]] || fail "GCE_USE_IAP must be true or false."
command -v gcloud >/dev/null || fail "gcloud is required."

SSH_TARGET="$GCE_INSTANCE"
if [[ -n "${GCE_SSH_USER:-}" ]]; then
    [[ "$GCE_SSH_USER" =~ ^[a-z_][a-z0-9_-]*$ ]] || fail "Invalid GCE_SSH_USER."
    SSH_TARGET="${GCE_SSH_USER}@${GCE_INSTANCE}"
fi

if [[ "$DEPLOYMENT_METHOD" == "docker" ]]; then
    REMOTE_DEPLOY_SCRIPT="scripts/deploy-docker.sh"
else
    REMOTE_DEPLOY_SCRIPT="scripts/deploy.sh"
fi

REMOTE_COMMAND="set -Eeuo pipefail; cd ${GCE_APP_PATH}; git fetch --prune origin; git checkout --detach ${DEPLOY_SHA}; bash ${REMOTE_DEPLOY_SCRIPT}"
GCLOUD_ARGUMENTS=(
    compute
    ssh
    "$SSH_TARGET"
    "--project=$GCP_PROJECT_ID"
    "--zone=$GCE_ZONE"
    --quiet
    "--command=$REMOTE_COMMAND"
)

if [[ "$GCE_USE_IAP" == "true" ]]; then
    GCLOUD_ARGUMENTS+=(--tunnel-through-iap)
fi

log "Deploying ${DEPLOY_SHA} to ${GCE_INSTANCE} using ${DEPLOYMENT_METHOD} mode"
gcloud "${GCLOUD_ARGUMENTS[@]}"
log "Compute Engine deployment complete"

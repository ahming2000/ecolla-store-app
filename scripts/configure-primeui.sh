#!/usr/bin/env bash

set -Eeuo pipefail

SCRIPT_NAME="$(basename "$0")"
readonly SCRIPT_NAME
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd -P)"
readonly ENV_FILE="$PROJECT_ROOT/.env"
LICENSE_KEY="${PRIMEUI_LICENSE_KEY:-${VITE_PRIMEUI_LICENSE_KEY:-}}"
TEMP_ENV_FILE=""

log() {
    printf '\n[%s] %s\n' "$SCRIPT_NAME" "$*"
}

fail() {
    printf '\n[%s] ERROR: %s\n' "$SCRIPT_NAME" "$*" >&2
    exit 1
}

cleanup() {
    if [[ -n "$TEMP_ENV_FILE" && -f "$TEMP_ENV_FILE" ]]; then
        rm -f "$TEMP_ENV_FILE"
    fi
}

trap cleanup EXIT

usage() {
    cat <<'EOF'
Validate a PrimeUI license and store it in the ignored Laravel .env file.

Usage:
  scripts/configure-primeui.sh

For interactive use, the script prompts without echoing the key. For automation,
provide PRIMEUI_LICENSE_KEY or VITE_PRIMEUI_LICENSE_KEY through a secret manager.
The key is never printed.
EOF
}

if [[ "${1:-}" == "-h" || "${1:-}" == "--help" ]]; then
    usage
    exit 0
fi

[[ $# -eq 0 ]] || fail "Unknown option: $1"

cd "$PROJECT_ROOT"

if [[ -z "$LICENSE_KEY" ]]; then
    if [[ ! -t 0 ]]; then
        fail "Set PRIMEUI_LICENSE_KEY when running non-interactively."
    fi

    read -r -s -p "PrimeUI license key: " LICENSE_KEY
    printf '\n'
fi

[[ -n "$LICENSE_KEY" ]] || fail "The PrimeUI license key cannot be empty."
[[ "$LICENSE_KEY" =~ ^[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+$ ]] \
    || fail "The PrimeUI license key has an invalid format."

if command -v sha256sum >/dev/null; then
    LICENSE_FINGERPRINT="$(printf '%s' "$LICENSE_KEY" | sha256sum | cut -d' ' -f1)"
elif command -v shasum >/dev/null; then
    LICENSE_FINGERPRINT="$(printf '%s' "$LICENSE_KEY" | shasum -a 256 | cut -d' ' -f1)"
else
    fail "sha256sum or shasum is required."
fi

LICENSE_METADATA=""

if command -v php >/dev/null; then
    LICENSE_METADATA="$(
        PRIMEUI_LICENSE_KEY_INPUT="$LICENSE_KEY" php <<'PHP'
<?php

declare(strict_types=1);

$licenseKey = getenv('PRIMEUI_LICENSE_KEY_INPUT');
if (! is_string($licenseKey)) {
    fwrite(STDERR, "Unable to read the PrimeUI license key.\n");
    exit(1);
}

[$encodedPayload] = explode('.', $licenseKey, 2);
$padding = strlen($encodedPayload) % 4;
if ($padding !== 0) {
    $encodedPayload .= str_repeat('=', 4 - $padding);
}

$decodedPayload = base64_decode(strtr($encodedPayload, '-_', '+/'), true);
if ($decodedPayload === false) {
    fwrite(STDERR, "The PrimeUI license payload is not valid base64url.\n");
    exit(1);
}

try {
    $payload = json_decode($decodedPayload, true, flags: JSON_THROW_ON_ERROR);
} catch (JsonException) {
    fwrite(STDERR, "The PrimeUI license payload is not valid JSON.\n");
    exit(1);
}

if (! is_array($payload) || ($payload['product'] ?? null) !== 'primeui') {
    fwrite(STDERR, "The license does not cover PrimeUI.\n");
    exit(1);
}

$expiresAt = $payload['exp'] ?? null;
if (! is_int($expiresAt) || $expiresAt <= time()) {
    fwrite(STDERR, "The PrimeUI license is expired or has no valid expiry.\n");
    exit(1);
}

$tier = is_string($payload['tier'] ?? null) ? $payload['tier'] : 'unknown';
$type = is_string($payload['type'] ?? null) ? $payload['type'] : 'unknown';

printf('%s|%s|%s', $tier, $type, gmdate('Y-m-d', $expiresAt));
PHP
    )" || fail "Unable to validate the PrimeUI license payload."
fi

if command -v node >/dev/null && [[ -d node_modules/@primeui/license-manager ]]; then
    PRIMEUI_LICENSE_KEY_INPUT="$LICENSE_KEY" node --input-type=module <<'NODE'
import { registerLicense, verifyLicense } from '@primeui/license-manager'

registerLicense({ primeui: process.env.PRIMEUI_LICENSE_KEY_INPUT })

const result = await verifyLicense('primeui')

if (!result.valid) {
    console.error(`[configure-primeui.sh] ERROR: ${result.message}`)
    process.exit(1)
}
NODE
fi

if [[ ! -f "$ENV_FILE" ]]; then
    [[ -f .env.example ]] || fail ".env.example was not found."
    cp .env.example "$ENV_FILE"
fi

TEMP_ENV_FILE="$(mktemp "${ENV_FILE}.XXXXXX")"
PRIMEUI_LICENSE_FINGERPRINT_INPUT="$LICENSE_FINGERPRINT" \
    PRIMEUI_LICENSE_KEY_INPUT="$LICENSE_KEY" \
    awk '
    BEGIN {
        replacement = "VITE_PRIMEUI_LICENSE_KEY=" ENVIRON["PRIMEUI_LICENSE_KEY_INPUT"]
        fingerprintReplacement = "PRIMEUI_LICENSE_FINGERPRINT=" ENVIRON["PRIMEUI_LICENSE_FINGERPRINT_INPUT"]
        licenseConfigured = 0
        fingerprintConfigured = 0
    }

    /^VITE_PRIMEUI_LICENSE_KEY=/ {
        if (licenseConfigured == 0) {
            print replacement
            licenseConfigured = 1
        }
        next
    }

    /^PRIMEUI_LICENSE_FINGERPRINT=/ {
        if (fingerprintConfigured == 0) {
            print fingerprintReplacement
            fingerprintConfigured = 1
        }
        next
    }

    {
        print
    }

    END {
        if (licenseConfigured == 0) {
            print replacement
        }
        if (fingerprintConfigured == 0) {
            print fingerprintReplacement
        }
    }
' "$ENV_FILE" >"$TEMP_ENV_FILE"

chmod 0600 "$TEMP_ENV_FILE"
mv "$TEMP_ENV_FILE" "$ENV_FILE"
TEMP_ENV_FILE=""

if [[ -n "$LICENSE_METADATA" ]]; then
    IFS='|' read -r license_tier license_type license_expiry <<<"$LICENSE_METADATA"
    log "Configured an active PrimeUI ${license_tier}/${license_type} license through ${license_expiry}"
else
    log "Configured the PrimeUI license"
fi

log "Restart Vite or rebuild frontend assets so the updated license is included"

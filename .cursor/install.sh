#!/usr/bin/env bash
# Cloud agent build: PHP 8.5 + compression extensions for laminas-filter PHPUnit.
set -euo pipefail

repo_root="$(cd "$(dirname "$0")/.." && pwd)"

ensure_ondrej_if_needed() {
  if apt-cache show php8.5-cli &>/dev/null 2>&1; then
    return 0
  fi
  sudo apt-get update -qq
  sudo apt-get install -y --no-install-recommends software-properties-common ca-certificates gnupg
  sudo add-apt-repository -y ppa:ondrej/php
  sudo apt-get update -qq
}

if ! php -r 'exit(version_compare(PHP_VERSION, "8.5.0", ">=") ? 0 : 1);' 2>/dev/null; then
  export DEBIAN_FRONTEND=noninteractive
  ensure_ondrej_if_needed
  sudo apt-get install -y --no-install-recommends \
    php8.5-cli \
    php8.5-xml \
    php8.5-mbstring \
    php8.5-bz2 \
    php8.5-zip \
    php8.5-curl \
    php8.5-fileinfo
  if command -v update-alternatives &>/dev/null; then
    sudo update-alternatives --set php /usr/bin/php8.5 2>/dev/null || true
  fi
fi

php -r 'exit(version_compare(PHP_VERSION, "8.5.0", ">=") ? 0 : 1);'

for ext in bz2 zip zlib mbstring fileinfo xml; do
  if ! php -m 2>/dev/null | grep -qi "^${ext}$"; then
    echo "ERROR: PHP extension missing: ${ext}" >&2
    php -m >&2 || true
    exit 1
  fi
done

php -r 'echo "laminas-filter cloud PHP ".PHP_VERSION.PHP_EOL;'

cd "$repo_root"
if [[ -f composer.json ]]; then
  composer install --no-interaction
fi

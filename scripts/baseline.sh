#!/usr/bin/env bash

set -euo pipefail

CURRENT_DIRECTORY="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIRECTORY="$(dirname "$CURRENT_DIRECTORY")"

pushd "$PROJECT_DIRECTORY" >/dev/null

# <editor-fold desc="Install tools">
if [ ! -d "tools" ]; then
    mkdir -p tools
fi

if [ ! -f "tools/phpstan.phar" ]; then
    wget https://github.com/phpstan/phpstan/releases/latest/download/phpstan.phar -O tools/phpstan.phar
    chmod +x tools/phpstan.phar
fi
# </editor-fold>

composer update --no-interaction --no-progress --ansi
composer validate --no-ansi --strict composer.json

php tools/phpstan.phar analyse --memory-limit=512M --ansi --no-progress --error-format=table --generate-baseline=phpstan-baseline.neon

popd >/dev/null

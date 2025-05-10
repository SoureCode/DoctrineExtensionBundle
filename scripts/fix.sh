#!/usr/bin/env bash

set -euo pipefail

CURRENT_DIRECTORY="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIRECTORY="$(dirname "$CURRENT_DIRECTORY")"

pushd "$PROJECT_DIRECTORY" >/dev/null

# <editor-fold desc="Install tools">
if [ ! -d "tools" ]; then
    mkdir -p tools
fi

if [ ! -f "tools/composer-normalize.phar" ]; then
    wget https://github.com/ergebnis/composer-normalize/releases/latest/download/composer-normalize.phar -O tools/composer-normalize.phar
    chmod +x tools/composer-normalize.phar
fi

if [ ! -f "tools/php-cs-fixer.phar" ]; then
    wget https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/releases/latest/download/php-cs-fixer.phar -O tools/php-cs-fixer.phar
    chmod +x tools/php-cs-fixer.phar
fi
# </editor-fold>

if [ ! -d "vendor" ]; then
    composer update --no-interaction --no-progress --ansi
fi

php tools/composer-normalize.phar
PHP_CS_FIXER_IGNORE_ENV=1 php tools/php-cs-fixer.phar fix --show-progress=dots --using-cache=no --verbose

popd >/dev/null

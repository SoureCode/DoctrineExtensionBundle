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

if [ ! -f "tools/composer-require-checker.phar" ]; then
    wget https://github.com/maglnet/ComposerRequireChecker/releases/latest/download/composer-require-checker.phar -O tools/composer-require-checker.phar
    chmod +x tools/composer-require-checker.phar
fi

if [ ! -f "tools/php-cs-fixer.phar" ]; then
    wget https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/releases/latest/download/php-cs-fixer.phar -O tools/php-cs-fixer.phar
    chmod +x tools/php-cs-fixer.phar
fi

if [ ! -f "tools/phpstan.phar" ]; then
    wget https://github.com/phpstan/phpstan/releases/latest/download/phpstan.phar -O tools/phpstan.phar
    chmod +x tools/phpstan.phar
fi

if [ ! -f "tools/infection.phar" ]; then
    wget https://github.com/infection/infection/releases/latest/download/infection.phar -O tools/infection.phar
    chmod +x tools/infection.phar
fi
# </editor-fold>

if [ ! -d "vendor" ]; then
    composer update --no-interaction --no-progress --ansi
fi

composer validate --no-ansi --strict composer.json

php tools/composer-normalize.phar --dry-run
php tools/composer-require-checker.phar check
PHP_CS_FIXER_IGNORE_ENV=1 php tools/php-cs-fixer.phar fix --dry-run --show-progress=dots --using-cache=no --verbose
php tools/phpstan.phar analyse --memory-limit=512M --ansi --no-progress --error-format=table
vendor/bin/phpunit
php tools/infection.phar --min-msi=64 --min-covered-msi=70 --threads=4

popd >/dev/null

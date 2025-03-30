#!/usr/bin/env bash

set -euo pipefail

CURRENT_DIRECTORY="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIRECTORY="$(dirname "$CURRENT_DIRECTORY")"

TEMP_DIRECTORY="$(mktemp -d)"
trap 'sudo rm -rf "$TEMP_DIRECTORY"' EXIT

pushd "$PROJECT_DIRECTORY" > /dev/null

composer run-script profile

cp -r "$PROJECT_DIRECTORY/.phpbench/xdebug-profile/"* "$TEMP_DIRECTORY"

# rename all files from "<id>.cachegrind.gz" to "cachegrind.out.<id>.gz"
for FILE in "$TEMP_DIRECTORY"/*.cachegrind.gz; do
    ID="${FILE##*/}"
    ID="${ID%.cachegrind.gz}"
    ID="${ID##*.}"

    echo "Renaming $FILE to cachegrind.out.$ID.gz"
    mv "$FILE" "$TEMP_DIRECTORY/cachegrind.out.$ID.gz"
done

# change permissions to all files to 33:33 for docker container
echo "$TEMP_DIRECTORY"
ls -lar "$TEMP_DIRECTORY"
sudo chown -R 33:33 "$TEMP_DIRECTORY"
docker run --rm -v "$TEMP_DIRECTORY:/tmp" -p 80:80 jokkedk/webgrind:latest

popd > /dev/null
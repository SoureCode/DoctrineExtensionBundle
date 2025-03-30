#!/usr/bin/env bash

set -euo pipefail

CURRENT_DIRECTORY="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIRECTORY="$(dirname "${CURRENT_DIRECTORY}")"

#<editor-fold desc="main">
pushd "${PROJECT_DIRECTORY}" >/dev/null 2>&1

if [ -d "${PROJECT_DIRECTORY:?}/scripts/public-common" ]; then
    bash "${PROJECT_DIRECTORY:?}/scripts/public-common/update-template.sh"
else
    git clone --depth 1 -b "master" "git@github.com:SoureCode/public-common-script-template.git" "scripts/public-common"
    bash "${PROJECT_DIRECTORY:?}/scripts/public-common/___cleanup.sh"
fi

popd >/dev/null 2>&1
#</editor-fold>

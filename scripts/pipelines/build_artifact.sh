#!/usr/bin/env bash

set -ev

export PATH=${COMPOSER_BIN}:${PATH}

# Generate artifact in a separate directory.
export NEW_BUILD_DIR=/tmp/artifact
blt deploy:build -D deploy.dir=${NEW_BUILD_DIR} -D disable-targets.source.build.frontend=TRUE --ansi
# Move git history to artifact directory. Required for pipelines to commit and push.
mv ${SOURCE_DIR}/.git ${NEW_BUILD_DIR}/
# Allow dotfiles (hidden) to be globbed (via *).
shopt -s dotglob
# Remove all files, including hidden files, from working directory.
rm -rf ${SOURCE_DIR}/*
# Move all files, including hidden files, from build artifact into working directory.
mv ${NEW_BUILD_DIR}/* ${SOURCE_DIR}/

set +v

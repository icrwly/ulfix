#!/usr/bin/env bash

set -ev

blt deploy --commit-msg "Automated commit by Acquia Pipelines for Build ${PIPELINE_JOB_ID}" --branch "${PIPELINES_BRANCH}-build" --ignore-dirty --no-interaction -v

set +v

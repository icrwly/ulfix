#!/usr/bin/env bash

set -ev

blt deploy --commit-msg "Automated commit by Acquia Pipelines for Build ${PIPELINE_JOB_ID}" --tag "${PIPELINE_DEPLOY_VCS_PATH}-build" --ignore-dirty -v

set +v

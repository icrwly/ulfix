#!/bin/bash

# Usage
# ./deploy-sequence.sh <site-name or uuid>

# Exit on error
set -e

SITE=$1
DEV=$(echo "${SITE}.dev")
TEST=$(echo "${SITE}.test")
LIVE=$(echo "${SITE}.live")
START=$SECONDS

echo -e "Starting ${SITE}";

# Run drush updates on dev, clear cache
# terminus drush "${1}.dev" -- updb -y
# terminus env:clear-cache "${1}.dev"

# Deploy code to live
terminus env:deploy $TEST --cc -n -q

# Report time to results.
DURATION=$(( SECONDS - START ))
TIME_DIFF=$(bc <<< "scale=2; $DURATION / 60")
MIN=$(printf "%.2f" $TIME_DIFF)
echo -e "Finished ${SITE} in ${MIN} minutes"

# Run config:import
function drush_cim {
echo "Importing drupal configuration..."
  retries=3
  while [[ $retries -gt 0 ]]; do
    terminus -n drush $TEST -- config:import -y 2>&1
    if [ "$?" = "0" ]; then
      break
    else
      echo "::warning:: drush config:import command failed - retrying in 30 seconds."
      sleep 30
      retries=$((retries-1))
    fi
  done
}
drush_cim

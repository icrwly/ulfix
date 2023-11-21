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

# Deploy code to test
terminus env:deploy $TEST --cc --updatedb -n -q
#terminus env:deploy $LIVE --cc --updatedb -n -q

# Report time to results.
DURATION=$(( SECONDS - START ))
TIME_DIFF=$(bc <<< "scale=2; $DURATION / 60")
MIN=$(printf "%.2f" $TIME_DIFF)
echo -e "Finished ${SITE} in ${MIN} minutes"

#!/bin/bash

# Fail if there is an error.
set -e

# Setup nvm.
. ~/.nvm/nvm.sh
nvm install
nvm use

# Get list of all custom install profiles.
customProfiles=($1/docroot/profiles/custom/*/)
# Loop through each custom profile
for install_profile in "${customProfiles[@]}"
do
  install_profile="$(basename $install_profile)"
  # check if it is not default or g
  if [ "$install_profile" != "default" ] && [ "$install_profile" != "g" ] && [ "$install_profile" != "settings" ]; then
    # Get theme name.
    theme_name="${install_profile//profile/theme}"
    echo "Set up $theme_name ..."
    theme_path="$1/docroot/profiles/custom/$install_profile/themes/custom/$theme_name"
    cd $theme_path
    npm install
    npm run-script build
  fi
done

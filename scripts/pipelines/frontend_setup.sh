#!/usr/bin/env bash

# This script gets executed during the pipelines build.
# Note node and npm should match values in .nvmrc and Dockerfile.

set -ev

export PATH=${COMPOSER_BIN}:${PATH}

# Set SOURCE_DIR to the root of your Drupal project
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
SOURCE_DIR="$(realpath "$SCRIPT_DIR/../../")"

# Install nvm if not already installed
if ! command -v nvm &> /dev/null; then
  # Install nvm
  curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
fi

# Load nvm and use Node.js version
export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
nvm install 18
nvm use 18

# Update npm itself
npm install -g npm@9.7.2

# Install gulp-cli globally
npm install -g gulp-cli

# Get list of all custom install profiles.
customProfiles=($SOURCE_DIR/docroot/profiles/custom/*/)

# echo "===> Setup PATH of libz.so.1 (1.2.11) for mozjpeg package"
export PATH="$PATH:$SOURCE_DIR/docroot/libraries/lib"
export LD_LIBRARY_PATH="$LD_LIBRARY_PATH::$SOURCE_DIR/docroot/libraries/lib"

# Loop through each custom profile
for install_profile in "${customProfiles[@]}"
do
  directory_name="$(dirname $install_profile)"
  install_profile="$(basename $install_profile)"
  echo "Setup Profile $install_profile located in $directory_name."
  # check if it is not default or g
  if [ "$install_profile" != "default" ] && [ "$install_profile" != "g" ]; then
    # Calculate the theme name.
    theme_name="${install_profile//profile/theme}"
    theme_directory="$directory_name/$install_profile/themes/custom/$theme_name"
    # Check if there is a package.json file
    if [ -f $theme_directory/package.json ]; then
      cd $theme_directory
      # Check if node_modules exists, if so delete.
      if [ -d "$theme_directory/node_modules" ]; then
        # Remove old node_modules directory
        echo "Remove old 'node_modules' directory for $theme_name in $theme_directory"
        rm -rf $theme_directory/node_modules
      fi
      # Check if build exists, if so delete.
      if [ -d "$theme_directory/build" ]; then
        # Remove old build directory
        echo "Remove old 'build' directory for $theme_name in $theme_directory"
        rm -rf $theme_directory/build
      fi
      # run npm install
      npm install

      if [ -f node_modules/gulp/bin/gulp.js ]; then
        echo "Running gulp build for $theme_name in $theme_directory"
        # run gulp build task
        node_modules/gulp/bin/gulp.js build
      else
        echo "Unable to find gulp.js for $theme_name in $theme_directory"
      fi
      # move back up to source
      cd $SOURCE_DIR
    else
      echo "package.json not found for $theme_name in $theme_directory"
    fi
  fi
done

set +v

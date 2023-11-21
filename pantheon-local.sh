#!/bin/bash

# Get UL Site

if [ $# -eq 0 ]; then
  echo "Usage: $0 <source>"
  exit 1
fi

source="$1"

# Install Composer dependencies

composer install

# Run your front-end build script

sh scripts/pipelines/frontend_setup.sh

#Pull DB and files from Pantheon

ddev pull $source

# Start DDEV

ddev start


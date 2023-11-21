#!/usr/bin/env bash

set -ev

#  The local.hostname must be set to 127.0.0.1:8888 because we are using drush runserver to test the site.
yaml-cli update:value blt/project.yml project.local.hostname '127.0.0.1:8888'

blt validate
${BLT_DIR}/scripts/travis/tick-tock.sh blt setup --define drush.alias='${drush.aliases.ci}' --define environment=ci --no-interaction --yes -v

# Get list of all custom install profiles.
customProfiles=(docroot/profiles/custom/*/)
# Loop through each custom profile
for install_profile in "${customProfiles[@]}"
do
  install_profile="$(basename $install_profile)"
  # check if it is not default or g
  if [ "$install_profile" != "default" ] && [ "$install_profile" != "g" ]; then
    # Calculate behat tag based on profile name.
    test_tag="${install_profile//_/}"
    test_tag="${test_tag//profile/}"
    # Install site with profile
    blt setup:drupal:install -Dproject.profile.name=${install_profile} --define drush.alias='${drush.aliases.ci}' --define environment=ci --no-interaction --yes -v
    # Run behat tests for profile tag
    blt tests:behat --define drush.alias='${drush.aliases.ci}' --define behat.tags="@${test_tag}" --define tests.run-server=true --define behat.web-driver=chrome --yes -v --ansi
  fi
done

set +v

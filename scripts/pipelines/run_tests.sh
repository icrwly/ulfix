#!/usr/bin/env bash

set -ev

export PATH=${COMPOSER_BIN}:${PATH}

update_settings_file() {
  profile_name=$1

  echo "\$dir = dirname(DRUPAL_ROOT);" >> $SOURCE_DIR/docroot/sites/default/settings.php
  echo "\$settings['config_sync_directory'] = \$dir . \"/config/${profile_name}/default\";" >> $SOURCE_DIR/docroot/sites/default/settings.php
}

backup_settings_file() {
  chmod -R ug+w $SOURCE_DIR/docroot/sites/default/

  if [[ -f "$SOURCE_DIR/docroot/sites/default/settings.php.bak" ]]; then
    rm $SOURCE_DIR/docroot/sites/default/settings.php.bak
  fi
  cp $SOURCE_DIR/docroot/sites/default/settings.php $SOURCE_DIR/docroot/sites/default/settings.php.bak
}

cleanup_test_environment() {
  # restore the settings.php file.
  if [[ -f "$SOURCE_DIR/docroot/sites/default/settings.php.bak" ]]; then
    chmod -R ug+w $SOURCE_DIR/docroot/sites/default/
    rm $SOURCE_DIR/docroot/sites/default/settings.php
    cp $SOURCE_DIR/docroot/sites/default/settings.php.bak $SOURCE_DIR/docroot/sites/default/settings.php
    rm $SOURCE_DIR/docroot/sites/default/settings.php.bak
  fi
}

# backup_settings_file

# Get list of all custom install profiles.
customProfiles=($SOURCE_DIR/docroot/profiles/custom/*/)
# Loop through each custom profile
for install_profile in "${customProfiles[@]}"
do
  install_profile="$(basename $install_profile)"
  # check if it is not default or g
  if [ "$install_profile" != "default" ] && [ "$install_profile" != "g" ] && [ "$install_profile" != "ul_base_profile" ]; then
    # Calculate behat tag based on profile name.
    test_tag="${install_profile//_/}"
    test_tag="${test_tag//profile/}"

    cleanup_test_environment
    backup_settings_file
    update_settings_file $install_profile

    echo $install_profile

    # Install site with profile
    blt setup -D project.profile.name=${install_profile} -D setup.strategy=install --define disable-targets.source.build.frontend=TRUE --define environment=ci --no-interaction -v
    # Run behat tests for profile tag
    blt tests:behat --define drush.alias='${drush.aliases.ci}' --define behat.tags="@${test_tag}" --define tests.run-server=true --define behat.web-driver=chrome -v --ansi
  fi
done

cleanup_test_environment

set +v

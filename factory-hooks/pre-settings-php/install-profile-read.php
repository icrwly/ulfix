<?php
/**
 * @file
 *
 * Read the value of the currently installed profile from the settings.php file.
 * This is useful if you want to switch code in a post-settings-php hook
 * based on install profile.
 *
 * This pre-settings-php hook must file after the BLT include, as it relies on
 * BLT variables.
 *
 * There is currently an item in the Site Factory backlog to do this cleaner
 * (PF-1213), after which has landed, this hook can likely be removed.
 */

use Acquia\Blt\Robo\Common\EnvironmentDetector;

if (EnvironmentDetector::isAcsfEnv()) {
  $ah_group = EnvironmentDetector::getAhGroup();
  $ah_env = EnvironmentDetector::getAhEnv();
  // Set the default install profile.
  $settings['install_profile'] = 'ul_base_profile';

  // Example path:
  // - /var/www/html/baxteracsf.01test/docroot/sites/g/files/ebysai746/settings.php
  $settings_file = "/var/www/html/$ah_group.$ah_env/docroot/sites/g/files/$acsf_db_name/settings.php";

  if (file_exists($settings_file) && is_readable($settings_file)) {
    // Get the contents as a string (do not execute PHP). Contents looks
    // something like:
    //
    // require('/var/www/html/baxteracsf.01live/docroot/sites/g/settings.php');$settings['install_profile'] = 'lightning';
    $contents = file_get_contents($settings_file);
    if (preg_match("#'install_profile'\] = '([^']+)';#", $contents, $matches)) {
      $settings['install_profile'] = $matches[1];
    }
  }

  // Check for the case where a site install is occurring. This needs to be
  // covered since the above settings.php file is not written until after
  // a site install completes.
  if (class_exists('\Drush\Drush') && \Drush\Drush::hasContainer()) {
    try {
      $executed_command = \Drush\Drush::input()->getArgument('command');
    }
    catch (InvalidArgumentException $e) {
      $executed_command = FALSE;
    }
    if ($executed_command == 'site-install' || $executed_command == 'site:install') {
      $profile = \Drush\Drush::input()->getArgument('profile');
      if (is_array($profile)) {
        $profile = $profile[0];
      }
      $is_installing = TRUE;
    }
  }
}

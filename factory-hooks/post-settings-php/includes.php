<?php

/**
 * @file
 * ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/extend/hooks/settings-php/
 *
 * phpcs:disable DrupalPractice.CodeAnalysis.VariableAnalysis
 */

use Acquia\Blt\Robo\Common\EnvironmentDetector;

// Set config directories to default location.
$config_directories['vcs'] = '../config/default';
$config_directories['sync'] = '../config/default';
$settings['config_sync_directory'] = '../config/default';

$dir = dirname(DRUPAL_ROOT);

// Location of configuration for site profiles.
$config_directories['ul_enterprise_profile'] = $dir . '/config/ul_enterprise_profile/default';
$config_directories['ul_guidelines_profile'] = $dir . '/config/ul_guidelines_profile/default';

ini_set('session.cookie_samesite', 'Strict');

if (!empty($site_metadata) && !empty($site_metadata['site']['profile'])) {
  // If we have a site's metadata available and that metadata provides the
  // installed site profile, use that information to set the sync directory.
  $config_directories['vcs'] = $config_directories[$site_metadata['site']['profile']];
  $config_directories['sync'] = $config_directories[$site_metadata['site']['profile']];
  $settings['config_sync_directory'] = $config_directories[$site_metadata['site']['profile']];
}
elseif (isset($is_installing) && isset($profile) && $is_installing === TRUE) {
  $config_directories['vcs'] = $config_directories[$profile];
  $config_directories['sync'] = $config_directories[$profile];
  $settings['config_sync_directory'] = $config_directories[$profile];
}

// Repeat this line from config.settings.php since we don't know which profile
// we're loading (as it's not available in settings because ACSF-things...).
// Both $split and $split_filename_prefix is included by config.settings.php by
// blt.settings.php.
if ($split != 'none' && EnvironmentDetector::isAhEnv()) {
  $config["$split_filename_prefix.$split"]['status'] = TRUE;
}

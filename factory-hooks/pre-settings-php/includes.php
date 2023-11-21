<?php

/**
 * @file
 * ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 *
 * phpcs:disable DrupalPractice.CodeAnalysis.VariableAnalysis
 */

// Configure your hash salt here.
// $settings['hash_salt'] = '';.
require DRUPAL_ROOT . '/../vendor/acquia/blt/settings/blt.settings.php';

// Load and set a site's metadata settings.
if (!empty($settings['file_private_path']) && file_exists($settings['file_private_path'] . '/site_metadata.yml')) {
  $site_metadata_file = $settings['file_private_path'] . '/site_metadata.yml';
  $site_metadata = \Symfony\Component\Yaml\Yaml::parseFile($site_metadata_file);
}

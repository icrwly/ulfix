<?php

/**
 * @file
 * ACSF post-settings-php hook to ensure unique hash_salt for each site.
 *
 * @see https://support.acquia.com/hc/en-us/articles/360054634014-How-to-set-a-unique-hash-salt-for-an-individual-site-in-Site-Factory
 *
 * phpcs:disable DrupalPractice.CodeAnalysis.VariableAnalysis
 */

$settings['hash_salt'] = hash('sha256', $site_settings['site'] . $site_settings['env'] . $site_settings['conf']['acsf_db_name']);

<?php

/**
 * @file
 * Specifies configuration settings for the Shield module.
 *
 * This relies on settings set in secrets.settings.php. That file can be found
 * in /mnt/files/[ah_site_group].[ah_site_env]/secrets.settings.php.
 *
 * Note: The secrets file is automatically loaded by BLT and does not need to
 * happen here. (@see blt.settings.php)
 */

use Acquia\Blt\Robo\Common\EnvironmentDetector;

// Force disable shield in local environments.
if (EnvironmentDetector::isLocalEnv()) {
  $config['shield.settings']['credentials']['shield']['user'] = NULL;
}

// Force enable shield on development and stage environments.
elseif (EnvironmentDetector::isAhEnv()) {
  switch (EnvironmentDetector::getAhEnv()) {
    case '01dev':
    case '01test':
    case '01sbox':
      if (isset($settings['shield_username']) && isset($settings['shield_password'])) {
        $config['shield.settings']['shield_enable'] = TRUE;
        $config['shield.settings']['allow_cli'] = TRUE;
        $config['shield.settings']['credential_provider'] = 'shield';
        /*
         * Username and password are stored outside of the repository on the acquia
         * server.
         *
         * @see: https://docs.acquia.com/resource/secrets/
         */
        $config['shield.settings']['credentials']['shield']['user'] = $settings['shield_username'];
        $config['shield.settings']['credentials']['shield']['pass'] = $settings['shield_password'];
      }
      break;
  }
}

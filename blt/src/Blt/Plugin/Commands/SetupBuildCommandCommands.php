<?php

namespace Acquia\Blt\Custom\Blt\Plugin\Commands;

use Acquia\Blt\Robo\Commands\Drupal\InstallCommand;

/**
 * Override class for Build Setup command.
 *
 * Needed in order to account for additional ul-config-split strategy since
 * the cm.strategy config value is referenced here.
 *
 * @package Acquia\Blt\Custom\Hooks
 */
class SetupBuildCommandCommands extends InstallCommand {

  /**
   * Installs Drupal and sets correct file/directory permissions.
   *
   * Overridden to account for usage of cm.strategy, which we have added to
   * with the introduction of ul-config-split.
   *
   * @see \Acquia\Blt\Custom\Hooks\ConfigImportHook
   *
   * @hook replace-command drupal:install
   *
   * @interactGenerateSettingsFiles
   *
   * @validateMySqlAvailable
   * @validateDocrootIsPresent
   * @executeInDrupalVm
   *
   * @todo Add a @validateSettingsFilesArePresent
   */
  public function drupalInstall() {
    $commands = ['internal:drupal:install'];
    $strategy = $this->getConfigValue('cm.strategy');
    if (in_array($strategy, ['config-split', 'features', 'ul-config-split'])) {
      $commands[] = 'drupal:config:import';
    }

    $this->invokeCommands($commands);
    $this->setSitePermissions();
  }

}

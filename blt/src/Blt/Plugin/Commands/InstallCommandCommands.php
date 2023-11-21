<?php

namespace Acquia\Blt\Custom\Blt\Plugin\Commands;

use Acquia\Blt\Robo\Common\RandomString;
use Acquia\Blt\Robo\Exceptions\BltException;
use Acquia\Blt\Robo\Commands\Drupal\InstallCommand;

/**
 * Override class for Internal install command.
 *
 * This helps automation of Docksal's workflow.
 *
 * @package Acquia\Blt\Custom\Hooks
 */
class InstallCommandCommands extends InstallCommand {

  /**
   * Installs Drupal and imports configuration.
   *
   * @hook replace-command internal:drupal:install
   *
   * @validateMySqlAvailable
   * @validateDrushConfig
   * @hidden
   *
   * @return \Robo\Result
   *   The `drush site-install` command result.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function drupalInstall() {

    // Generate a random, valid username.
    // @see \Drupal\user\Plugin\Validation\Constraint\UserNameConstraintValidator
    $username = RandomString::string(10, FALSE,
      function ($string) {
        return !preg_match('/[^\x{80}-\x{F7} a-z0-9@+_.\'-]/i', $string);
      },
      'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!#%^&*()_?/.,+=><'
    );

    /** @var \Acquia\Blt\Robo\Tasks\DrushTask $task */
    $task = $this->taskDrush()
      ->drush("site-install")
      ->arg($this->getConfigValue('project.profile.name'))
      ->rawArg("install_configure_form.update_status_module='array(FALSE,FALSE)'")
      ->rawArg("install_configure_form.enable_update_status_module=NULL")
      ->option('sites-subdir', $this->getConfigValue('site'))
      ->option('site-name', $this->getConfigValue('project.human_name'))
      ->option('site-mail', $this->getConfigValue('drupal.account.mail'))
      ->option('account-name', $username, '=')
      ->option('account-mail', $this->getConfigValue('drupal.account.mail'))
      ->option('locale', $this->getConfigValue('drupal.locale'))
      ->verbose(TRUE)
      ->printOutput(TRUE);

    // Docksal-specific modification.
    if (file_exists('/home/docker/.docksalrc')) {
      // Assume we're in Docksal and tell the Drush install task to assume yes.
      // This prevents the init script from stalling.
      $task->option('yes');
    }

    $result = $task->interactive($this->input()->isInteractive())->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Failed to install Drupal!");
    }

    return $result;
  }

}

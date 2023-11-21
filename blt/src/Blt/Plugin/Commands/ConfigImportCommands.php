<?php

namespace Acquia\Blt\Custom\Blt\Plugin\Commands;

use Robo\Collection\CollectionBuilder;
use Acquia\Blt\Robo\Exceptions\BltException;
use Acquia\Blt\Robo\Commands\Drupal\ConfigCommand;

/**
 * Override class for BLT config import command.
 *
 * This is needed in order to provide an additional config import strategy.
 * Specifically, the strategy needed to import any config directory through
 * config filters. The regular import/export commands will only affect the sync
 * directory.
 *
 * @see https://www.drupal.org/project/config_filter/issues/2953769
 *
 * @package Acquia\Blt\Custom\Hooks
 */
class ConfigImportCommands extends ConfigCommand {

  /**
   * Imports configuration from the config directory according to cm.strategy.
   *
   * @hook replace-command drupal:config:import
   */
  public function import() {
    $strategy = $this->getConfigValue('cm.strategy');
    $cm_core_key = $this->getConfigValue('cm.core.key');
    $this->logConfig($this->getConfigValue('cm'), 'cm');

    if ($strategy != 'none') {
      $this->invokeHook('pre-config-import');

      // If using core-only or config-split strategies, first check to see if
      // required config is exported.
      if (in_array($strategy, [
        'core-only',
        'config-split',
        'ul-config-split',
      ])) {
        $core_config_file = $this->getConfigValue('docroot') . '/' . $this->getConfigValue("cm.core.dirs.$cm_core_key.path") . '/core.extension.yml';

        if (!file_exists($core_config_file)) {
          $this->logger->warning("BLT will NOT import configuration, $core_config_file was not found.");
          // This is not considered a failure.
          return 0;
        }
      }

      $task = $this->taskDrush()
        ->stopOnFail()
        // Sometimes drush forgets where to find its aliases.
        ->drush("cc")->arg('drush')
        // Rebuild caches in case service definitions have changed.
        // @see https://www.drupal.org/node/2826466
        ->drush("cache-rebuild")
        // Execute db updates.
        // This must happen before features are imported or configuration is
        // imported. For instance, if you add a dependency on a new extension to
        // an existing configuration file, you must enable that extension via an
        // update hook before attempting to import the configuration.
        // If a db update relies on updated configuration, you should import the
        // necessary configuration file(s) as part of the db update.
        ->drush("updb");

      // If exported site UUID does not match site active site UUID, set active
      // to equal exported.
      // @see https://www.drupal.org/project/drupal/issues/1613424
      $exported_site_uuid = $this->getExportedSiteUuid($cm_core_key);

      switch ($strategy) {
        case 'core-only':
          $this->importCoreOnly($task, $cm_core_key);
          break;

        case 'config-split':
        case 'ul-config-split':
          $this->importConfigSplit($task, $cm_core_key);
          break;

        case 'features':
          // $this->importFeatures($task, $cm_core_key);
          break;
      }

      $task->drush("cache-rebuild");
      $result = $task->run();
      if (!$result->wasSuccessful()) {
        throw new BltException("Failed to import configuration!");
      }

      $this->checkConfigOverrides($cm_core_key);

      $result = $this->invokeHook('post-config-import');

      return $result;
    }
  }

  /**
   * Import configuration using config_split and ul_base_config module.
   *
   * @param \Robo\Collection\CollectionBuilder $task
   *   Drush task to add on to.
   * @param string $cm_core_key
   *   The config directory key to import.
   */
  protected function importUlConfigSplit(CollectionBuilder $task, $cm_core_key) {
    /** @var \Acquia\Blt\Robo\Tasks\DrushTask $task */
    $task->drush('pm-enable')->args('config_split', 'ul_base_config');
    $task->drush('ul-import-config')->arg($cm_core_key);
  }

  /**
   * Checks whether core config is overridden.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function checkConfigOverrides():void {
    if (!$this->getConfigValue('cm.allow-overrides') && !$this->getInspector()->isActiveConfigIdentical()) {
      $task = $this->taskDrush()
        ->stopOnFail()
        ->drush("config-status");
      $result = $task->run();
      if (!$result->wasSuccessful()) {
        throw new BltException("Unable to determine configuration status.");
      }
      throw new BltException("Configuration in the database does not match configuration on disk. This indicates that your configuration on disk needs attention. Please read https://support.acquia.com/hc/en-us/articles/360034687394--Configuration-in-the-database-does-not-match-configuration-on-disk-when-using-BLT");
    }
  }

}

<?php

namespace Drupal\ul_base_config\Commands;

use Drush\Drupal\Commands\config\ConfigCommands;
use Drush\Drupal\Commands\config\ConfigImportCommands;
use Drupal\ul_base_config\Config\StorageComparer;
use Drupal\config\StorageReplaceDataWrapper;
use Drupal\Core\Config\BootstrapConfigStorageFactory;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class UlBaseConfigImportCommands extends ConfigImportCommands {

  /**
   * Import config from a config directory.
   *
   * @param string $label
   *   A config directory label (i.e. a key in $config_directories array in
   *   settings.php). Defaults to 'sync'.
   * @param array $options
   *   An associative array of options whose values come from cli, aliases,
   *   config, etc.
   *
   * @option preview
   *   Format for displaying proposed changes. Recognized values: list, diff.
   *   Defaults to list.
   * @option source
   *   An arbitrary directory that holds the configuration files. An
   *   alternative to label argument
   * @option partial
   *   Allows for partial config imports from the source directory. Only
   *   updates and new configs will be processed with this flag (missing
   *   configs will not be deleted).
   * @usage drush ul-import-config --partial
   *   Import configuration; do not remove missing configuration.
   *
   * @command ul:import-config
   * @aliases ulimc,ul-import-config
   */
  public function importConfig($label = NULL, array $options = [
    'preview' => 'list',
    'source' => self::REQ,
    'partial' => FALSE,
    'diff' => FALSE,
  ]) {

    if (!$label) {
      if (\Drupal::hasContainer()) {
        $profile = \Drupal::installProfile();
      }
      else {
        $profile = BootstrapConfigStorageFactory::getDatabaseStorage()
          ->read('core.extension')['profile'];
      }

      if ($profile) {
        $label = $profile;
      }
    }

    // Get config storage service, generated from UlBaseConfigServiceProvider.
    try {
      $source_storage = \Drupal::service("config.storage.$label");
    }
    catch (\Exception $e) {
      $this->logger()->error("No config storage could be found for label '$label'.");
      return;
    }

    // Determine $source_storage in partial case.
    $active_storage = $this->getConfigStorage();
    if ($options['partial']) {
      $replacement_storage = new StorageReplaceDataWrapper($active_storage);
      foreach ($source_storage->listAll() as $name) {
        $data = $source_storage->read($name);
        $replacement_storage->replaceData($name, $data);
      }
      $source_storage = $replacement_storage;
    }

    $config_manager = $this->getConfigManager();
    $storage_comparer = new StorageComparer($source_storage, $active_storage, $config_manager);

    if (!$storage_comparer->createChangelist()->hasChanges()) {
      $this->logger()->notice(('There are no changes to import.'));
      return;
    }

    if ($options['preview'] == 'list' && !$options['diff']) {
      $change_list = [];
      foreach ($storage_comparer->getAllCollectionNames() as $collection) {
        $change_list[$collection] = $storage_comparer->getChangelist(NULL, $collection);
      }
      $table = ConfigCommands::configChangesTable($change_list, $this->output());
      $table->render();
    }
    else {
      $output = ConfigCommands::getDiff($active_storage, $source_storage, $this->output());

      $this->output()->writeln(implode("\n", $output));
    }

    if ($this->io()->confirm(dt('Import the listed configuration changes?'))) {
      return drush_op([$this, 'doImport'], $storage_comparer);
    }
  }

}

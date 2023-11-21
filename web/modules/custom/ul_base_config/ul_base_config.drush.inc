<?php

/**
 * @file
 * Custom drush commands for importing and exporting config.
 */

use Drupal\config\StorageReplaceDataWrapper;
use Drupal\ul_base_config\Config\StorageComparer;
use Drupal\ul_base_config\UlConfigStorageFactory;
use Drupal\Core\Config\ConfigImporter;
use Drupal\Core\Config\ConfigException;
use Drupal\Core\Config\BootstrapConfigStorageFactory;
use Drupal\Core\Site\Settings;
use Symfony\Component\Filesystem\Filesystem;
use Drush\Drush;

/**
 * Implements hook_drush_command().
 */
function ul_base_config_drush_command() {
  $items = [];
  $items['ul-import-config'] = [
    'description' => 'Import config from a config directory.',
    'arguments' => [
      'label' => "A config directory label (i.e. a key in \$config_directories array in settings.php). Defaults to the name of the install profile",
    ],
    'options' => [
      'preview' => [
        'description' => 'Format for displaying proposed changes. Recognized values: list, diff. Defaults to list.',
        'example-value' => 'list',
      ],
      'source' => [
        'description' => 'An arbitrary directory that holds the configuration files. An alternative to label argument',
      ],
      'partial' => [
        'description' => 'Allows for partial config imports from the source directory. Only updates and new configs will be processed with this flag (missing configs will not be deleted).',
      ],
    ],
    'core' => ['8+'],
    'examples' => [
      'drush ul-import-config --partial' => 'Import configuration; do not remove missing configuration.',
    ],
    'aliases' => ['bimc'],
  ];
  $items['ul-export-config'] = [
    'description' => 'Export configuration to a directory.',
    'core' => ['8+'],
    'aliases' => ['bexc'],
    'arguments' => [
      'label' => "A config directory label (i.e. a key in \$config_directories array in settings.php). Defaults to 'sync'",
    ],
    'options' => [
      'add' => 'Run `git add -p` after exporting. This lets you choose which config changes to sync for commit.',
      'commit' => 'Run `git add -A` and `git commit` after exporting.  This commits everything that was exported without prompting.',
      'message' => 'Commit comment for the exported configuration.  Optional; may only be used with --commit or --push.',
      'push' => 'Run `git push` after committing.  Implies --commit.',
      'remote' => [
        'description' => 'The remote git branch to use to push changes.  Defaults to "origin".',
        'example-value' => 'origin',
      ],
      'branch' => [
        'description' => 'Make commit on provided working branch. Ignored if used without --commit or --push.',
        'example-value' => 'branchname',
      ],
      'destination' => 'An arbitrary directory that should receive the exported files. An alternative to label argument.',
    ],
    'examples' => [
      'drush ul-export-config --destination' => 'Export configuration; Save files in a backup directory named ul-export-config.',
    ],
  ];
  return $items;
}

/**
 * Command callback. Import from specified config directory (defaults to sync).
 */
function drush_ul_base_config_ul_import_config($source = NULL) {
  // Global $config_directories;.
  /** @var \Drupal\config_filter\ConfigFilterStorageCollector $storage_collector */
  $storage_collector = \Drupal::service('config_filter.storage_collector');
  /** @var \Drupal\config_filter\Config\FilteredStorage[] $storage_collection */
  $storage_collection = $storage_collector->getStorages();

  if (!$source) {
    if (\Drupal::hasContainer()) {
      $profile = \Drupal::installProfile();
    }
    else {
      $profile = BootstrapConfigStorageFactory::getDatabaseStorage()
        ->read('core.extension')['profile'];
    }

    if ($profile) {
      $source = $profile;
    }
  }

  $config_prefix = UlConfigStorageFactory::CONFIG_STORAGE_PREFIX;
  $source_storage = NULL;
  foreach ($storage_collection as $storage_id => $storage) {
    if ($storage_id == $config_prefix . $source) {
      $source_storage = $storage;
    }
  }

  // Determine $source_storage in partial and non-partial cases.
  /** @var \Drupal\Core\Config\StorageInterface $active_storage */
  $active_storage = \Drupal::service('config.storage');
  if (Drush::config()->get('partial')) {
    $replacement_storage = new StorageReplaceDataWrapper($active_storage);
    foreach ($source_storage->listAll() as $name) {
      $data = $source_storage->read($name);
      $replacement_storage->replaceData($name, $data);
    }
    $source_storage = $replacement_storage;
  }

  /** @var \Drupal\Core\Config\ConfigManagerInterface $config_manager */
  $config_manager = \Drupal::service('config.manager');
  $storage_comparer = new StorageComparer($source_storage, $active_storage, $config_manager);

  if (!$storage_comparer->createChangelist()->hasChanges()) {
    return \Drupal::logger("ul_base_config")->info(dt('There are no changes to import.'));
  }

  if (Drush::config()->get('preview', 'list') == 'list') {
    $change_list = [];
    foreach ($storage_comparer->getAllCollectionNames() as $collection) {
      $change_list[$collection] = $storage_comparer->getChangelist(NULL, $collection);
    }
    _drush_print_config_changes_table($change_list);
  }

  if (drush_confirm(dt('Import the listed configuration changes?'))) {
    return drush_op('_drush_ul_base_config_ul_import_config', $storage_comparer);
  }
}

/**
 * Copied from submitForm() at /core/modules/config/src/Form/ConfigSync.php.
 */
function _drush_ul_base_config_ul_import_config(StorageComparer $storage_comparer) {
  $config_importer = new ConfigImporter(
    $storage_comparer,
    \Drupal::service('event_dispatcher'),
    \Drupal::service('config.manager'),
    \Drupal::lock(),
    \Drupal::service('config.typed'),
    \Drupal::moduleHandler(),
    \Drupal::service('module_installer'),
    \Drupal::service('theme_handler'),
    \Drupal::service('string_translation')
  );
  if ($config_importer->alreadyImporting()) {
    \Drupal::logger("ul_base_config")->info('Another request may be synchronizing configuration already.');
  }
  else {
    try {
      // This is the contents of \Drupal\Core\Config\ConfigImporter::import.
      // Copied here so we can log progress.
      if ($config_importer->hasUnprocessedConfigurationChanges()) {
        $sync_steps = $config_importer->initialize();
        foreach ($sync_steps as $step) {
          $context = [];
          do {
            $config_importer->doSyncStep($step, $context);
            if (isset($context['message'])) {
              \Drupal::logger("ul_base_config")->info(str_replace('Synchronizing', 'Synchronized', (string) $context['message']));
            }
          } while ($context['finished'] < 1);
        }
      }
      if ($config_importer->getErrors()) {
        throw new ConfigException('Errors occurred during import');
      }
      else {
        \Drupal::logger("ul_base_config")->info('The configuration was imported successfully.');
      }
    }
    catch (ConfigException $e) {
      // Return a negative result for UI purposes. We do not differentiate
      // between an actual synchronization error and a failed lock, because
      // concurrent synchronizations are an edge-case happening only when
      // multiple developers or site builders attempt to do it without
      // coordinating.
      $message = 'The import failed due for the following reasons:' . "\n";
      $message .= implode("\n", $config_importer->getErrors());

      watchdog_exception('config_import', $e);
      return \Drupal::logger('config_import_fail')->error($message);
    }
  }
}

/**
 * Command callback: Export config to specified directory (usually sync).
 */
function drush_ul_base_config_ul_export_config($destination = NULL) {
  global $config_directories;

  // Determine which target directory to use.
  if ($target = Drush::config()->get('destination')) {
    if ($target === TRUE) {
      // User did not pass a specific value for --destination. Make one.
      /** @var drush_version_control_backup $backup */
      $backup = drush_include_engine('version_control', 'backup');
      $destination_dir = $backup->prepare_backup_dir('ul-export-config');
    }
    else {
      $destination_dir = $target;
      // It is important to be able to specify a destination directory that
      // does not exist yet, for exporting on remote systems.
      $filesystem = new Filesystem();
      $filesystem->mkdir($destination_dir);
    }
  }
  else {
    $choices = drush_map_assoc(array_keys($config_directories));
    unset($choices['active']);
    if (!isset($destination) && count($choices) >= 2) {
      $destination = drush_choice($choices, 'Choose a destination.');
      if (empty($destination)) {
        return drush_user_abort();
      }
    }
    elseif (!isset($destination)) {
      $destination = 'sync';
    }
    $destination_dir = Settings::get('config_sync_directory');
  }

  // Prepare a new branch, if applicable.
  $remote = Drush::config()->get('push', FALSE);
  $original_branch = FALSE;
  $branch = FALSE;
  if ($remote) {
    // Get the branch that we're on at the moment.
    $result = drush_shell_cd_and_exec($destination_dir, 'git rev-parse --abbrev-ref HEAD');
    if (!$result) {
      return \Drupal::logger('config_export_fail')->error(
        'DRUSH_CONFIG_EXPORT_NO_GIT',
        dt("The drush ul-export-config command requires that the selected configuration directory !dir be under git revision control when using --commit or --push options.", ['!dir' => $destination_dir])
      );
    }
    $output = drush_shell_exec_output();
    $original_branch = $output[0];
    $branch = Drush::config()->get('branch', FALSE);
    if (!$branch) {
      $branch = $original_branch;
    }
    if ($branch != $original_branch) {
      // Switch to the working branch; create it if it does not exist.
      // We do NOT want to use -B here, as we do NOT want to reset the
      // branch if it already exists.
      $result = drush_shell_cd_and_exec($destination_dir, 'git checkout %s', $branch);
      if (!$result) {
        $result = drush_shell_cd_and_exec($destination_dir, 'git checkout -b %s', $branch);
      }
    }
  }

  // Do the actual config export operation.
  $result = _drush_ul_base_config_ul_export_config($destination, $destination_dir, $branch);

  // Regardless of the result of the export, reset to our original branch.
  if ($branch != $original_branch) {
    drush_shell_cd_and_exec($destination_dir, 'git checkout %s', $original_branch);
  }

  return $result;
}

/**
 * Helper function for exporting config.
 *
 * @param string $destination
 *   The destination.
 * @param string $destination_dir
 *   The destination directory.
 * @param string $branch
 *   The branch.
 *
 * @return array|bool
 *   Array of destination information.
 */
function _drush_ul_base_config_ul_export_config($destination, $destination_dir, $branch) {
  /** @var \Drupal\config_filter\ConfigFilterStorageCollector $storage_collector */
  $storage_collector = \Drupal::service('config_filter.storage_collector');
  /** @var \Drupal\config_filter\Config\FilteredStorage[] $storage_collection */
  $storage_collection = $storage_collector->getStorages();
  $config_prefix = UlConfigStorageFactory::CONFIG_STORAGE_PREFIX;
  $commit = Drush::config()->get('commit');
  $comment = Drush::config()->get('message', 'Exported configuration.');
  if (count(glob($destination_dir . '/*')) > 0) {
    // Retrieve a list of differences between the active and target
    // configuration (if any).
    $target_storage = NULL;
    foreach ($storage_collection as $storage_id => $storage) {
      if ($storage_id == $config_prefix . $destination) {
        $target_storage = $storage;
      }
    }

    /** @var \Drupal\Core\Config\StorageInterface $active_storage */
    $active_storage = \Drupal::service('config.storage');
    $comparison_source = $active_storage;

    $config_comparer = new StorageComparer($comparison_source, $target_storage, \Drupal::service('config.manager'));
    if (!$config_comparer->createChangelist()->hasChanges()) {
      return \Drupal::logger("ul_base_config")->info(dt('The active configuration is identical to the configuration in the export directory (!target).', ['!target' => $destination_dir]));
    }

    drush_print("Differences of the active config to the export directory:\n");
    $change_list = [];
    foreach ($config_comparer->getAllCollectionNames() as $collection) {
      $change_list[$collection] = $config_comparer->getChangelist(NULL, $collection);
    }
    // Print a table with changes in color, then re-generate again without
    // color to place in the commit comment.
    _drush_print_config_changes_table($change_list);
    $tbl = _drush_format_config_changes_table($change_list);
    $output = $tbl->getTable();
    if (!stristr(PHP_OS, 'WIN')) {
      $output = str_replace("\r\n", PHP_EOL, $output);
    }
    $comment .= "\n\n$output";

    if (!$commit && !drush_confirm(dt('The .yml files in your export directory (!target) will be deleted and replaced with the active config.', ['!target' => $destination_dir]))) {
      return drush_user_abort();
    }
    // Only delete .yml files, and not .htaccess or .git.
    $target_storage->deleteAll();
  }

  // Write all .yml files.
  $source_storage = \Drupal::service('config.storage');
  $destination_storage = NULL;
  foreach ($storage_collection as $storage_id => $storage) {
    if ($storage_id == $config_prefix . $destination) {
      $destination_storage = $storage;
    }
  }

  foreach ($source_storage->listAll() as $name) {
    $destination_storage->write($name, $source_storage->read($name));
  }

  // Export configuration collections.
  foreach (\Drupal::service('config.storage')->getAllCollectionNames() as $collection) {
    $source_storage = $source_storage->createCollection($collection);
    $destination_storage = $destination_storage->createCollection($collection);
    foreach ($source_storage->listAll() as $name) {
      $destination_storage->write($name, $source_storage->read($name));
    }
  }

  \Drupal::logger("ul_base_config")->info(dt('Configuration successfully exported to !target.', ['!target' => $destination_dir]));
  drush_backend_set_result($destination_dir);

  // Commit and push, or add exported configuration if requested.
  $remote = Drush::config()->get('push', FALSE);
  if ($commit || $remote) {
    // There must be changed files at the destination dir; if there are not,
    // then we will skip the commit-and-push step.
    $result = drush_shell_cd_and_exec($destination_dir, 'git status --porcelain .');
    if (!$result) {
      return \Drupal::logger('config_export_fail')->error('DRUSH_CONFIG_EXPORT_FAILURE', dt("`git status` failed."));
    }
    $uncommitted_changes = drush_shell_exec_output();
    if (!empty($uncommitted_changes)) {
      $result = drush_shell_cd_and_exec($destination_dir, 'git add -A .');
      if (!$result) {
        return \Drupal::logger('config_export_fail')->error('DRUSH_CONFIG_EXPORT_FAILURE', dt("`git add -A` failed."));
      }
      $comment_file = drush_save_data_to_temp_file($comment);
      $result = drush_shell_cd_and_exec($destination_dir, 'git commit --file=%s', $comment_file);
      if (!$result) {
        return \Drupal::logger('config_export_fail')->error('DRUSH_CONFIG_EXPORT_FAILURE', dt("`git commit` failed.  Output:\n\n!output", ['!output' => implode("\n", drush_shell_exec_output())]));
      }
      if ($remote) {
        // Remote might be FALSE, if --push was not specified, or
        // it might be TRUE if --push was not given a value.
        if (!is_string($remote)) {
          $remote = 'origin';
        }
        $result = drush_shell_cd_and_exec($destination_dir, 'git push --set-upstream %s %s', $remote, $branch);
        if (!$result) {
          return \Drupal::logger('config_export_fail')->error('DRUSH_CONFIG_EXPORT_FAILURE', dt("`git push` failed."));
        }
      }
    }
  }
  elseif (Drush::config()->get('add')) {
    drush_shell_exec_interactive('git add -p %s', $destination_dir);
  }

  $values = [
    'destination' => $destination_dir,
  ];

  return $values;
}

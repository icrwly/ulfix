<?php

namespace Drupal\ul_base_config\Commands;

use Drush\Drupal\Commands\config\ConfigCommands;
use Drush\Drupal\Commands\config\ConfigExportCommands;
use Drupal\ul_base_config\Config\StorageComparer;
use Symfony\Component\Console\Output\BufferedOutput;
use Drush\Exceptions\UserAbortException;

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
class UlBaseConfigExportCommands extends ConfigExportCommands {

  /**
   * Export configuration to a directory.
   *
   * @param string $label
   *   A config directory label (i.e. a key in $config_directories array
   *   in settings.php). Defaults to 'sync'.
   * @param array $options
   *   An associative array of options whose values come from cli, aliases,
   *   config, etc.
   *
   * @option add
   *   Run `git add -p` after exporting. This lets you choose which config
   *   changes to sync for commit.
   * @option commit
   *   Run `git add -A` and `git commit` after exporting.  This commits
   *   everything that was exported without prompting.
   * @option message
   *   Commit comment for the exported configuration.  Optional; may only be
   *   used with --commit or --push.
   * @option push
   *   Run `git push` after committing.  Implies --commit.
   * @option remote
   *   The remote git branch to use to push changes.  Defaults to "origin".
   * @option branch
   *   Make commit on provided working branch. Ignored if used without
   *   --commit or --push.
   * @option destination
   *   An arbitrary directory that should receive the exported files. An
   *   alternative to label argument.
   * @usage drush ul-export-config --destination
   *   Export configuration; Save files in a backup directory named
   *   ul-export-config.
   *
   * @command ul:export-config
   * @aliases ulexc,ul-export-config
   */
  public function exportConfig($label = NULL, array $options = [
    'add' => FALSE,
    'commit' => FALSE,
    'message' => self::REQ,
    'destination' => self::OPT,
    'diff' => FALSE,
  ]) {
    // Get destination directory.
    $destination_dir = ConfigCommands::getDirectory($label, $options['destination']);

    // Do the actual config export operation.
    $preview = $this->doUlExport($options, $destination_dir, $label);

    // Do the VCS operations.
    $this->doAddCommit($options, $destination_dir, $preview);
  }

  /**
   * Does the export.
   */
  public function doUlExport($options, $destination_dir, $label) {
    try {
      $target_storage = \Drupal::service("config.storage.$label");
    }
    catch (\Exception $e) {
      $this->logger()->error("No config storage could be found for label '$label'.");
      return;
    }

    if (count(glob($destination_dir . '/*')) > 0) {
      // Retrieve a list of differences between the active and target
      // configuration (if any).
      $config_comparer = new StorageComparer($this->getConfigStorage(), $target_storage, $this->getConfigManager());
      if (!$config_comparer->createChangelist()->hasChanges()) {
        $this->logger()->notice(dt('The active configuration is identical to the configuration in the export directory (!target).', ['!target' => $destination_dir]));
        return;
      }
      $this->output()->writeln("Differences of the active config to the export directory:\n");

      if ($options['diff']) {
        $diff = ConfigCommands::getDiff($target_storage, $this->getConfigStorage(), $this->output());
        $this->output()->writeln($diff);
      }
      else {
        $change_list = [];
        foreach ($config_comparer->getAllCollectionNames() as $collection) {
          $change_list[$collection] = $config_comparer->getChangelist(NULL, $collection);
        }
        // Print a table with changes in color, then re-generate again without
        // color to place in the commit comment.
        $bufferedOutput = new BufferedOutput();
        $table = ConfigCommands::configChangesTable($change_list, $bufferedOutput, FALSE);
        $table->render();
        $preview = $bufferedOutput->fetch();
        $table = ConfigCommands::configChangesTable($change_list, $this->output(), TRUE);
        $table->render();
      }

      if (!$this->io()->confirm(dt('The .yml files in your export directory (!target) will be deleted and replaced with the active config.', ['!target' => $destination_dir]))) {
        throw new UserAbortException();
      }
      // Only delete .yml files, and not .htaccess or .git.
      $target_storage->deleteAll();
    }

    // Write all .yml files.
    ConfigCommands::copyConfig($this->getConfigStorage(), $target_storage);

    $this->logger()->success(dt('Configuration successfully exported to !target.', ['!target' => $destination_dir]));
    drush_backend_set_result($destination_dir);
    return isset($preview) ? $preview : 'No existing configuration to diff against.';
  }

}

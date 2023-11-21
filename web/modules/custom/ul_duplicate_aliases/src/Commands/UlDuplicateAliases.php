<?php

namespace Drupal\ul_duplicate_aliases\Commands;

use Drupal\Core\Database\Connection;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;
use Drupal\path_alias\Entity\PathAlias;

/**
 * A Drush commandfile.
 */
class UlDuplicateAliases extends DrushCommands {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The number of path aliases containning duplicate records.
   *
   * @var int
   */
  protected $num_aliases;

  /**
   * The total records of duplicate path aliases.
   *
   * @var int
   */
  protected $total_records;

  /**
   * The number of records deleted from the path_alias table.
   *
   * @var int
   */
  protected $count_removed;

  /**
   * Constructor.
   */
  public function __construct(Connection $connection) {
    $this->database = $connection;
    $this->num_aliases = 0;
    $this->total_records = 0;
    $this->count_removed = 0;
  }

  /**
   * Drush command to remove duplicate path aliases.
   *
   * @command ul_duplicate_aliases:remove
   * @aliases ul-dar
   * @usage ul_duplicate_aliases:remove
   *   Remove duplicate path_aliases.
   */
  public function remove() {
    // Preview the path aliases before removing.
    $this->previewPathAliases();
    // User input to confirm (yes/no).
    if (!$this->io()->confirm(dt('Remove all duplicate path aliases (URL aliases) listed above?'))) {
      throw new UserAbortException();
    }
    // Do the delete operation.
    $this->doRemove(TRUE);
  }

  /**
   * Preview the duplicate path aliases before removing.
   */
  protected function previewPathAliases() {
    // Query records without deletion.
    $this->doRemove(FALSE);

    if ($this->num_aliases <= 0) {
      // Print out the message.
      $this->output()->writeln("\n- There are NO duplicate path aliases.");
      return;
    }
    // Print out the message.
    $this->output()->writeln("\n*** There are '$this->num_aliases' path aliases containing duplicate records (total: $this->total_records records). *** ");
  }

  /**
   * Run the drush command to remve path aliases.
   *
   * @param bool $flag
   *   A flag to delete or not.
   */
  protected function doRemove($flag = FALSE) {
    // SELECT count(*) as subtotal, path, alias, langcode FROM path_alias
    // GROUP BY path, alias, langcode HAVING  subtotal > 1.
    $limit = 1;
    $query = $this->database->select('path_alias', 'pa');
    $query->fields('pa', ['path', 'alias', 'langcode']);
    $query->addExpression('count(*)', 'subtotal');
    $query->having('COUNT(*) > :matches', [':matches' => $limit]);
    $query->groupBy("path, alias, langcode");
    $result = $query->execute();

    while ($record = $result->fetchAssoc()) {
      $this->num_aliases++;
      $this->getDuplicateAliases($record['path'], $record['alias'], $record['langcode'], $flag);
    }

    if ($this->count_removed > 0) {
      $this->output()->writeln("*** Deleted '$this->count_removed' duplicate records from the path_alias table successully. ***\n");
    }
    else {
      $this->output()->writeln("- Nothing deleted from the path_alias table.\n");
    }
  }

  /**
   * Get a list of records(IDs) with the same path, alias and langcode.
   *
   * @param string $path
   *   The path of path_alias.
   * @param string $alias
   *   The alias of path_alias.
   * @param string $langcode
   *   The langcode of path_alias.
   * @param bool $flag
   *   A flag to delete or not.
   */
  protected function getDuplicateAliases($path, $alias, $langcode, $flag = FALSE) {
    // Select all duplicate records for one path.
    $query = $this->database->select('path_alias', 'pa');
    $query->condition('path', $path)
      ->condition('alias', $alias)
      ->condition('langcode', $langcode);
    $query->fields('pa', ['id']);
    $query->orderBy('id');
    // Get the number of records(rows).
    $query2 = $query;
    $num_records = $query2->countQuery()->execute()->fetchField();
    $this->total_records += $num_records;

    $result = $query->execute();
    $i = 1;
    // The last record(id) is the newest path_alias and will be reserved.
    while ($record = $result->fetchAssoc()) {
      if ($i >= $num_records) {
        break;
      }
      elseif ($flag) {
        // Delete one record if $flag is TRUR.
        $this->deletePathAlias($record['id']);
        $i++;
      }
    }
  }

  /**
   * Delete one duplicate path_alias.
   *
   * @param int $id
   *   ID Number of path_alias.
   */
  protected function deletePathAlias($id) {
    if ($path_alias = PathAlias::load($id)) {
      try {
        $path_alias->delete();
        $this->count_removed++;
      }
      catch (\Exception $e) {
        throw $e;
      }
    }
  }

}

<?php

namespace Drupal\ul_base_config\Config;

use Drupal\Core\Config\StorageComparer as CoreStorageComparer;
use Drupal\Core\Config\StorageInterface;

/**
 * Override StorageComparer to control certain aspects of config comparison.
 *
 * Unfortunately this is not a service so we cannot replace it
 * entirely.
 *
 * @see _drush_ul_base_config_ul_export_config()
 * @see _drush_ul_base_config_ul_import_config()
 * @see drush_ul_base_config_ul_import_config()
 */
class StorageComparer extends CoreStorageComparer {

  /**
   * {@inheritdoc}
   */
  public function getAllCollectionNames($include_default = TRUE) {
    $collections = parent::getAllCollectionNames($include_default);
    $ignored_collections_settings = [
      'language.*',
    ];

    $collections = array_filter($collections, function ($collection) use ($ignored_collections_settings) {
      $collection_fill = array_fill(0, count($ignored_collections_settings), $collection);
      $filter_results = array_map('fnmatch', $ignored_collections_settings, $collection_fill);
      return !in_array(TRUE, $filter_results);
    });

    // If somehow the default collection got unset and we're told to include it,
    // this will include it.
    if ($include_default) {
      array_unshift($collections, StorageInterface::DEFAULT_COLLECTION);
    }

    return array_unique($collections);
  }

}

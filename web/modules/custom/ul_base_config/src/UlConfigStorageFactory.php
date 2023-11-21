<?php

namespace Drupal\ul_base_config;

use Drupal\config_filter\ConfigFilterStorageFactory;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\StorageInterface;

/**
 * Factory class for generating FileStorage objects for configuration directory.
 */
class UlConfigStorageFactory {

  /**
   * All file storage aliases are prefixed with the following.
   */
  const CONFIG_STORAGE_PREFIX = 'config.storage.';

  /**
   * All filter storage aliases are prefixed with the following.
   */
  const CONFIG_FILTER_STORAGE_PREFIX = 'config_filter.storage.';

  /**
   * Config filter storage factory service.
   *
   * @var \Drupal\config_filter\ConfigFilterStorageFactory
   */
  private $storageFactory;

  /**
   * UlConfigStorageFactory constructor.
   *
   * @param \Drupal\config_filter\ConfigFilterStorageFactory $storage_factory
   *   Storage factory object.
   */
  public function __construct(ConfigFilterStorageFactory $storage_factory) {
    $this->storageFactory = $storage_factory;
  }

  /**
   * Return file storage for a configuration directory.
   *
   * @param string $config_dir
   *   The path of the config directory.
   *
   * @return \Drupal\Core\Config\FileStorage
   *   FileStorage object representing the directory argument.
   */
  public static function getConfigStorage($config_dir) {
    return new FileStorage($config_dir);
  }

  /**
   * Return filtered storage.
   *
   * @param \Drupal\Core\Config\StorageInterface $config_storage
   *   The storage config object.
   * @param string $storage_name
   *   The storage name.
   *
   * @return \Drupal\config_filter\Config\FilteredStorageInterface
   *   Return filtered storage object.
   */
  public function getFilteredStorage(StorageInterface $config_storage, $storage_name) {
    return $this->storageFactory->getFilteredStorage($config_storage, [$storage_name]);
  }

}

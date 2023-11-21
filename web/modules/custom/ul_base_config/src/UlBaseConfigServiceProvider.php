<?php

namespace Drupal\ul_base_config;

use Drupal\config_filter\Config\FilteredStorage;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds config storage services for every configuration storage directory.
 *
 * @package Drupal\ul_base_config
 */
class UlBaseConfigServiceProvider extends ServiceProviderBase {

  /**
   * Alter method.
   */
  public function alter(ContainerBuilder $container) {
    global $config_directories;
    if (isset($config_directories)) {
      foreach ($config_directories as $config_key => $directory) {
        // Skip if we see the sync key, since config_filter already does that
        // for us.
        if ($config_key == 'sync') {
          continue;
        }
        $storage_alias = UlConfigStorageFactory::CONFIG_STORAGE_PREFIX . $config_key;
        $filter_alias = UlConfigStorageFactory::CONFIG_FILTER_STORAGE_PREFIX . $config_key;
        // Add FileStorage service definition for config directory.
        $container->register($storage_alias, FileStorage::class)
          ->setFactory([
            new Reference('ul.config.storage_factory'), 'getConfigStorage',
          ])
          ->addArgument($directory);
        // Add FilteredStorage service definition for config directory.
        $container->register($filter_alias, FilteredStorage::class)
          ->setFactory([
            new Reference('ul.config.storage_factory'), 'getFilteredStorage',
          ])
          ->setDecoratedService($storage_alias)
          ->setArguments([new Reference("$filter_alias.inner"), $storage_alias])
          ->addTag('config.filter_storage', ['alias' => $storage_alias]);
      }
    }

  }

}

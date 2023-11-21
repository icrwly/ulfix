<?php

namespace Drupal\ul_base_config\Plugin\ConfigFilter;

use Drupal\config_filter\Plugin\ConfigFilterBase;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the ConfigFilter type.
 *
 * @ConfigFilter(
 *   id = "config_acsf",
 *   label = @Translation("Acquia Cloud Site Factory Filter"),
 *   weight = 200
 * )
 */
class Acsf extends ConfigFilterBase implements ContainerFactoryPluginInterface {

  /**
   * The active configuration storage.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $active;

  /**
   * Constructs a new Filter.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\StorageInterface $active
   *   The active configuration store with the configuration on the site.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, StorageInterface $active) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->active = $active;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.storage')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function filterReadMultiple(array $names, array $data) {

    // If active theme is not set them add it back to the config to prevent
    // config import from failing.
    $active_theme = $this->active->read('system.theme');
    if (!isset($data['core.extension']['theme'][$active_theme['default']])) {
      $data['core.extension']['theme'][$active_theme['default']] = 0;
    }

    return $data;
  }

}

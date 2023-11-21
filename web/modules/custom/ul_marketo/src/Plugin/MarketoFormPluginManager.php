<?php

namespace Drupal\ul_marketo\Plugin;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\ul_marketo\UlMarketoServiceInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Plugin type manager for field widgets.
 */
class MarketoFormPluginManager extends DefaultPluginManager {

  /**
   * The marketo service.
   *
   * @var \Drupal\ul_marketo\UlMarketoServiceInterface
   */
  protected $marketo;

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, ConfigFactory $config, UlMarketoServiceInterface $ul_marketo) {
    parent::__construct(
      'Plugin/marketo_form',
      $namespaces,
      $module_handler,
      'Drupal\ul_marketo\Plugin\MarketoFormPluginInterface',
      'Drupal\ul_marketo\Annotation\MarketoForm'
    );
    $this->config = $config;
    $this->marketo = $ul_marketo;
    $this->alterInfo('marketo_form_info');
    $this->setCacheBackend($cache_backend, 'marketo_form_plugins');
  }

  /**
   * Create Marketo Form instance.
   *
   * @param string $plugin_id
   *   The plugin id.
   * @param array $configuration
   *   Configuration array.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity usually node.
   * @param array $passed_settings
   *   Settings passed in from non-entities.
   *
   * @return object
   *   The plugin instance.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function createInstance($plugin_id, array $configuration = [], EntityInterface $entity = NULL, array $passed_settings = []) {

    return parent::createInstance($plugin_id, $configuration);
  }

}

<?php

namespace Drupal\ul_marketo\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\ul_marketo\Plugin\MarketoFormPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Deriver class for marketo form blocks.
 */
class MarketoFormBlockDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The marketo form plugin manager.
   *
   * @var \Drupal\ul_marketo\Plugin\MarketoFormPluginManager
   */
  protected $marketoFormPluginManager;

  /**
   * MarketoFormBlockDeriver constructor.
   *
   * @param \Drupal\ul_marketo\Plugin\MarketoFormPluginManager $marketo_manager
   *   The marketo form plugin manager.
   */
  public function __construct(MarketoFormPluginManager $marketo_manager) {
    $this->marketoFormPluginManager = $marketo_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('plugin.manager.marketo_form')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    // Get all marketo forms that have been flagged as a block.
    $definitions = $this->marketoFormPluginManager->getDefinitions();

    foreach ($definitions as $id => $definition) {
      if (!empty($definition['block'])) {
        $this->derivatives[$id] = $base_plugin_definition;
        $this->derivatives[$id]['marketo_form'] = $definition['id'];
        $this->derivatives[$id]['admin_label'] = $definition['title'];
      }
    }

    return $this->derivatives;
  }

}

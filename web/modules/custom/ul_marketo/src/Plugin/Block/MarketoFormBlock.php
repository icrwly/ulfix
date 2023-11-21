<?php

namespace Drupal\ul_marketo\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\ul_marketo\UlMarketoServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Marketo Form' Block.
 *
 * @Block(
 *   id = "marketo_form_block",
 *   admin_label = @Translation("Marketo Form"),
 *   category = @Translation("Marketo"),
 *   deriver = "Drupal\ul_marketo\Plugin\Derivative\MarketoFormBlockDeriver"
 * )
 */
class MarketoFormBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * MarketoForm plugin manager.
   *
   * @var \Drupal\ul_marketo\Plugin\MarketoFormPluginManager
   */
  protected $marketoManager;

  /**
   * Marketo service.
   *
   * @var \Drupal\ul_marketo\UlMarketoServiceInterface
   */
  protected $marketo;

  /**
   * Current Route Match service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * MarketoFormBlock constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\ul_marketo\UlMarketoServiceInterface $ul_marketo
   *   The Marketo service.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $current_route_match
   *   The Current Route Match service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, UlMarketoServiceInterface $ul_marketo, CurrentRouteMatch $current_route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->marketo = $ul_marketo;
    $this->currentRouteMatch = $current_route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('ul_marketo'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $definition = $this->getPluginDefinition();

    // Check to see if we are on a node page, override settings from that node.
    $entity = $this->currentRouteMatch->getParameter('node');

    if (!empty($entity)) {
      $settings = $this->marketo->getEntitySettings($entity);
    }

    if (!empty($settings[$definition['marketo_form']])) {
      $marketoForm = $this->marketoManager->createInstance($definition['marketo_form'], [], $entity);
    }
    else {
      $marketoForm = $this->marketoManager->createInstance($definition['marketo_form']);
    }

    return $marketoForm->renderBlock();
  }

}

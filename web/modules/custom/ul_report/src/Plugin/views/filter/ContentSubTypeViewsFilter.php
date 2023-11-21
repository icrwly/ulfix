<?php

namespace Drupal\ul_report\Plugin\views\filter;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\ManyToOne;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;

/**
 * Filters by content sub type of the node.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("content_sub_type_views_filter")
 */
class ContentSubTypeViewsFilter extends ManyToOne {

  /**
   * The current display.
   *
   * @var string
   *   The current display of the view.
   */
  protected $currentDisplay;

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->valueTitle = 'Filter by sub COU';
    $this->definition['options callback'] = [$this, 'generateOptions'];
    $this->currentDisplay = $view->current_display;
  }

  /**
   * Helper function that generates the options.
   *
   * @return array
   *   An array of states and their ids.
   */
  public function generateOptions() {
    $options = \Drupal::service('ul_marketo.data_service')->getContentSubTypeOptions();

    return $options;
  }

  /**
   * Helper function that builds the query.
   */
  public function query() {
    if (!empty($this->value)) {
      $configuration = [
        'table' => 'taxonomy_index',
        'field' => 'nid',
        'left_table' => 'node_field_data',
        'left_field' => 'nid',
        'operator' => '=',
      ];

      $join = Views::pluginManager('join')->createInstance('standard', $configuration);
      $this->query->addRelationship('node_sub_type', $join, 'node_field_data');

      $this->query->addWhere('AND', 'node_sub_type.tid', $this->value, '=');
    }
  }

}

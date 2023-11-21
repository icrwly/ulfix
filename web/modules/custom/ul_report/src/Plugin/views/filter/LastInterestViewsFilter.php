<?php

namespace Drupal\ul_report\Plugin\views\filter;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\ManyToOne;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;

/**
 * Filters by Last Interest value of the node.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("last_interest_views_filter")
 */
class LastInterestViewsFilter extends ManyToOne {

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
    $this->valueTitle = 'Filter by last interest';
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
    $options = [];
    $last_interest = array_keys(\Drupal::service('ul_marketo.data_service')->getLastInterestToSubCou());
    sort($last_interest);
    foreach ($last_interest as $l) {
      $options[$l] = $l;
    }

    return $options;
  }

  /**
   * Helper function that builds the query.
   */
  public function query() {
    if (!empty($this->value)) {
      $configuration = [
        'table' => 'node__field_shared_marketo_forms',
        'field' => 'entity_id',
        'left_table' => 'node_field_data',
        'left_field' => 'nid',
        'operator' => '=',
      ];

      $join = Views::pluginManager('join')->createInstance('standard', $configuration);
      $this->query->addRelationship('node_last_interest', $join, 'node_field_data');

      $this->query->addWhere('AND', 'node_last_interest.field_shared_marketo_forms_last_interest', $this->value, '=');
    }
  }

}

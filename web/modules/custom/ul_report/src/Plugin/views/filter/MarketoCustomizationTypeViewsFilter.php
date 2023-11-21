<?php

namespace Drupal\ul_report\Plugin\views\filter;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\ManyToOne;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;

/**
 * Filters by Marketo Customization entity referenced on the node.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("marketo_customization_type_views_filter")
 */
class MarketoCustomizationTypeViewsFilter extends ManyToOne {

  /**
   * The current display.
   *
   * @var string
   *   The current display of the view.
   */
  protected $currentDisplay;

  /**
   * Marketo Form types to exclude from the filter.
   *
   * @var array
   *   The array of marketo form types that should be excluded form the filter.
   */
  protected $excluded_types = [
    'mkto_pref_ctr',
  ];

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->valueTitle = 'Filter by Marketo form type';
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

    $forms = \Drupal::service('entity_type.manager')->getStorage('marketo_form_type')->loadMultiple();
    foreach ($forms as $id => $entity) {
      if (!in_array($id, $this->excluded_types)) {
        $options[$id] = $entity->label();
      }
    }

    asort($options);

    return $options;
  }

  /**
   * Helper function that builds the query.
   */
  public function query() {
    if (!empty($this->value)) {
      $marketo_custom_configuration = [
        'table' => 'node__field_shared_marketo_custom',
        'field' => 'entity_id',
        'left_table' => 'node_field_data',
        'left_field' => 'nid',
        'operator' => '=',
      ];

      $marketo_custom_join = Views::pluginManager('join')->createInstance('standard', $marketo_custom_configuration);
      $this->query->addRelationship('node_marketo_form_custom', $marketo_custom_join, 'node');

      $marketo_form_configuration = [
        'table' => 'marketo_form_field_data;',
        'field' => 'id',
        'left_table' => 'node_marketo_form_custom',
        'left_field' => 'field_shared_marketo_custom_target_id',
        'operator' => '=',
      ];

      $marketo_form_join = Views::pluginManager('join')->createInstance('standard', $marketo_form_configuration);
      $this->query->addRelationship('node_marketo_form_field_data', $marketo_form_join, 'node');

      $this->query->addWhere('AND', 'node_marketo_form_field_data.type', $this->value, '=');
    }
  }

}

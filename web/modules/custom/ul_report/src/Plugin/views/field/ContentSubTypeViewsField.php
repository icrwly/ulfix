<?php

namespace Drupal\ul_report\Plugin\views\field;

use Drupal\taxonomy\Entity\Term;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("content_sub_type_views_field")
 */
class ContentSubTypeViewsField extends FieldPluginBase {

  /**
   * The current display.
   *
   * @var string
   *   The current display of the view.
   */
  protected $currentDisplay;

  /**
   * Assoc array of content type to content sub type field.
   *
   * @var array
   *   The field map.
   */
  protected $subTypeFields = [
    'event' => 'field_event_type',
    'help' => 'field_help_type',
    'knowledge' => 'field_know_type',
    'news' => 'field_news_type',
    'offering' => 'field_of_service_category',
    'resource' => 'field_resource_type',
    'tool' => 'field_tool_types',
  ];

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->currentDisplay = $view->current_display;
  }

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Do nothing -- to override the parent query.
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    // First check whether the field should be hidden.
    $options['hide_alter_empty'] = ['default' => FALSE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $sub_type = '';
    $node = $values->_entity;
    $field = isset($this->subTypeFields[$node->bundle()]) ? $this->subTypeFields[$node->bundle()] : NULL;

    if (!empty($field)) {
      $sub_type = $node->{$field} !== NULL ? $node->{$field}->getString() : '';
      if (!empty($sub_type)) {
        $term = Term::load($sub_type);
        return !empty($term) ? $term->getName() : '';
      }
    }

    return $sub_type;
  }

}

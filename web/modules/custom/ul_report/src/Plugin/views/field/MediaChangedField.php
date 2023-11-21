<?php

namespace Drupal\ul_report\Plugin\views\field;

use Drupal\taxonomy\Entity\Term;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;
use Drupal\Core\Datetime\DateFormatter;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("media_changed_field")
 */
class MediaChangedField extends FieldPluginBase {

  /**
   * The current display.
   *
   * @var string
   *   The current display of the view.
   */
  protected $currentDisplay;

  /**
   * Date formatter class.
   *
   * @var dateFormatter
   *   Date formatter service.
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->currentDisplay = $view->current_display;
    $this->dateFormatter = \Drupal::service('date.formatter');
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
    $this->query->addField('media_field_data', 'changed', NULL, []);
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
    $changed = $values->media_field_data_changed;
    return $this->dateFormatter->format($changed, 'short');
  }

  /**
   * {@inheritdoc}
   */
  public function clickSort($order) {
    $this->ensureMyTable();
    $this->query->addOrderBy('media_field_data', 'changed', $order);
  }

}

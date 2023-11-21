<?php

namespace Drupal\ul_crc_asset\Plugin\views\filter;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\ManyToOne;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;

/**
 * Filters by language of the crc asset.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("crc_asset_language_views_filter")
 */
class CrcAssetLanguageViewsFilter extends ManyToOne {

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
    $this->valueTitle = 'Filter by language';
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
    $languages = \Drupal::languageManager()->getLanguages();

    $options = [];
    foreach ($languages as $key => $l) {
      $options[$key] = $l->getName();
    }
    // "none" => "Language Undefined (None)".
    $options['none'] = $this->t("Language Undefined (None)");
    return $options;
  }

  /**
   * Helper function that builds the query.
   */
  public function query() {
    if (!empty($this->value)) {
      $this->query->addWhere('AND', 'crc_asset.langcode', $this->value, '=');
    }
  }

}

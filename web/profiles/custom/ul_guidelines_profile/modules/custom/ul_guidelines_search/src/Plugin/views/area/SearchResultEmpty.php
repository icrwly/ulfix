<?php

namespace Drupal\ul_guidelines_search\Plugin\views\area;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\DefaultSummary;
use Drupal\views\Plugin\views\area\AreaPluginBase;

/**
 * Views area handler to display some configurable result summary.
 *
 * @ingroup views_area_handlers
 *
 * @ViewsArea("search_result_empty")
 */
class SearchResultEmpty extends AreaPluginBase {
  /**
   * {@inheritdoc}
  */
  public function render($empty = FALSE) {
    $search = [];
    $input = $this->view->exposed_raw_input;
    $number_of_results = $this->view->total_rows;

    // If a search was performed but it returned no results
    // Then show an error message.
    if (!empty($input) && $number_of_results <= 0) {
      foreach($input as $key => $value){
        $search[$key] = Xss::filterAdmin(Html::escape($value));
      }
      return [
        '#theme' => 'search_results_empty',
        '#search' => $search,
        '#results' => 0
      ];
    } else {
      return [];
    }
  }
}

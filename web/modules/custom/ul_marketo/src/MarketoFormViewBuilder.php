<?php

namespace Drupal\ul_marketo;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Render\Element;

/**
 * Render controller for marketo forms.
 */
class MarketoFormViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildMultiple(array $build_list) {
    $build_list = parent::buildMultiple($build_list);

    // Allow enabled behavior plugin to alter the rendering.
    foreach (Element::children($build_list) as $key) {
      $build = $build_list[$key];
      $display = EntityViewDisplay::load('marketo_form.' . $build['#marketo_form']->bundle() . '.' . $build['#view_mode']) ?: EntityViewDisplay::load('marketo_form.' . $build['#marketo_form']->bundle() . '.default');
      // In case we use paragraphs type with no fields the EntityViewDisplay
      // might not be available yet.
      if (!$display) {
        $display = EntityViewDisplay::create([
          'targetEntityType' => 'marketo_form',
          'bundle' => $build['#marketo_form']->bundle(),
          'mode' => 'default',
          'status' => TRUE,
        ]);
      }

      $build['#marketo_form']->getMarketoFormId();
    }

    return $build_list;
  }

}

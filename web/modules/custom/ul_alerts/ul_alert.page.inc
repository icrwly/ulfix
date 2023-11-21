<?php

/**
 * @file
 * Contains ul_alert.page.inc.
 *
 * Page callback for Alert entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Alert templates.
 *
 * Default template: ul_alert.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_ul_alert(array &$variables) {
  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}

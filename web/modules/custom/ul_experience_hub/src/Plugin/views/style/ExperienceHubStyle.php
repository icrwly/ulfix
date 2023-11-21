<?php

namespace Drupal\ul_experience_hub\Plugin\views\style;

use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Experience Hub style plugin to render rows in a grid with pagination.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "experience_hub",
 *   title = @Translation("Experience Hub"),
 *   help = @Translation("Displays rows as an Experience Hub."),
 *   theme = "hub__views_view_experience_hub",
 *   display_types = {"normal"}
 * )
 */
class ExperienceHubStyle extends StylePluginBase {

  /**
   * {@inheritdoc}
   */
  protected $usesRowPlugin = TRUE;

  /**
   * Does the style plugin support custom css class for the rows.
   *
   * @var bool
   */
  protected $usesRowClass = TRUE;

}

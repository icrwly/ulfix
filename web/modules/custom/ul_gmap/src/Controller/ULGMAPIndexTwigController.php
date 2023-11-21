<?php

namespace Drupal\ul_gmap\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for UL_GMAP Intro Page.
 *
 * @ingroup ul_gmap
 */
class ULGMAPIndexTwigController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function content() {

    return [
      // Your theme hook name.
      '#theme' => 'ul_gmap_index_hook',
    ];

  }

}

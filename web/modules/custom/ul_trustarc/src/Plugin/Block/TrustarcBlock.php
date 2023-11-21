<?php

namespace Drupal\ul_trustarc\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides the Trust Arc Block.
 *
 * @Block(
 *   id = "trustarc_block",
 *   admin_label = @Translation("Trust Arc block"),
 *   category = @Translation("UL Trust Arc (module)"),
 * )
 */
class TrustarcBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => NULL,
    ];
  }

}

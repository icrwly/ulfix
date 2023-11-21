<?php

namespace Drupal\vde_drush\Plugin\FormatManipulator;

use Drupal\vde_drush\FormatManipulatorDefault;

/**
 * Implements xlsx format handler.
 *
 * @FormatManipulator(
 *   id="xlsx"
 * )
 */
class FormatManipulatorXlsx extends FormatManipulatorDefault {

  /**
   * {@inheritdoc}
   */
  protected function extractHeader(&$content) {

    throw new \Exception(dt('Xlsx format manipulation is not supported yet.'));
  }

}

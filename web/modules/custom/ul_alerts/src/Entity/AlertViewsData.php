<?php

namespace Drupal\ul_alerts\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Alert entities.
 */
class AlertViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}

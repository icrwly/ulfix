<?php

namespace Drupal\ul_trustarc;

/**
 * TrustArc Script Service.
 */
class TrustArcService implements TrustArcServiceInterface {

  /**
   * {@inheritdoc}
   */
  public function getScriptUrl($scriptUrl) {
    return $scriptUrl;
  }

  /**
   * {@inheritdoc}
   */
  public function getScriptParams($paramsArray) {
    return $paramsArray;
  }

}

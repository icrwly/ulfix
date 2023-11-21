<?php

namespace Drupal\ul_trustarc;

/**
 * Interface for TrustArc script service.
 */
interface TrustArcServiceInterface {

  /**
   * Returns the Script Base URL.
   */
  public function getScriptUrl($scriptUrl);

  /**
   * Returns the Script Parameters.
   */
  public function getScriptParams($paramsArray);

}

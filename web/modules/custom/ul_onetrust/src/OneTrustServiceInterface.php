<?php

namespace Drupal\ul_onetrust;

/**
 * Interface for OneTrust script service.
 */
interface OneTrustServiceInterface {

  /**
   * Returns the Script URL.
   */
  public function getScriptUrl($scriptUrl);

  /**
   * Returns Data Domain Script (ID).
   */
  public function getDataDomain($dataDomain);

  /**
   * Returns New Data Domain Script (ID).
   */
  public function getNewDataDomain($dataDomain_new);

}

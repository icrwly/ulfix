<?php

namespace Drupal\ul_chat;

/**
 * Interface for Chat script service.
 */
interface ChatServiceInterface {

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

<?php

namespace Drupal\ul_disable_content;

/**
 * Interface for UL Disable Content Type service.
 */
interface DisableContentServiceInterface {

  /**
   * Returns a list of content types.
   */
  public function getContentTypes();

  /**
   * Returns a list of saved `hidden` content types from config.
   */
  public function getDisabledContentTypes();

}

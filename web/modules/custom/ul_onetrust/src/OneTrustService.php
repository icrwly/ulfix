<?php

namespace Drupal\ul_onetrust;

/**
 * OneTrust Script Service.
 */
class OneTrustService implements OneTrustServiceInterface {

  /**
   * {@inheritdoc}
   */
  public function getScriptUrl($scriptUrl) {
    return $scriptUrl;
  }

  /**
   * {@inheritdoc}
   */
  public function getDataDomain($dataDomain) {
    return $dataDomain;
  }

  /**
   * {@inheritdoc}
   */
  public function getNewDataDomain($dataDomain_new) {
    return $dataDomain_new;
  }

}

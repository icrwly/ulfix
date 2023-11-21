<?php

namespace Drupal\ul_chat;

/**
 * Chat Script Service.
 */
class ChatService implements ChatServiceInterface {

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

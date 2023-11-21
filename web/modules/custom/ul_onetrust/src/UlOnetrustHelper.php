<?php

namespace Drupal\ul_onetrust;

/**
 * Provides tools for the Onetrust Cookie Banner.
 */
class UlOnetrustHelper {

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->config_factory = \Drupal::configFactory();
    $this->config = $this->config_factory->getEditable('ul_onetrust.settings');
  }

  /**
   * Enable the Cookie Banner.
   */
  public function enableCookieBanner() {
    \Drupal::logger('ul_onetrust')->notice('Enabling the Onetrust cookie banner.');
    $this->config->set('consent_notice_enable', 1)->save();
  }

  /**
   * Disable the Cookie Banner.
   */
  public function disableCookieBanner() {
    \Drupal::logger('ul_onetrust')->notice('Disabling the Onetrust cookie banner.');
    $this->config->set('consent_notice_enable', 0)->save();
  }

  /**
   * Enable the test Banner.
   */
  public function enableTestBanner() {
    \Drupal::logger('ul_onetrust')->notice('Enabling the `test` banner.');
    $this->config->set('use_testing_script', 1)->save();
  }

  /**
   * Disable the test Banner.
   */
  public function disableTestBanner() {
    \Drupal::logger('ul_onetrust')->notice('Disabling the `test` banner. Using the PROD banner.');
    $this->config->set('use_testing_script', 0)->save();
  }

}

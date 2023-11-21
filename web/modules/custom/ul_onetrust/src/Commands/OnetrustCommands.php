<?php

namespace Drupal\ul_onetrust\Commands;

use Drush\Commands\DrushCommands;
use Drupal\ul_onetrust\UlOnetrustHelper;

/**
 * Provide Drush commands for the UL Onetrust module.
 */
class OnetrustCommands extends DrushCommands {

  /**
   * Enable the cookie banner.
   *
   * @command ul-onetrust:enable-onetrust-banner
   * @aliases ot-enable
   */
  public function enableBanner() {
    \Drupal::logger('UL OneTrust')->notice('Running drush command: `enable-onetrust-banner`.');
    $banner = new UlOnetrustHelper();
    $banner->enableCookieBanner();
  }

  /**
   * Disable the cookie banner.
   *
   * @command ul-onetrust:disable-onetrust-banner
   * @aliases ot-disable
   */
  public function disableCookieBanner() {
    \Drupal::logger('UL OneTrust')->notice('Running drush command: `disable-onetrust-banner`.');
    $banner = new UlOnetrustHelper();
    $banner->disableCookieBanner();
  }

  /**
   * Use testing banner.
   *
   * @command ul-onetrust:use-test-banner
   * @aliases ot-use-test
   */
  public function useTestBanner() {
    \Drupal::logger('UL OneTrust')->notice('Running drush command: `use-test-banner`.');
    $banner = new UlOnetrustHelper();
    $banner->enableTestBanner();
  }

  /**
   * Use production banner.
   *
   * @command ul-onetrust:use-prod-banner
   * @aliases ot-use-prod
   */
  public function useProdBanner() {
    \Drupal::logger('UL OneTrust')->notice('Running drush command: `use-prod-banner`.');
    $banner = new UlOnetrustHelper();
    $banner->disableTestBanner();
  }

}

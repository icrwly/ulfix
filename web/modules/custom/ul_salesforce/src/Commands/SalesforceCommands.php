<?php

namespace Drupal\ul_salesforce\Commands;

use Drush\Commands\DrushCommands;
use Drupal\ul_salesforce\UlSalesforceContentGenerator;

/**
 * Provide Drush commands for the UL Salesforce module.
 */
class SalesforceCommands extends DrushCommands {

  /**
   * Generates content and blocks required by L2O-R3, updated config.
   *
   * @command ul-salesforce:generate-salesforce-content
   * @aliases sf-generate
   */
  public function generateSalesforceContent() {
    \Drupal::logger('UL Marketo')->notice('Running drush command: `generate-salesforce-content`.');

    $content = new UlSalesforceContentGenerator();
    $content->generateWebToCaseContent();
  }

  /**
   * Update Web-to-case translations.
   *
   * @command ul-salesforce:update-salesforce-translations
   * @aliases sf-update
   */
  public function updateSalesforceTranslations() {
    \Drupal::logger('UL Marketo')->notice('Running drush command: `update-salesforce-translations`.');

    $content = new UlSalesforceContentGenerator();
    $content->updateWebToCaseTranslations();
  }

}

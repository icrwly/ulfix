<?php

namespace Drupal\ul_marketo\Commands;

use Drush\Commands\DrushCommands;
use Drupal\ul_marketo\MarketoDrushHelper;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Provide Drush commands for the UL Marketo module.
 */
class MarketoCommands extends DrushCommands {

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The entity type messenger.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(MessengerInterface $messenger, LoggerChannelFactoryInterface $logger_factory) {
    $this->messenger = $messenger;
    $this->loggerFactory = $logger_factory;
  }

  /**
   * Use DEV (staging) form IDs.
   *
   * @command ul-marketo:use-dev-forms
   * @aliases mkto-use-dev
   */
  public function useDevForms() {
    $this->loggerFactory->get('ul_marketo')->info('Running drush command: `use-dev-forms`.');
    $this->messenger->addStatus('Running drush command: `use-dev-forms`.');
    $mkto = new MarketoDrushHelper($this->messenger, $this->loggerFactory);
    $mkto->useDevForms();
  }

  /**
   * Use PROD form IDs.
   *
   * @command ul-marketo:use-prod-forms
   * @aliases mkto-use-prod
   */
  public function useProdForms() {
    $this->loggerFactory->get('ul_marketo')->info('Running drush command: `use-prod-forms`.');
    $this->messenger->addStatus('Running drush command: `use-prod-forms`.');
    $mkto = new MarketoDrushHelper($this->messenger, $this->loggerFactory);
    $mkto->useProdForms();
  }

  /**
   * Generate a basic `Contact Us` Landing Page.
   *
   * @command ul-marketo:create-contactus-page
   * @aliases mkto-new-contactus
   */
  public function createContactusPage() {
    $this->loggerFactory->get('ul_marketo')->info('Running drush command: `mkto-contactus`.');
    $this->messenger->addStatus('Running drush command: `mkto-contactus`.');
    $mkto = new MarketoDrushHelper($this->messenger, $this->loggerFactory);
    $mkto->createContactUsPage();
    $this->loggerFactory->get('ul_marketo')->info('All done!');
    $this->messenger->addStatus('All done!');
  }

}

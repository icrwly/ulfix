<?php

namespace Drupal\ul_media_usage\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drush\Commands\DrushCommands;
use Drupal\ul_media_usage\EntityUsageBatchManager;

/**
 * Media Usage drush commands.
 */
class MediaUsageCommands extends DrushCommands {

  /**
   * Mediatity Usage batch manager.
   *
   * @var \Drupal\ul_media_usage\EntityUsageBatchManager
   */
  protected $batchManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityUsageBatchManager $batch_manager) {
    parent::__construct();
    $this->batchManager = $batch_manager;
  }

  /**
   * Recreate all medai usage statistics.
   *
   * @command ul_media_usage:recreate
   * @aliases mu-r, media-usage-recreate
   */
  public function recreate() {
    $this->batchManager->recreate(1);
  }

  /**
   * Regenerate all medai usage statistics.
   *
   * @command ul_media_usage:regenerate
   * @aliases mu-rg, media-usage-regenerate
   */
  public function regenerate() {
    $this->batchManager->generateDataMediaUsage(1);
  }

}

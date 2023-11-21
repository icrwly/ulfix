<?php

namespace Drupal\ul_report\Commands;

use Drush\Commands\DrushCommands;
use Drupal\ul_report\UlReportService;

/**
 * Provide Drush commands for the UL Report module.
 */
class ReportCommands extends DrushCommands {

  /**
   * Ul ReportService.
   *
   * @var object
   */
  protected $report_service;

  /**
   * Constructs a new ReportCommands object.
   *
   * @param \Drupal\ul_report\UlReportService $report_service
   *   Report Service object.
   */
  public function __construct(UlReportService $report_service) {
    $this->report_service = $report_service;
  }

  /**
   * Manually update the marketo_report table.
   *
   * @command ul-report:update-marketo-report
   * @aliases umr
   */
  public function updateMarketoReport() {
    $this->report_service->updateMarketoReport();
  }

  /**
   * Update the node_media_file table for all nodes.
   *
   * @command ul-report:update-node-media-file
   * @aliases unmf
   *
   * @option idlist
   *   Comma separate list of node IDs to specify which nodes to update the referenced media and files. Default is to update all nodes.
   */
  public function updateNodeMediaFile(array $options = [
    'idlist' => NULL,
  ]) {
    \Drupal::logger('UL Report')->notice('Running update-node-media-file');

    if ($options['idlist']) {
      $ids = array_filter(explode(",", $options['idlist']));
    }

    \Drupal::service('ul_report.report_service')->updateNodeMediaAndFiles($ids);
  }

}

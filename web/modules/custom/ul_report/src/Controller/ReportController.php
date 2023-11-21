<?php

namespace Drupal\ul_report\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;

/**
 * List and download migration reports.
 */
class ReportController extends ControllerBase {

  /**
   * Array of custom reports.
   *
   * @var array
   */
  protected $reports = [
    [
      'name' => 'Marketo Report',
      'path' => '/admin/reports/marketo-report',
      'description' => 'Lists all nodes with their related Marketo information',
    ],
    [
      'name' => 'Marketo - Gated Form',
      'path' => '/admin/reports/marketo-gated-form',
      'description' => 'Nodes that have gated content forms with the ability to filter Asset Title, Asset Language, and Gate This Node flag.',
    ],
  ];

  /**
   * Returns a table listing selected UL reports with links and descriptions.
   *
   * @return array
   *   The themed table.
   */
  public function list() {

    $header = ['Name', 'Path', 'Description'];
    $rows = [];

    foreach ($this->reports as $report) {
      $rows[] = [
        Markup::create('<a href="' . $report['path'] . '">' . $report['name'] . '</a>'),
        Markup::create('<a href="' . $report['path'] . '">' . $report['path'] . '</a>'),
        $report['description'],
      ];
    }

    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];

  }

}

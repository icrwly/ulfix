<?php

namespace Drupal\ul_marketo\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Render\Markup;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Drupal\file\Entity\File;
use Drupal\Core\StreamWrapper\StreamWrapperManager;

/**
 * List and download migration reports.
 */
class MigrationReportController extends ControllerBase {


  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $date_formatter;

  /**
   * The stream wrapper manager service.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManager
   */
  protected $stream_wrapper_manager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManager $stream_wrapper_manager
   *   The stream wrapper manager service.
   */
  public function __construct(Connection $connection, DateFormatter $date_formatter, StreamWrapperManager $stream_wrapper_manager) {
    $this->connection = $connection;
    $this->date_formatter = $date_formatter;
    $this->stream_wrapper_manager = $stream_wrapper_manager;
  }

  /**
   * Create a new instance.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container.
   *
   * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
   * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('date.formatter'),
      $container->get('stream_wrapper_manager')
    );
  }

  /**
   * Lists the migration reports.
   *
   * @return array
   *   The themed table.
   */
  public function list() {

    $header = ['Name', 'Size', 'Date Created'];
    $rows = [];

    $result = $this->connection->query("select * from file_managed where uri like 'private://%migration_report%' ")->fetchAll();
    foreach ($result as $row) {
      $rows[] = [
        Markup::create('<a href="/admin/reports/migration-report/' . $row->fid . '">' . $row->filename . '</a>'),
        format_size($row->filesize),
        $this->date_formatter->format($row->created),
      ];
    }

    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];

  }

  /**
   * Downloads the migration reports.
   */
  public function download($fid) {
    $file = File::load($fid);
    $uri = $file->getFileUri();
    $stream = $this->stream_wrapper_manager->getViaUri($uri);
    $path = $stream->realpath();
    $response = new BinaryFileResponse($path);
    $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    return $response;
  }

}

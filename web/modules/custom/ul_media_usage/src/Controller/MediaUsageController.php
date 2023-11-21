<?php

namespace Drupal\ul_media_usage\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\ul_media_usage\MediaUsage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines MediaUsageController class.
 */
class MediaUsageController extends ControllerBase {

  /**
   * The service MediaUsage.
   *
   * @var \Drupal\ul_media_usage\MediaUsage
   */
  protected $mediaUsage;

  /**
   * MediaUsageController constructor.
   *
   * @param \Drupal\ul_media_usage\MediaUsage $media_usage
   *   The service of ul_media_usage.usage.
   */
  public function __construct(MediaUsage $media_usage) {
    $this->mediaUsage = $media_usage;
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal service container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ul_media_usage.usage')
    );
  }

  /**
   * Export the data to CSV file.
   */
  public function exportCsv() {
    $count = 0;
    $data = $this->mediaUsage->selectMediaUsageData('ALL');
    if (!empty($data)) {
      $count = $this->dataToCsvDownload($data);
    }

    if ($count > 0) {
      $output = "<h1>There are \"$count\" items exported successfully!</h1>";
    }
    else {
      $output = $this->t("<h1>No data exported!</h1>");
    }

    $build = [
      '#markup' => $output,
    ];
    return $build;

  }

  /**
   * Select data from database and output for download.
   *
   * @param array $data
   *   Asociate array of data.
   * @param string $filename
   *   The filename to download.
   * @param string $delimiter
   *   The char of delimiter.
   */
  protected function dataToCsvDownload(array $data, $filename = "ul-media-usage-export.csv", $delimiter = ",") {
    error_reporting(0);
    $count = 0;
    $streamSize = 0;

    header('Content-Type: application/csv; charset=utf-8;');
    header('Content-Disposition: attachment; filename="' . $filename . '",');

    // Open the "output" stream
    // see http://www.php.net/manual/en/wrappers.php.php#refsect2-wrappers.php-unknown-unknown-unknown-descriptioq
    $f = fopen('php://output', 'w');
    // Add BOM to fix UTF-8 in Excel.
    fwrite($f, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

    foreach ($data as $line) {
      fputcsv($f, $line, $delimiter);
      $count++;
    }
    // Get size of output after last output data sent.
    $streamSize = ob_get_length();
    header('Content-Length: ' . $streamSize);

    fclose($f);
    exit();
  }

}

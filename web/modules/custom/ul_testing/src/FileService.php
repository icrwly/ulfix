<?php

namespace Drupal\ul_testing;

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Provides tools for converting csv and excel files to an associative array.
 */
class FileService {

  /**
   * Mimetype for xlsx files.
   *
   * @var string
   */
  public static $XLSX_TYPE = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

  /**
   * Mimetype for xls files.
   *
   * @var string
   */
  public static $XLS_TYPE = 'application/vnd.ms-excel';

  /**
   * Mimetype for csv files.
   *
   * @var string
   */
  public static $CSV_TYPE = 'text/csv';

  /**
   * Convert an uploaded csv or excel file to an associative array.
   *
   * @param string $fid
   *   The file id of the uploaded file to convert.
   *
   * @return array
   *   The associative array of the converted file.
   */
  public function fileToArray($fid) {
    $data = [];
    if (!empty($fid)) {
      $file = \Drupal::entityTypeManager()->getStorage('file')->load($fid);
      if (!empty($file)) {
        switch ($file->getMimeType()) {
          case FileService::$XLS_TYPE:
          case FileService::$XLSX_TYPE:
            $data = $this->marketoExcelToArray($file);
            break;

          case FileService::$CSV_TYPE:
            $data = $this->marketoCsvToArray($file);
            break;
        }
      }
    }

    return $data;
  }

  /**
   * Convert an uploaded csv file to an associative array.
   *
   * @param object $file
   *   The uploaded file to convert.
   *
   * @return array
   *   The associative array of the converted file.
   */
  protected function marketoCsvToArray(object &$file) {
    $data = [];
    $file_path = \Drupal::service('file_system')->realpath($file->getFileUri());
    $content = file_get_contents($file_path);
    $rows = explode(PHP_EOL, $content);

    // Get the column headings from the first row.
    if ($rows) {
      $cols = str_getcsv($rows[0]);
      for ($c = 0; $c < count($cols); $c++) {
        $cols[$c] = trim($cols[$c]);
      }

      for ($i = 1; $i < count($rows); $i++) {
        $row = str_getcsv($rows[$i]);
        $data_row = [];
        for ($c = 0; $c < count($cols); $c++) {
          $data_row[$cols[$c]] = trim($row[$c]);
        }
        $data[] = $data_row;
      }

    }

    return $data;
  }

  /**
   * Convert an uploaded excel file to an associative array.
   *
   * @param object $file
   *   The uploaded file to convert.
   *
   * @return array
   *   The associative array of the converted file.
   */
  protected function marketoExcelToArray(object &$file) {
    $data = [];

    // Read From excel file.
    $file_path = \Drupal::service('file_system')->realpath($file->getFileUri());
    $spreadsheet = IOFactory::load($file_path);
    $worksheet = $spreadsheet->getActiveSheet();
    $sheet = $worksheet->toArray();

    // First Row contains column headings.
    if (count($sheet)) {
      $data = [];
      $cols = [];
      for ($c = 0; $c < count($sheet[0]); $c++) {
        $cols[] = trim($sheet[0][$c]);
      }

      for ($r = 1; $r < count($sheet); $r++) {
        $row = [];
        for ($c = 0; $c < count($cols); $c++) {
          $row[$cols[$c]] = trim($sheet[$r][$c]);
        }
        $data[] = $row;
      }
    }

    return $data;
  }

}

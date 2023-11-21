<?php

namespace Drupal\ul_crc;

use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Cache\CacheableResponseTrait;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CRCService.
 */
class CRCResponse extends Response implements CacheableResponseInterface {

  use CacheableResponseTrait;

  /**
   * The response data object.
   *
   * @var string|null
   */
  protected $responseData;

  /**
   * Constructs a new CrcResponse Object.
   *
   * @param string $data
   *   The encoded json string data.
   * @param int $status
   *   The response status.
   * @param array $headers
   *   The array of header data passed to the response.
   */
  public function __construct($data = NULL, $status = 200, array $headers = []) {
    $this->responseData = $data;

    // By default any invalid status causes a fatal exception.
    // Intercept this and replace it with 500 so it doesn't kill the
    // entire site.
    try {
      $this->setStatusCode($status);
    }
    catch (\Exception $e) {
      $status = 500;
      $this->setStatusCode($status);
    }

    // If is a valid response then continue building the rest of the object.
    parent::__construct('', $status, $headers);
  }

  /**
   * Parse and return the API response data .
   *
   * @return array
   *   Returns an array of the decoded json data.
   */
  public function getResponseDataById() {
    $data = json_decode($this->responseData, TRUE);
    $dataCrc = $this->formatDataApiV3($data);
    return $dataCrc;
  }

  /**
   * Parse and return the data.
   *
   * @return array
   *   Returns array data of the decoded json string.
   */
  public function getResponseData() {
    $data = json_decode($this->responseData, TRUE);
    return $data;
  }

  /**
   * Format the JSON data of API V3.
   *
   * @param string $data
   *   The array of data passed as reference.
   */
  public function formatDataApiV3($data = []) {
    $tmp_data = $data;
    $original_url = $tmp_data['original_url'];

    if (empty($tmp_data['available_languages'])) {
      $data['available_languages'][0] = 'en';
      if (isset($data['asset_files'][0])) {
        $data['sm_thumbnail_url'] = $data['asset_files'][0]['sm_thumbnail_url'];
        $data['med_thumbnail_url'] = $data['asset_files'][0]['med_thumbnail_url'];
        $data['original_url'] = $data['asset_files'][0]['original_url'];
      }
    }
    else {
      foreach ($tmp_data['asset_files'] as $asset_file) {
        if ($original_url == $asset_file['original_url']) {
          $data['available_languages'][0] = $asset_file['language'];
          break;
        }
      }
    }
    return $data;

  }

}

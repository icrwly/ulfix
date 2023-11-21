<?php

namespace Drupal\ul_crc;

use Drupal\Component\Utility\UrlHelper;

/**
 * Handle requests to the CRC server.
 *
 * Makes token auth requests.
 */
class CRCRequest {

  /**
   * CRC Staging environment URL.
   */
  const URL_STAGE = 'https://staging.crc.ul.com';

  /**
   * CRC Preproduction environment URL.
   */
  const URL_PREPROD = 'https://preproduction.crc.ul.com';

  /**
   * CRC Production environment URL.
   */
  const URL_PROD = 'https://crc.ul.com';

  /**
   * The request method (e.g. GET or POST)
   *
   * @var string
   */
  protected $method = 'GET';

  /**
   * The environment flag.
   *
   * @var bool
   */
  protected $environment = 'production';

  /**
   * The authorization token to connect to the CRC service.
   *
   * @var string
   */
  protected $authToken;

  /**
   * The request path.
   *
   * @var string
   */
  protected $path;

  /**
   * The request parameters.
   *
   * @var array
   */
  protected $params = [];

  /**
   * Constructs a new CRCRequest object.
   */
  public function __construct($auth_token, $path, $params = []) {
    $this->authToken = $auth_token;
    $this->path = $path;
    $this->params = $params;
  }

  /**
   * Set request to staging environment.
   */
  public function useStage() {
    $this->environment = 'stage';
  }

  /**
   * Set request to preproduction environment.
   */
  public function usePreproduction() {
    $this->environment = 'preproduction';
  }

  /**
   * Set the Request method.
   *
   * @param string $method
   *   The request method (e.g GET OR POST)
   */
  public function setMethod($method) {
    $this->method = $method;
  }

  /**
   * Send request to CRC.
   *
   * @return \Drupal\ul_crc\CRCResponse
   *   Returns the CRCResponse object.
   */
  public function execute() {

    // Parse the arguments.
    $string = '';
    if (!empty($this->params)) {
      if ($this->method == 'GET') {
        $string = '?' . UrlHelper::buildQuery($this->params);
      }
    }

    // Environment URL.
    $env_url = self::URL_PROD;

    switch ($this->environment) {
      case 'stage':
        $env_url = self::URL_STAGE;
        break;

      case 'preproduction':
        $env_url = self::URL_PREPROD;
        break;

    }
    $url = $env_url . $this->path . $string;
    // Generate options.
    $options = [
      'headers' => ['Authorization' => 'Token token=' . $this->authToken],
      'verify' => FALSE,
    ];

    if ($this->method == 'POST') {
      $options['form_params'] = $this->params;
    }

    $client = \Drupal::httpClient();
    try {
      $request = $client->request($this->method, $url, $options);
      $body = $request->getBody();
      $code = $request->getStatusCode();
    }
    catch (\Exception $e) {
      $body = $e->getMessage();
      $code = $e->getCode();
    }

    // Return CRC Response.
    return new CRCResponse($body, $code);
  }

}

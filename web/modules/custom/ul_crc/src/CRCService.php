<?php

namespace Drupal\ul_crc;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use GuzzleHttp\Client;
use Drupal\ul_crc_asset\Entity\CRCAsset;
use Drupal\Core\Entity\EntityStorageException;

/**
 * Provide service to crc_asset entity.
 */
class CRCService implements CRCServiceInterface {

  /**
   * The CRC Config settings.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The Drupal Cache Service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The Cache Tag invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTags;

  /**
   * The Guzzle HTTP Client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * The CRC API version.
   *
   * @var string
   */
  protected $api;

  /**
   * Constructs a new CRCService object.
   */
  public function __construct($config, CacheBackendInterface $cache_backend, CacheTagsInvalidatorInterface $cache_tags, Client $http_client) {
    $this->config = $config->get('ul_crc.settings');
    $this->cache = $cache_backend;
    $this->cacheTags = $cache_tags;
    $this->httpClient = $http_client;
    $this->api = '/asset_api/v3/';
  }

  /**
   * Get relative cache expire timestamp.
   *
   * @return int
   *   The number of seconds for cache to expire.
   */
  private function getCacheExpire() {
    return time() + $this->config->get('cache_response_interval');
  }

  /**
   * Generate cache ID.
   *
   * @param string $prefix
   *   The unique cache prefix.
   * @param array $args
   *   An array of request parameters.
   *
   * @return string
   *   The full cache id string.
   */
  private function generateCacheId($prefix, array $args) {
    if (is_array($args)) {
      foreach ($args as $key => $value) {
        $args[$key] = mb_strtolower($value);
      }
    }
    $string = implode(':', $args);
    $cache_key = Crypt::hashBase64(serialize($string));
    return $prefix . ':' . $cache_key;
  }

  /**
   * Helper function for parsing search params.
   *
   * @param mixed $search
   *   An string or array of search parameters.
   * @param int $page
   *   The page in the search.
   *
   * @return array
   *   A normalized search array.
   */
  private function normalizeSearchParams($search, $page = 1) {

    // If params is an array then use that.
    if (is_array($search)) {
      $params = $search;
      $params['page'] = $page;
    }
    // Otherwise create a basic params array with just keyword.
    else {
      $params = [
        'keyword' => $search,
        'page' => $page,
      ];
    }

    return $params;
  }

  /**
   * Helper function for creating service request.
   *
   * @param string $path
   *   The request path.
   * @param array $arguments
   *   The arguments passed to the request.
   *
   * @return \Drupal\ul_crc\CRCRequest
   *   The CRC request object.
   */
  protected function createRequest($path, array $arguments = []) {

    $path = (strstr($path, '/asset_api/v3')) ? $path : "/asset_api/v3/" . $path;

    $request = new CRCRequest($this->config->get('auth_token'), $path, $arguments);

    // Set environment.
    if ($this->config->get('environment') == 'Staging') {
      $request->useStage();
    }
    if ($this->config->get('environment') == 'Preproduction') {
      $request->usePreproduction();
    }

    return $request;

  }

  /**
   * Invalidate the Drupal cache.
   */
  public function invalidateCache() {
    $this->cacheTags->invalidateTags(['ul_crc_api:search', 'ul_crc_api:asset']);
  }

  /**
   * Search for a page of assets by keyword.
   *
   * @param mixed $keyword
   *   A string or array of search parameters.
   * @param int $page
   *   The page in the search.
   *
   * @return array
   *   The array of results from the request. Includes pagination data.
   */
  public function search($keyword, $page = 1) {

    // Normalize $keyword.
    $params = $this->normalizeSearchParams($keyword, $page);
    $cid = $this->generateCacheId('ul_crc_api:search', $params);
    $data = $this->cache->get($cid);

    // Return cached data if it exists.
    if (!empty($data->data)) {
      return $data->data;
    }

    $data = $this->doSearch($params);

    if (!empty($data)) {
      $this->cache->set($cid, $data, $this->getCacheExpire(), ['ul_crc_api:search']);
      return $data;
    }

    return FALSE;
  }

  /**
   * Search all assets by keyword.
   *
   * @param mixed $keyword
   *   The string or array of search parameters.
   *
   * @return array
   *   A full array results from the request. Does not include pagination data.
   */
  public function searchAll($keyword) {

    $params = $this->normalizeSearchParams($keyword);
    // The normalizer adds a page by default.
    // @todo Should rethink this.
    unset($params['page']);
    $cid = $this->generateCacheId('ul_crc_api:search', $params);

    $data = $this->cache->get($cid);

    // Return cached data if it exists.
    if (!empty($data->data)) {
      return $data->data;
    }

    // If data is not stored in cache then perform API call.
    // We are attempting to fetch all the results so start with the first
    // page of results and loop through the rest.
    $params = $this->normalizeSearchParams($keyword, 1);
    $data = $this->doSearch($params);

    if (!empty($data['data'])) {
      $all_results = $data['data'];
      $pager = $data['pagination_info'];

      // Start with the next page and loop through and make a request.
      for ($i = 2; $i <= $pager['total_pages']; $i++) {

        // Search remaining pages and merge results.
        $params = $this->normalizeSearchParams($keyword, $i);
        $results = $this->doSearch($params);
        $all_results = array_merge($data['data'], $results['data']);
      }

      if (!empty($all_results)) {
        $data = [
          'data' => $all_results,
        ];
      }

      $this->cache->set($cid, $data, $this->getCacheExpire(), ['ul_crc_api:search']);

      return $data;
    }

    return FALSE;

  }

  /**
   * Perform Search API call.
   *
   * @param array $params
   *   The normalized array of search parameters.
   *
   * @return array
   *   The array of results from the reqeust.
   */
  protected function doSearch(array $params) {
    $response = $this->createRequest('collateral_assets/search', $params)->execute();

    $data = [];

    if ($response->isOk()) {
      $items = [];
      $data = $response->getResponseData();

      if (!empty($data['data'])) {
        foreach ($data['data'] as $item) {
          // Cache the single asset item.
          $this->setAsset($item['id'], $item, $params['language']);
          // Save the item id.
          $items[] = $item;
        }
        // Override the search results with Ids.
        $data['data'] = $items;
      }
    }
    return $data;
  }

  /**
   * Set a specific CRC Asset.
   *
   * @param mixed $id
   *   The unique asset id.
   * @param array $data
   *   Data passed from the service.
   * @param mixed $langcode
   *   The language code.
   */
  public function setAsset($id, array $data, $langcode) {

    $cid = $this->generateCacheId('ul_crc_api:asset', [$id, $langcode]);
    $expire = time() + 600;

    // Parse the filename of the asset to retrieve expiration date. We are doing
    // this because each asset has a limited lifespan on Amazon and we need to
    // accommodate for it.
    if (!empty($data['original_url'])) {
      $file = $data['original_url'];
      $parsed = UrlHelper::parse($file);

      // Everytime an API request is sent it returns a new expiration date.
      if (!empty($parsed['query']['X-Amz-Date'])) {
        $amz_date = strtotime($parsed['query']['X-Amz-Date']);
        $amz_seconds = $parsed['query']['X-Amz-Expires'];

        // The drupal cache for this asset should expire before the Amazon
        // cache.
        // Default is time() + 600 seconds.
        $expire = $amz_date + $amz_seconds - 60;

      }

      $this->cache->set($cid, ['data' => $data], $expire, ['ul_crc_api:asset']);
    }
  }

  /**
   * Fetch a specific CRC Asset.
   *
   * @param mixed $id
   *   The unique asset id.
   * @param mixed $langcode
   *   The langcode.
   *
   * @return array
   *   An array of that asset's data.
   */
  public function getAsset($id, $langcode) {
    $crc_id = $id;
    if (!$crc_id) {
      return FALSE;
    }

    $cid = $this->generateCacheId('ul_crc_api:asset', [$crc_id, $langcode]);
    $data = $this->cache->get($cid);
    // Return cached data if it exists.
    if (!empty($data->data)) {
      return $data->data;
    }

    // Problem for V3 query this; nothing returned in the View crc_asset_search.
    $request = $this->createRequest('collateral_assets/' . $crc_id, ['language' => $langcode]);
    $response = $request->execute();

    if ($response->isOk()) {
      $data = $response->getResponseDatabyId();
      // Set the cache data for this item.
      $this->setAsset($crc_id, $data, $langcode);
      return ['data' => $data];
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getUserById($ulid) {
    $user = FALSE;
    // Note: We don't cache this data.
    $params = [
      'user' => [
        'ul_id' => $ulid,
      ],
    ];
    $request = $this->createRequest('/asset_api/v3/user', $params);
    $request->setMethod('POST');
    $response = $request->execute();

    if ($response->isOk()) {
      $data = $response->getResponseData();

      if ($this->isValidUserData($data)) {
        $user = $data;
      }
    }

    return $user;
  }

  /**
   * Search for a specific user account with email.
   *
   * @param string $email
   *   The user's email address.
   *
   * @return array
   *   An array of user data.
   */
  public function getUserByEmail($email) {
    $user = FALSE;
    // Note: We don't cache this data.
    $params = [
      'user' => [
        'email' => $email,
      ],
    ];
    $request = $this->createRequest('/asset_api/v3/user', $params);
    $request->setMethod('POST');

    $response = $request->execute();

    if ($response->isOk()) {
      $data = $response->getResponseData();

      if ($this->isValidUserData($data)) {
        $user = $data;
      }
    }

    return $user;
  }

  /**
   * {@inheritdoc}
   */
  public function isConnected() {

    // Note: it does not matter what the ID is.
    $params = ['language' => 'en'];
    $request = $this->createRequest('collateral_assets/1', $params);
    $response = $request->execute();

    // If response is unauthorized return error.
    if ($response->getStatusCode() == 401) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Boolean check to see if user account data is valid or not.
   *
   * @param array $user_data
   *   Array of user data from the CRC.
   *
   * @return bool
   *   Boolean true or false.
   */
  private function isValidUserData(array $user_data) {
    $is_valid = TRUE;

    // Check if data has a 'valid' attribute and that it is set to false.
    if (isset($user_data['user']['valid']) && ($user_data['user']['valid'] == FALSE)) {
      $is_valid = FALSE;
    }

    return $is_valid;
  }

  /**
   * Get a CRC objct from DB table crc_asset.
   *
   * @param string $id
   *   The crc_id.
   * @param string $langcode
   *   The language code.
   *
   * @return Drupal\ul_crc_asset\Entity\CRCAsset
   *   An object of CRCAsset.
   */
  public function loadCrcAssetDb($id, $langcode) {
    // Query DB to get an array of Entity IDs.
    $database = \Drupal::database();
    // Check if the DB tables are different.
    $schema = $database->schema();
    $tbl_crc = 'crc_asset';
    $tbl_crc_data = 'crc_asset_field_data';

    // On Shimadzu site (D9), 2 DB tables are created,
    // crc_asset & crc_asset_field_data.
    if ($schema->tableExists($tbl_crc) && $schema->tableExists($tbl_crc_data)) {
      $query_table = $tbl_crc_data;
    }
    else {
      $query_table = $tbl_crc;
    }

    $query = $database->select($query_table, 'crc');
    $query->condition('status', 1)
      ->condition('langcode', $langcode)
      ->condition('crc_id', $id)
      ->fields('crc', ['id']);
    // Get the crc_asset entity id.
    if ($result = $query->execute()) {
      $record = $result->fetchAssoc();
      if (isset($record['id'])) {
        $entity = CRCAsset::load($record['id']);
        return $entity;
      }
    }
    return FALSE;
  }

  /**
   * Save the CRC asset data into DB table crc_asset.
   *
   * @param array $data
   *   The array of CRC asset Data from CRC API query.
   * @param string $user_id
   *   The User ID.
   * @param string $langcode
   *   The language code.
   *
   * @return Drupal\ul_crc_asset\Entity\CRCAsset
   *   An object of CRCAsset.
   */
  public function saveNewCrcAsset(array $data, $user_id, $langcode) {
    // Create the entity.
    if (!empty($data)) {
      $asset_entity = CRCAsset::create([
        'uid' => $user_id,
        'name' => $data['name'],
        'status' => 1,
        'langcode' => $langcode,
      ]);

      $asset_entity->setCrcId($data['id']);
      $asset_entity->setCrcLanguage(($langcode));

      try {
        $asset_entity->save();
      }
      catch (EntityStorageException $e) {
        \Drupal::logger('saveNewCrcAsset')->error($e);
      }
      return $asset_entity;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Save the CRC asset data into DB table crc_asset.
   *
   * @param array $data
   *   The array of CRC asset Data from CRC API query.
   * @param string $user_id
   *   The User ID.
   * @param string $langcode
   *   The language code.
   * @param string $uuid
   *   The uuid code from acquia_contenthub.
   *
   * @return Drupal\ul_crc_asset\Entity\CRCAsset
   *   An object of CRCAsset.
   */
  public function saveNewCrcAssetFromHub(array $data, $user_id, $langcode, $uuid) {
    if (empty($data) || !isset($uuid)) {
      return FALSE;
    }
    // Check if the crc_asset with $uuid is already exit.
    $asset_entity = \Drupal::service('entity.repository')->loadEntityByUuid('crc_asset', $uuid);
    if ($asset_entity) {
      return $asset_entity;
    }
    else {
      // Create the entity.
      $asset_entity = CRCAsset::create([
        'uuid' => $uuid,
        'uid' => $user_id,
        'name' => $data['name'],
        'status' => 1,
        'langcode' => $langcode,
      ]);
      $asset_entity->setCrcId($data['id']);
      $asset_entity->setCrcLanguage(($langcode));

      try {
        $asset_entity->save();
        $id = $asset_entity->id();
        $logStr = "CRC is saved: id=$id, uuid=$uuid,  crc_id=" . $data['id'];
        \Drupal::logger('CRC_saved_from_HUB')->notice($logStr);
      }
      catch (EntityStorageException $e) {
        \Drupal::logger('saveNewCrcAssetFromHub')->error($e);
      }
      return $asset_entity;
    }
  }

}

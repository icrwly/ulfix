<?php

namespace Drupal\ul_crc;

/**
 * Interface class for handling crc assets.
 */
interface CRCServiceInterface {

  /**
   * Search against CRC's Service API.
   *
   * @param mixed $search
   *   An array or string of search parameters.
   * @param int $page
   *   The page of the returned results.
   */
  public function search($search, $page = 1);

  /**
   * Fetch a single asset from CRC.
   *
   * @param int $id
   *   The unique asset id.
   * @param string $langcode
   *   The language code.
   */
  public function getAsset($id, $langcode);

  /**
   * Check if the connection is valid.
   *
   * @return bool
   *   Boolean value, true if connected false if not.
   */
  public function isConnected();

  /**
   * Search for a specific user account with email.
   *
   * @param string $ulid
   *   The user's UL id.
   *
   * @return array
   *   An array of user data.
   */
  public function getUserById($ulid);

  /**
   * Get a CRC objct from DB table crc_asset.
   *
   * @param string $id
   *   The entity id is also crc id.
   * @param string $langcode
   *   The language code.
   *
   * @return Drupal\ul_crc_asset\Entity\CRCAsset
   *   An object of CRCAsset.
   */
  public function loadCrcAssetDb($id, $langcode);

  /**
   * Save the CRC asset data into DB table crc_asset.
   *
   * @param array $data
   *   The array of CRC asset Data from CRC API query.
   * @param string $user_id
   *   The User ID.
   * @param string $langcode
   *   The langcode.
   *
   * @return Drupal\ul_crc_asset\Entity\CRCAsset
   *   An object of CRCAsset.
   */
  public function saveNewCrcAsset(array $data, $user_id, $langcode);

}

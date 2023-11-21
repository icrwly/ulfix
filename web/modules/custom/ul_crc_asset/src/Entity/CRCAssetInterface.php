<?php

namespace Drupal\ul_crc_asset\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining CRC Asset entities.
 *
 * @ingroup ul_crc_asset
 */
interface CRCAssetInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the CRC Asset name.
   *
   * @return string
   *   Name of the CRC Asset.
   */
  public function getName();

  /**
   * Sets the CRC Asset name.
   *
   * @param string $name
   *   The CRC Asset name.
   *
   * @return \Drupal\ul_crc_asset\Entity\CRCAssetInterface
   *   The called CRC Asset entity.
   */
  public function setName($name);

  /**
   * Gets the CRC Asset creation timestamp.
   *
   * @return int
   *   Creation timestamp of the CRC Asset.
   */
  public function getCreatedTime();

  /**
   * Sets the CRC Asset creation timestamp.
   *
   * @param int $timestamp
   *   The CRC Asset creation timestamp.
   *
   * @return \Drupal\ul_crc_asset\Entity\CRCAssetInterface
   *   The called CRC Asset entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the CRC Asset permanent status indicator.
   *
   * Unpublished CRC Asset are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the CRC Asset is published.
   */
  public function isPermanent();

  /**
   * Sets the permanent status of a CRC Asset.
   *
   * @return \Drupal\ul_crc_asset\Entity\CRCAssetInterface
   *   The called CRC Asset entity.
   */
  public function setPermanent();

  /**
   * Sets the temporary status of a CRC Asset.
   *
   * @return \Drupal\ul_crc_asset\Entity\CRCAssetInterface
   *   The called CRC Asset entity.
   */
  public function setTemporary();

  /**
   * Gets the CRC Asset langcode.
   *
   * @return string
   *   Name of the CRC Asset.
   */
  public function getLangcode();

  /**
   * Return CRC Service data about this asset.
   *
   * @param string $field
   *   The field name from CRC.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface|mixed
   *   Returns the value from the CRC service.
   */
  public function getCrcData($field);

  /**
   * Gets the CRC Asset ID.
   *
   * @return ini
   *   The CRC asset ID (crc_id).
   */
  public function getCrcId();

  /**
   * Sets the temporary status of a CRC Asset.
   *
   * @param int $crc_id
   *   The CRC Asset ID.
   *
   * @return int
   *   The CRC asset ID (crc_id).
   */
  public function setCrcId($crc_id);

  /**
   * Gets the CRC Asset langcode.
   *
   * @return string
   *   Langcode of Lanuage (crc_language).
   */
  public function getCrcLanguage();

  /**
   * Sets the langcode of a CRC Asset.
   *
   * @param int $crc_language
   *   The CRC Asset langcode of language.
   *
   * @return string
   *   Name of the CRC Asset.
   */
  public function setCrcLanguage($crc_language);

}

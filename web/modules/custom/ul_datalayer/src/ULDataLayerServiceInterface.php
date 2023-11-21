<?php

namespace Drupal\ul_datalayer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Interface ULDataLayerServiceInterface - Provides info to the DataLayer.
 *
 * @package Drupal\ul_datalayer
 */
interface ULDataLayerServiceInterface {

  /**
   * Retrieves an array of datalayer values for the current page.
   *
   * @return array
   *   Returns an associative array of datalayer values.
   */
  public function getDataLayer();

  /**
   * Retrieves an array of datalayer values for a specific user.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $user
   *   Drupal user object.
   *
   * @return array
   *   Returns an associative array of datalayer values specific to user.
   */
  public function getUserDataLayer(AccountProxyInterface $user);

  /**
   * Retrieves an array of datalayer values for a content entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Drupal content entity object.
   *
   * @return array
   *   Returns an associative array of datalayer values specific to entity.
   */
  public function getEntityDataLayer(ContentEntityInterface $entity);

}

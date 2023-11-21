<?php

namespace Drupal\ul_alerts;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\ul_alerts\Entity\AlertInterface;

/**
 * Defines the storage handler class for Alert entities.
 *
 * This extends the base storage class, adding required special handling for
 * Alert entities.
 *
 * @ingroup ul_alerts
 */
interface AlertStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Alert revision IDs for a specific Alert.
   *
   * @param \Drupal\ul_alerts\Entity\AlertInterface $entity
   *   The Alert entity.
   *
   * @return int[]
   *   Alert revision IDs (in ascending order).
   */
  public function revisionIds(AlertInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Alert author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Alert revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\ul_alerts\Entity\AlertInterface $entity
   *   The Alert entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(AlertInterface $entity);

  /**
   * Unsets the language for all Alert with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}

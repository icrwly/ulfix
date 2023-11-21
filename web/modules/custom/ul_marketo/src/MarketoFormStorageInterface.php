<?php

namespace Drupal\ul_marketo;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\ul_marketo\Entity\MarketoFormInterface;

/**
 * Defines the storage handler class for Marketo entities.
 *
 * This extends the base storage class, adding required special handling for
 * Marketo entities.
 *
 * @ingroup ul_marketo
 */
interface MarketoFormStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Marketo revision IDs for a specific Marketo entity.
   *
   * @param \Drupal\ul_marketo\Entity\MarketoFormInterface $entity
   *   The Marketo entity.
   *
   * @return int[]
   *   Marketo revision IDs (in ascending order).
   */
  public function revisionIds(MarketoFormInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Marketo author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Marketo revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\ul_marketo\Entity\MarketoFormInterface $entity
   *   The Marketo entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(MarketoFormInterface $entity);

  /**
   * Unsets the language for all Marketo entities with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}

<?php

namespace Drupal\ul_marketo;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\ul_marketo\Entity\MarketoFormInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for Marketo entities.
 *
 * This extends the base storage class, adding required special handling for
 * Marketo entities.
 *
 * @ingroup ul_marketo
 */
class MarketoFormStorage extends SqlContentEntityStorage implements MarketoFormStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(MarketoFormInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {marketo_form_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {marketo_form_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(MarketoFormInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {marketo_form_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('marketo_form_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}

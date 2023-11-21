<?php

namespace Drupal\ul_alerts;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class AlertStorage extends SqlContentEntityStorage implements AlertStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(AlertInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {ul_alert_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {ul_alert_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(AlertInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {ul_alert_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('ul_alert_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}

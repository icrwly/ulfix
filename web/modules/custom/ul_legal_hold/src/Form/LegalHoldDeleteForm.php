<?php

namespace Drupal\ul_legal_hold\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Url;

/**
 * Provides a form for deleting Legal Hold entities.
 *
 * @ingroup ul_legal_hold
 */
class LegalHoldDeleteForm extends ContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.ul_legal_hold.content', ['node' => $this->entity->getHeldContentId()]);
  }

  /**
   * {@inheritdoc}
   */
  protected function getRedirectUrl() {
    return $this->getCancelUrl();
  }

  /**
   * {@inheritdoc}
   */
  protected function getDeletionMessage() {
    return $this->t('The Legal Hold %label has been deleted.', [
      '%label' => $this->entity->getTitle(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this->getEntity();

    if (!$entity->isDefaultTranslation()) {
      return $this->t('Are you sure you want to delete the @language translation of the @entity-type %label?', [
        '@language' => $entity->language()->getName(),
        '@entity-type' => $this->getEntity()->getEntityType()->getSingularLabel(),
        '%label' => $this->getEntity()->label(),
      ]);
    }

    return $this->t('Are you sure you want to delete the legal hold %label?', [
      '@entity-type' => $this->getEntity()->getEntityType()->getSingularLabel(),
      '%label' => $this->getEntity()->getTitle(),
    ]);
  }

}

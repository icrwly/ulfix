<?php

namespace Drupal\ul_alerts\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Alert edit forms.
 *
 * @ingroup ul_alerts
 */
class AlertForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    // Save as a new revision if requested to do so.
    if (!$form_state->isValueEmpty('revision') && $form_state->getValue('revision') != FALSE) {
      $entity->setNewRevision();

      // If a new revision is created, save the current user as revision author.
      $request_time = \Drupal::time()->getRequestTime();
      $entity->setRevisionCreationTime($request_time);
      $entity->setRevisionUserId($this->currentUser()->id());
    }
    else {
      $entity->setNewRevision(FALSE);
    }

    $status = parent::save($form, $form_state);

    $str_save_create = "";
    switch ($status) {
      case SAVED_NEW:
        $str_save_create = "Created";
        break;

      default:
        $str_save_create = "Saved";
    }

    \Drupal::messenger()->addMessage($this->t('%save the %label Alert.', [
      '%save' => $str_save_create,
      '%label' => $entity->label(),
    ]));

    $form_state->setRedirect('entity.ul_alert.collection');
  }

}

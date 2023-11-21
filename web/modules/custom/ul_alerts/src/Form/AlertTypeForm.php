<?php

namespace Drupal\ul_alerts\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form for managing Alert types.
 */
class AlertTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $ul_alert_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $ul_alert_type->label(),
      '#description' => $this->t("The human-readable name of this alert type. This text will be displayed as part of the list on the Add alert page. This name must be unique."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $ul_alert_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\ul_alerts\Entity\AlertType::load',
      ],
      '#disabled' => !$ul_alert_type->isNew(),
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#maxlength' => 255,
      '#default_value' => $ul_alert_type->getDescription(),
      '#description' => $this->t("This text will be displayed on the Add new alert page."),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $ul_alert_type = $this->entity;
    $status = $ul_alert_type->save();
    $str_save_create = "";
    switch ($status) {
      case SAVED_NEW:
        $str_save_create = "Created";
        break;

      default:
        $str_save_create = "Saved";
    }

    \Drupal::messenger()->addMessage($this->t('%save the %label Alert type.', [
      '%save' => $str_save_create,
      '%label' => $ul_alert_type->label(),
    ]));

    $form_state->setRedirectUrl($ul_alert_type->toUrl('collection'));
  }

}

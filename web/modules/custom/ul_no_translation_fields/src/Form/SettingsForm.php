<?php

namespace Drupal\ul_no_translation_fields\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure UL No Translation Fields settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ul_no_translation_fields_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ul_no_translation_fields.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['fields'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Field ID names excluding from Translations'),
      '#default_value' => $this->config('ul_no_translation_fields.settings')->get('fields'),
      '#description' => $this->t('For example, field_shared_review_comments, using a comma "," to separate items in a list.'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $fields = $form_state->getValue('fields');
    if (empty($fields)) {
      return TRUE;
    }
    $fields_arr = explode(",", $fields);
    $error = [];

    $entityTypeManager = \Drupal::service('entity_type.manager');
    $contentTypes = $entityTypeManager->getStorage('node_type')->loadMultiple();

    foreach ($fields_arr as $field_name) {
      if (!$this->isValideField(trim($field_name), $contentTypes)) {
        $error[] = $field_name;
      }
    }

    if (!empty($error)) {
      $str = "Not valid field(s): " . implode(",", $error) . ".";
      $form_state->setErrorByName('fields', $str);
    }
    else {
      return TRUE;
    }

  }

  /**
   * Check if a field is valid.
   *
   * @param string $field_name
   *   The field name.
   * @param array $contentTypes
   *   The field name.
   *
   * @return bool
   *   TRUE of FALSE.
   */
  protected function isValideField($field_name, array $contentTypes) {
    $valid = FALSE;
    foreach ($contentTypes as $contentType) {
      $type = $contentType->id();
      $definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', $type);

      if (isset($definitions[$field_name])) {
        $valid = TRUE;
        break;
      }
    }
    return $valid;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('ul_no_translation_fields.settings')
      ->set('fields', $form_state->getValue('fields'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}

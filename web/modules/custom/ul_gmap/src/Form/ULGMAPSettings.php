<?php

namespace Drupal\ul_gmap\Form;

use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures forms module settings.
 */
class ULGMAPSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ul_gmap_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ul_gmap.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $config = $this->config('ul_gmap.settings');

    $form["#attributes"]["autocomplete"] = "off";

    $form['ul_gmap'] = [
      '#type'  => 'fieldset',
      '#title' => $this->t('UL Global Market Access Settings'),
    ];

    // Not Sure URL Field.
    $form['ul_gmap']['page_uuid'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Page UUID'),
      '#default_value' => $config->get('ul_gmap.page_uuid'),
      '#description'   => $this->t('UUID for Info Page'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $config = $this->config('ul_gmap.settings');
    $state  = \Drupal::state();

    $config->set('ul_gmap.page_uuid', $values['page_uuid']);

    $config->save();
  }

}

<?php

namespace Drupal\ul_json\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure ul_json settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ul_json_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ul_json.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $token = 'ULSOLUTIONS-POWERBI--T-Vp8YwxR5B2h5zpRseC398tQN-uXBCqbo4uIyRE2mAbQYuhvY-FXz4pSRevbPVSVd9MoVp8YwxR5B2h5zpR-seC398tQNuXBCqbo4ui5R5B8tuP2kiiyDdI';

    $form['access_token'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Access Token'),
      '#default_value' => "*******************************************",
      '#description' => $this->t('Set a random "Access Token" <b>(length >= 100)
        and securely store it</b>,<br>
      such as: :token',
        [':token' => $token]),
    ];

    $default_types = $this->config('ul_json.settings')->get('content_types');
    if (empty($default_types)) {
      $default_types = "campaign_page, event, market_access_profile, knowledge,landing_page, news, offering,resource,tool";
    }

    $form['content_types'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Node content types for the exporting of JSON data intended for the Newsletter/Email'),
      '#default_value' => $default_types,
      '#description' => $this->t('Please set up node conent types specifically for Newsletter/Email JSON data only. Use the commma ( , ) as the delimiter.'),
    ];

    $default_types_2 = $this->config('ul_json.settings')->get('content_types_china');
    if (empty($default_types_2)) {
      $default_types_2 = "campaign_page,event,knowledge,landing_page,news,offering,resource";
    }
    $form['content_types_china'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Node content types for the exporting of Feed in Chinese intended for the China team'),
      '#default_value' => $default_types_2,
      '#description' => $this->t('Please set up node conent types specifically for China site migration data only. Use the commma ( , ) as the delimiter.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $token = $form_state->getValue('access_token');
    if (strlen($token) < 100 && !stristr($token, '************')) {
      $form_state->setErrorByName('access_token', $this->t('The length of string must be greater than 100.'));
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $token = strip_tags($form_state->getValue('access_token'));
    if (!stristr($token, '*************')) {
      $hash = md5($form_state->getValue('access_token'));
      $this->config('ul_json.settings')
        ->set('access_token', $hash)
        ->save();
    }

    $types = strip_tags($form_state->getValue('content_types'));
    if (strlen($types) > 4) {
      $this->config('ul_json.settings')
        ->set('content_types', $types)
        ->save();
    }

    $types = strip_tags($form_state->getValue('content_types_china'));
    if (strlen($types) > 4) {
      $this->config('ul_json.settings')
        ->set('content_types_china', $types)
        ->save();
    }

    parent::submitForm($form, $form_state);
  }

}

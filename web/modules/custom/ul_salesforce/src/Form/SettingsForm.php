<?php

namespace Drupal\ul_salesforce\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Salesforce module settings form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ul_salesforce.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ul_salesforce_settings_form';
  }

  /**
   * Salesforce environments.
   *
   * @var environments
   */
  protected $environments = [
    'dev',
    'sit',
    'uat',
    'prod',
  ];

  /**
   * Custom Web-To-Case Fields.
   *
   * @var custom_fields
   */
  protected $custom_fields = [
    'language',
    'web_country',
    'web_inquiry_type',
  ];

  /**
   * Get options for the environment fields.
   *
   * @return array
   *   Array of env options.
   */
  public function getEnvironmentOptions() {
    $options = [];
    foreach ($this->environments as $env) {
      $options[$env] = $env;
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ul_salesforce.settings');

    // We are using config split to store the Salesforce environment
    // separate from the rest of the settings.
    $environment_config = $this->config('ul_salesforce.salesforce_environment');

    $form['environment_wrapper'] = [
      '#type' => 'details',
      '#title' => $this->t("Selected Salesforce Environment"),
    ];

    $form['environment_wrapper']['salesforce_environment'] = [
      '#title' => 'Salesforce Environment',
      '#type' => 'select',
      '#options' => $this->getEnvironmentOptions(),
      '#description' => $this->t('Web-To-Case submissions will be posted to this environment.'),
      '#default_value' => NULL !== $environment_config->get('salesforce_environment') ? $environment_config->get('salesforce_environment') : 'prod',
    ];

    $form['environment'] = [
      '#type' => 'details',
      '#title' => $this->t("Salesforce Environments"),
      '#tree' => TRUE,
    ];

    foreach ($this->environments as $env) {
      $form['environment'][$env] = [
        '#type' => 'fieldset',
        '#title' => $env,
      ];

      $form['environment'][$env]['url'] = [
        '#type' => 'textfield',
        '#title' => $this->t('URL'),
        '#description' => $this->t('Base Salesforce @env url.', ['@env' => $env]),
        '#default_value' => array_key_exists($env, $config->get('environment')) ? $config->get('environment')[$env]['url'] : '',
      ];

      $form['environment'][$env]['orgid'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Org ID'),
        '#description' => $this->t('Salesforce @env orgid.', ['@env' => $env]),
        '#default_value' => array_key_exists($env, $config->get('environment')) ? $config->get('environment')[$env]['orgid'] : '',
      ];

      $form['environment'][$env]['confirmation_url'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Confirmation Url'),
        '#description' => $this->t('Redirect user to this url after web-to-case submission'),
        '#default_value' => array_key_exists($env, $config->get('environment')) ? $config->get('environment')[$env]['confirmation_url'] : '',
      ];

    }

    $form['web_to_case'] = [
      '#type' => 'details',
      '#title' => $this->t('Web-to-Case'),
    ];

    $form['web_to_case']['web_to_case_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Web-to-Case Path'),
      '#description' => $this->t('The path component of the url for web-to-case submissions.'),
      '#default_value' => NULL !== $config->get('web_to_case_path') ? $config->get('web_to_case_path') : '/servlet/servlet.WebToCase?encoding=UTF-8',
    ];

    // Debug.
    $form['web_to_case']['debug'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Debug'),
      '#description' => $this->t('Enable debug mode for web-to-case submissions'),
      '#default_value' => $config->get('debug'),
    ];

    // debugEmail.
    $form['web_to_case']['debugEmail'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Debug Email'),
      '#description' => $this->t('Web-to-case debugging info will be sent to this email.'),
      '#default_value' => $config->get('debugEmail'),
    ];

    $form['web_to_case']['recaptcha_site_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Recaptcha Site Key'),
      '#description' => $this->t('Recaptcha Site Key for Web-to-case posts.'),
      '#default_value' => $config->get('recaptcha_site_key'),
    ];

    $form['web_to_case']['custom_fields'] = [
      '#type' => 'details',
      '#title' => $this->t('Custom Fields'),
      '#description' => $this->t('Custom fields that will have different field IDs accross Salesforce environments.'),
      '#tree' => TRUE,
    ];

    foreach ($this->custom_fields as $field) {
      $form['web_to_case']['custom_fields'][$field] = [
        '#type' => 'fieldset',
        '#title' => $field,
      ];

      foreach ($this->environments as $env) {

        $default_val = '';

        if (isset($config->get('custom_fields')[$field])) {
          if (array_key_exists($env, $config->get('custom_fields')[$field])) {
            $default_val = $config->get('custom_fields')[$field][$env];
          }
        }

        $form['web_to_case']['custom_fields'][$field][$env] = [
          '#type' => 'textfield',
          '#title' => $env,
          '#default_value' => $default_val,
        ];
      }
    }

    $form['web_to_case']['record_type'] = [
      '#type' => 'details',
      '#title' => $this->t('Record Type'),
      '#description' => $this->t("recordType is a standard web-to-case field but the option values have different ids across different environments. We need to send a hidden 'recordType' field with the correct value for the 'Customer Support' option."),
      '#tree' => TRUE,
    ];

    foreach ($this->environments as $env) {
      $form['web_to_case']['record_type'][$env] = [
        '#type' => 'textfield',
        '#title' => $this->t('Customer Support - @env', ['@env' => $env]),
        '#default_value' => array_key_exists($env, $config->get('record_type')) ? (NULL !== $config->get('record_type')[$env] ? $config->get('record_type')[$env] : '') : '',
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('ul_salesforce.settings')
      ->set('environment', $form_state->getValue('environment'))
      ->set('web_to_case_path', $form_state->getValue('web_to_case_path'))
      ->set('debug', $form_state->getValue('debug'))
      ->set('debugEmail', $form_state->getValue('debugEmail'))
      ->set('recaptcha_site_key', $form_state->getValue('recaptcha_site_key'))
      ->set('custom_fields', $form_state->getValue('custom_fields'))
      ->set('record_type', $form_state->getValue('record_type'))
      ->save();

    \Drupal::configFactory()->getEditable('ul_salesforce.salesforce_environment')
      ->set('salesforce_environment', $form_state->getValue('salesforce_environment'))
      ->save();

  }

}

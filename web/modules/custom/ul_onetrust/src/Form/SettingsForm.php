<?php

namespace Drupal\ul_onetrust\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ul_onetrust\OneTrustServiceInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Settings Form for OneTrust privacy management.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * GuzzleHttp Client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * OneTrustService object.
   *
   * @var \Drupal\ul_onetrust\OneTrustServiceInterface
   */
  protected $onetrust;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    return new static(
      $container->get('config.factory'),
      $container->get('ul_onetrust'),
      $container->get('http_client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, OneTrustServiceInterface $one_trust, Client $http_client) {
    parent::__construct($config_factory);
    $this->onetrust = $one_trust;
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ul_onetrust.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ul_onetrust_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // One Trust settings:
    $config = $this->config('ul_onetrust.settings');

    // Saved Data domain script value:
    $data_domain_script = str_replace('-test', '', $config->get('data_domain_script'));

    // This is a way to preserve deprecated field, "data_domain_script_other".
    if ($data_domain_script === 'other') {
      $data_domain_script = str_replace('-test', '', $form_state->getValue('data_domain_script_other'));
    }

    // Data Domain Script - NEW:
    $data_domain_script_new = $config->get('data_domain_script_new');

    // Data Domain Script Options:
    $data_domain_script_options = [
      // Default: WWW, Canada, Brandhub.
      'b060a578-9830-448d-9d34-2419f2b5d3cb' => $this->t('Default (b060a578-9830-448d-9d34-2419f2b5d3cb)'),
      // Latam.
      'a409f37b-55b7-4cf8-8713-a21f7a5dbbe9' => $this->t('LATAM (a409f37b-55b7-4cf8-8713-a21f7a5dbbe9)'),
      // AU-NZ.
      'e550a7f6-db09-4c03-a3db-ac0b9c32c5db' => $this->t('AU-NZ (e550a7f6-db09-4c03-a3db-ac0b9c32c5db)'),
      // Shimadzu.
      '6de997f6-4f25-49d6-b587-b93992b3722a' => $this->t('Shimadzu (6de997f6-4f25-49d6-b587-b93992b3722a)'),
      // Emergo.
      'd5689838-8dbf-4c8d-a8c8-583228b17be1' => $this->t('Emergo (d5689838-8dbf-4c8d-a8c8-583228b17be1)'),
      // Other.
      'new' => $this->t('Other'),
    ];

    // Use the "default" value when no value:
    if (!isset($data_domain_script_options[$data_domain_script])) {
      $data_domain_script = 'b060a578-9830-448d-9d34-2419f2b5d3cb';
    }

    // The form object:
    $form['cookie_banner'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Cookie Banner'),
    ];
    // This allows module to be installed without serving the cookie banner.
    $form['cookie_banner']['consent_notice_enable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Cookie Banner'),
      '#description' => $this->t('Display the cookie consent banner on the site.'),
      '#default_value' => $config->get('consent_notice_enable'),
    ];
    // This is hard-coded and readonly, because it should not change.
    $form['cookie_banner']['script_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Script URL (read only)'),
      '#description' => $this->t('The script URL cannot be edited.'),
      '#size' => '60',
      '#attributes' => [
        'readonly' => 'readonly',
      ],
      '#default_value' => 'https://cdn.cookielaw.org/scripttemplates/otSDKStub.js',
    ];
    // This changes per site.
    $form['cookie_banner']['data_domain_script'] = [
      '#required' => TRUE,
      '#type' => 'select',
      '#title' => $this->t('Data Domain Script'),
      '#description' => $this->t('The data domain script for this site.'),
      '#options' => $data_domain_script_options,
      '#default_value' => $data_domain_script,
    ];

    // Only shows when the option 'other' is selected above.
    $form['cookie_banner']['data_domain_script_new'] = [
      '#type' => 'textfield',
      '#size' => '60',
      '#title' => $this->t('Data Domain Script - New'),
      '#description' => $this->t('Enter a new data domain script (if it is not an option above).'),
      '#default_value' => $data_domain_script_new,
      '#states' => [
        // Only show this if the selected value above is 'other'.
        'visible' => [
          ':input[name="data_domain_script"]' => ['value' => 'new'],
        ],
      ],
    ];

    // NOT FOR PROD!
    if (!isset($_ENV['AH_PRODUCTION']) || empty($_ENV['AH_PRODUCTION'])) {
      // This allows users to select the testing script:
      $form['cookie_banner']['use_testing_script'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Use Testing Script (not for prod!)'),
        '#description' => $this->t('Use the testing script in the lower environments.'),
        '#default_value' => $config->get('use_testing_script'),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // We need to make sure the the ID is a valid javascript file.
    // Perform an http request to ensure that it doesn't return a 404 error.
    $script_url = $form_state->getValue('script_url');
    try {
      $this->httpClient->get($script_url);
    }
    catch (RequestException $e) {
      $form_state->setErrorByName('script_url', $this->t('The Script URL entered is not valid and could not be saved.'));
    }
    // @todo Change the autogenerated stub.
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('ul_onetrust.settings')
      ->set('consent_notice_enable', $form_state->getValue('consent_notice_enable'))
      ->set('script_url', 'https://cdn.cookielaw.org/scripttemplates/otSDKStub.js')
      ->set('data_domain_script', $form_state->getValue('data_domain_script'))
      ->set('data_domain_script_new', $form_state->getValue('data_domain_script_new'))
      ->set('use_testing_script', $form_state->getValue('use_testing_script'))
      ->save();
  }

}

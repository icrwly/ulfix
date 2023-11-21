<?php

namespace Drupal\ul_trustarc\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ul_trustarc\TrustArcServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Settings Form for TrustArc privacy management.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Module Service object.
   *
   * @var \Drupal\ul_trustarc\TrustArcServiceInterface
   */
  protected $trustarc;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    return new static(
      $container->get('config.factory'),
      $container->get('ul_trustarc')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, TrustArcServiceInterface $trust_arc) {
    parent::__construct($config_factory);
    $this->trustarc = $trust_arc;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ul_trustarc.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ul_trustarc_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Hard-coded values:
    $script_url = '//consent.trustarc.com/notice';
    $target_element = 'teconsent';

    // Module settings:
    $config = $this->config('ul_trustarc.settings');

    // Values stored in DB / config settings:
    $module_enabled = $config->get('module_enable');
    $domain = $config->get('domain');
    $country = $config->get('country');
    $language = $config->get('language');
    $behavior = $config->get('behavior');
    $privacy_policy_url = $config->get('privacy_policy_url');

    // Country options:
    $countries = [
      '' => '-- Select one --',
      'AD' => 'Andorra',
      'AR' => 'Argentina',
      'AT' => 'Austria',
      'AU' => 'Australia',
      'BE' => 'Belgium',
      'BR' => 'Brazil',
      'CA' => 'Canada',
      'CN' => 'China',
      'FR' => 'France',
      'DE' => 'Germany',
      'JP' => 'Japan',
      'KP' => 'Korea (North)',
      'KR' => 'Korea (South)',
      'MX' => 'Mexico',
      'MC' => 'Monaco',
      'ME' => 'Montenegro',
      'PH' => 'Philippines',
      'RU' => 'Russian Federation',
      'SM' => 'San Marino',
      'SA' => 'Saudia Arabia',
      'SG' => 'Singapore',
      'ZA' => 'South Africa',
      'ES' => 'Spain',
      'CH' => 'Switzerland',
      'TW' => 'Taiwan',
      'GB' => 'United Kingdom',
      'US' => 'United States of America',
      'UY' => 'Uruguay',
      'VN' => 'Vietnam',
    ];

    // Language options:
    $languages = [
      '' => '-- Select one --',
      'zh-cn' => 'Chinese, simplified',
      'zh-tw' => 'Chinese, traditional',
      'de' => 'German',
      'en' => 'English',
      'fr-ca' => 'French (CA)',
      'it' => 'Italian',
      'ja' => 'Japanese',
      'ko' => 'Korean',
      'pt-br' => 'Portuguese (BR)',
      'es' => 'Spanish',
      'vi' => 'Vietnamese',
    ];

    // Behavior options:
    $behaviors = [
      '' => '-- Select one --',
      'expressed' => 'Expressed',
      'implied' => 'Implied',
    ];

    // Begin building the form:
    // The form object:
    $form['cookie_banner'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Cookie Banner'),
    ];

    // Enable/disable module.
    $form['cookie_banner']['module_enable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Cookie Banner'),
      '#description' => $this->t('Display the cookie consent banner on the site.'),
      '#default_value' => $module_enabled,
    ];

    // The script domain.
    $form['cookie_banner']['domain'] = [
      '#required' => TRUE,
      '#type' => 'textfield',
      '#title' => $this->t('Script Domain'),
      '#description' => $this->t('The script domain for this site.'),
      '#size' => '60',
      '#default_value' => $domain,
    ];

    // Cookie banner target element (div ID).
    // Read only!
    $form['cookie_banner']['target_element'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cookie banner target div (read only)'),
      '#description' => $this->t('The target div&rsquo;s ID.'),
      '#size' => '60',
      '#attributes' => [
        'readonly' => 'readonly',
      ],
      '#default_value' => $target_element,
    ];

    // The script URL.
    // Read only!
    $form['cookie_banner']['script_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Script URL (read only)'),
      '#description' => $this->t('The script URL cannot be edited.'),
      '#size' => '60',
      '#attributes' => [
        'readonly' => 'readonly',
      ],
      '#default_value' => $script_url,
    ];

    // Privacy Policy URL.
    $form['cookie_banner']['privacy_policy_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Privacy Policy URL'),
      '#description' => $this->t('Enter the privacy policy URL where the Consent Manager widget should link.'),
      '#size' => '60',
      '#default_value' => $privacy_policy_url,
    ];

    // TESTING SCRIPT PARAMETERS BELOW: NOT FOR PROD!
    // ============================================= //.
    $form['cookie_banner']['testing_options'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Testing Options: Not for Prod!'),
    ];

    // Country.
    $form['cookie_banner']['testing_options']['country'] = [
      '#type' => 'select',
      '#title' => $this->t('Country'),
      '#description' => $this->t('Override the dynamic IP detection. Value must be a 2 letter ISO country code.'),
      '#options' => $countries,
      '#default_value' => $country,
    ];

    // Language.
    $form['cookie_banner']['testing_options']['language'] = [
      '#type' => 'select',
      '#title' => $this->t('Language'),
      '#description' => $this->t('Override the dynamic browser language detection. Value must be a 2 letter ISO language code.'),
      '#options' => $languages,
      '#default_value' => $language,
    ];

    // Behavior.
    $form['cookie_banner']['testing_options']['behavior'] = [
      '#type' => 'select',
      '#title' => $this->t('Behavior'),
      '#description' => $this->t('Enter a value of `expressed` or `implied` to force behavior for the consent manager.'),
      '#options' => $behaviors,
      '#default_value' => $behavior,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Intentionally left blank.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('ul_trustarc.settings')
      ->set('module_enable', $form_state->getValue('module_enable'))
      ->set('script_url', $form_state->getValue('script_url'))
      ->set('domain', $form_state->getValue('domain'))
      ->set('target_element', $form_state->getValue('target_element'))
      ->set('country', $form_state->getValue('country'))
      ->set('language', $form_state->getValue('language'))
      ->set('behavior', $form_state->getValue('behavior'))
      ->set('privacy_policy_url', $form_state->getValue('privacy_policy_url'))
      ->save();
  }

}

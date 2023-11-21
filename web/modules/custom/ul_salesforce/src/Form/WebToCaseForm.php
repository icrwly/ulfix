<?php

namespace Drupal\ul_salesforce\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\Client;
use Drupal\Core\Routing\TrustedRedirectResponse;

/**
 * WebToCase base form.
 */
class WebToCaseForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ul_salesforce_web_to_case_form';
  }

  /**
   * Salesforce Configuration.
   *
   * @var salesforce_config
   */
  protected $salesforce_config;

  /**
   * Selected Salesforce Environment.
   *
   * @var env
   */
  protected $env;

  /**
   * Orgid for selected Salesforce environment.
   *
   * @var orgid
   */
  protected $orgid;

  /**
   * Url for web-to-case submissions.
   *
   * @var web_to_case_url
   */
  protected $web_to_case_url;

  /**
   * Boolean value to submit web-to-case in debug mode.
   *
   * @var debug
   */
  protected $debug;

  /**
   * Email address to send debugging info to.
   *
   * @var debugEmail
   */
  protected $debugEmail;

  /**
   * The confirmation URL for the selected Salesforce environment.
   *
   * @var confirmation_url
   */
  protected $confirmation_url;

  /**
   * The site key to use for recaptcha.
   *
   * @var recaptcha_site_key
   */
  protected $recaptcha_site_key;

  /**
   * Custom field ids for salesforce environment.
   *
   * @var custom_fields
   */
  protected $custom_fields;

  /**
   * Hidden recordType Customer Support value.
   *
   * @var record_type
   */
  protected $record_type;

  /**
   * The current language.
   *
   * @var current_lanaguage
   */
  protected $current_language;

  /**
   * The title to display at the top of the form.
   *
   * @var title
   */
  protected $title = 'Web To Case Form';

  /**
   * Country options by language.
   *
   * @var country_options_by_language
   */
  protected $country_options_by_language;

  /**
   * Country options for the current language.
   *
   * @var country_options
   */
  protected $country_options;

  /**
   * Language options by language.
   *
   * @var language_options_by_language
   */
  protected $language_options_by_language;

  /**
   * Language options for the current language.
   *
   * @var language_options
   */
  protected $language_options;

  /**
   * Regions options for the region web-to-case field.
   *
   * @var region_options
   */
  protected $region_options = [
    'Taiwan' => 'Taiwan',
    'Japan' => 'Japan',
    'Latin America' => 'Latin America',
    'United States' => 'United States',
    'Europe South' => 'Europe South',
    'Europe Central/East' => 'Europe Central/East',
    'Europe North' => 'Europe North',
    'Korea' => 'Korea',
    'ASEAN ANZ' => 'ASEAN ANZ',
    'Canada' => 'Canada',
    'MEA' => 'MEA',
  ];

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->salesforce_config = $this->config('ul_salesforce.settings');
    $this->env = $this->config('ul_salesforce.salesforce_environment')->get('salesforce_environment');
    $this->orgid = $this->salesforce_config->get('environment')[$this->env]['orgid'];
    $this->web_to_case_url = $this->salesforce_config->get('environment')[$this->env]['url'] . $this->salesforce_config->get('web_to_case_path');
    $this->confirmation_url = $this->salesforce_config->get('environment')[$this->env]['confirmation_url'];
    $this->custom_fields = $this->salesforce_config->get('custom_fields');
    $this->record_type = $this->salesforce_config->get('record_type')[$this->env];

    $this->debug = $this->salesforce_config->get('debug');
    $this->debugEmail = $this->salesforce_config->get('debugEmail');
    $this->recaptcha_site_key = $this->salesforce_config->get('recaptcha_site_key');
    $this->current_language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $this->country_options_by_language = $this->config('ul_salesforce.country_options_by_language')->getRawData();
    $this->country_options = $this->country_options_by_language[$this->current_language];
    $this->language_options_by_language = $this->config('ul_salesforce.language_options_by_language')->getRawData();
    $this->language_options = $this->language_options_by_language[$this->current_language];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Attach needed library items.
    $form['#attached']['library'][] = 'ul_salesforce/ul_salesforce.web_to_case';

    // Attached drupalSettings variables.
    $form['#attached']['drupalSettings']['ulSalesforce'] = \Drupal::config('ul_salesforce.settings')->getRawData();

    // Add the current language to drupalSettings variables so
    // that recaptcha api can be called with correct language.
    $form['#attached']['drupalSettings']['ulSalesforce']['lang'] = $this->current_language;

    // Orgid.
    $form['orgid'] = [
      '#type' => 'hidden',
      '#value' => $this->orgid,
    ];

    // retURL.
    $form['retURL'] = [
      '#type' => 'hidden',
      '#value' => $this->getConfirmationUrl(),
    ];

    // Debug.
    $form['debug'] = [
      '#type' => 'hidden',
      '#value' => $this->debug,
    ];

    // debugEmail.
    $form['debugEmail'] = [
      '#type' => 'hidden',
      '#value' => $this->debugEmail,
    ];

    // recordType.
    $form['recordType'] = [
      '#type' => 'hidden',
      '#value' => $this->record_type,
    ];

    $form['recaptcha_widget'] = [
      '#type' => 'inline_template',
      '#template' => '<div id="sf_recaptcha" class="g-recaptcha" data-sitekey="{{ recaptcha_site_key }}"></div>',
      '#context' => [
        'form_id' => $this->getFormId(),
        'recaptcha_site_key' => $this->recaptcha_site_key,
      ],
      '#weight' => 999,
      '#prefix' => '<div class="button-container">',
    ];

    $form['hiddenRecaptcha'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'class' => [
          'hiddenRecaptcha',
          'required',
        ],
      ],
      '#weight' => 999,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#attributes' => [
        'class' => [
          'mktoButton',
          'button',
          'button--red',
        ],
        'id' => 'form-submit',
      ],
      '#weight' => 1000,
      '#suffix' => '</div>',
    ];

    $form['#method'] = 'post';
    $form['#action'] = $this->web_to_case_url;
    $form['#attributes']['data-recaptcha-form'] = 'true';
    $form['#attributes']['class'][] = 'web-to-case-form';
    $form['#theme'] = 'web_to_case_form';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // This will not get called when we set the action of the form to point
    // to web_to_case_url. This is an example of how guzzle can be used for
    // the form post to web-to-case.
    $values = $form_state->getValues();
    $client = new Client();
    $form_params = [];
    foreach ($values as $key => $value) {
      $form_params[$key] = $value;
    }

    $response = $client->request('POST', $this->web_to_case_url, ['form_params' => $form_params]);

    if ($response->getStatusCode() == 200) {
      $form_state->setResponse(new TrustedRedirectResponse($this->confirmation_url, 302));
    }
  }

  /**
   * Include language in return URL to get properly translated thankyou page.
   */
  public function getConfirmationUrl() {
    $url = $this->confirmation_url;

    // If the current language is not english we need to include the
    // language code in the url.
    if (is_string($url) && $this->current_language != 'en') {
      $parts = explode("/", $url);
      $url = $parts[0] . "//" . $parts[2] . "/" . $this->current_language . "/" . implode("/", array_splice($parts, 3));
    }

    // UL-4799.
    // Including the referrer in the return url will be used in the
    // future to support the back to website link.
    /*
    $referrer = \Drupal::request()->query->get('referrer');
    if (!empty($referrer)) {
    $url .= '?referrer=' .  urlencode($referrer);
    }
     */

    return $url;
  }

}

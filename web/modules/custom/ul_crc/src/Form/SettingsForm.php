<?php

namespace Drupal\ul_crc\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Module settings form for ul_crc.
 */
class SettingsForm extends ConfigFormBase {


  /**
   * DateFormatter object.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  private $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    return new static(
      $container->get('config.factory'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, $date_formatter) {
    parent::__construct($config_factory);
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ul_crc.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ul_crc_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ul_crc.settings');
    $form['auth_token'] = [
      '#required' => TRUE,
      '#type' => 'textfield',
      '#title' => $this->t('Authentication Token'),
      '#description' => $this->t('Provide a valid authentication token.'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => $config->get('auth_token'),
    ];

    $options = [3600, 10800, 21600, 43200, 86400, 604800];
    $form['cache_response_interval'] = [
      '#type' => 'select',
      '#title' => $this->t('Cache search results for'),
      '#options' => [0 => $this->t('Never')] + array_map([
        $this->dateFormatter, 'formatInterval',
      ], array_combine($options, $options)),
      '#description' => $this->t('Cache the search results response from CRC.'),
      '#default_value' => $config->get('cache_response_interval'),
    ];
    $form['environment'] = [
      '#type' => 'radios',
      '#title' => $this->t('Environment'),
      '#description' => $this->t('Choose an environment to connect to.'),
      '#options' => [
        'Staging' => $this->t('Staging'),
        'Preproduction' => $this->t('Preproduction'),
        'Production' => $this->t('Production'),
      ],
      '#default_value' => $config->get('environment'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('ul_crc.settings')
      ->set('auth_token', $form_state->getValue('auth_token'))
      ->set('cache_response_interval', $form_state->getValue('cache_response_interval'))
      ->set('environment', $form_state->getValue('environment'))
      ->save();
  }

}

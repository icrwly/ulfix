<?php

namespace Drupal\ul_marketo\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ul_marketo\UlMarketoServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Marketo module settings form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * UlMarketo Service.
   *
   * @var \Drupal\ul_marketo\UlMarketoService
   */
  protected $marketo;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    return new static(
      $container->get('config.factory'),
      $container->get('ul_marketo')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, UlMarketoServiceInterface $ul_marketo) {
    parent::__construct($config_factory);
    $this->marketo = $ul_marketo;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ul_marketo.metadata.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ul_marketo_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ul_marketo.metadata.settings');
    $env = $config->get('env');

    $form['#prefix'] = '<div id="marketo-settings">';
    $form['#suffix'] = '</div>';

    // The Environment:
    $form['environment'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Environment'),
    ];
    $form['environment']['env'] = [
      '#required' => TRUE,
      '#type' => 'select',
      '#title' => $this->t('Marketo Form IDs'),
      '#description' => $this->t('Switch the form IDs between Prod / Staging versions.'),
      '#options' => [
        'prod' => $this->t('Production'),
        'stage' => $this->t('Staging'),
      ],
      '#default_value' => $env,
      "#empty_option" => $this->t('- Select -'),
    ];

    // ReCaptcha.
    $form['recaptcha'] = [
      '#type' => 'fieldset',
      '#title' => $this->t("reCaptcha"),
    ];
    $form['recaptcha']['recaptcha_site_key'] = [
      '#required' => TRUE,
      '#type' => 'textfield',
      '#title' => $this->t('reCaptcha Site Key'),
      '#description' => $this->t('For test mode use: 6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI'),
      '#default_value' => $config->get('recaptcha_site_key'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Form callback function for rebuilding the form.
   */
  public static function rebuildForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild();
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    // NOTE: Hard-coding the Marketo Instance value: 'Enterprise'.
    $this->config('ul_marketo.metadata.settings')
      ->set('instance', 'Enterprise')
      ->set('env', $form_state->getValue('env'))
      ->set('recaptcha_site_key', $form_state->getValue('recaptcha_site_key'))
      ->clear('sub_cou')
      ->clear('last_interest')
      ->clear('oracle_industry')
      ->clear('oracle_subindustry')
      ->clear('area_interest')
      ->save();
  }

}

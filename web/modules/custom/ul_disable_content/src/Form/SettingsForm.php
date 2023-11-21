<?php

namespace Drupal\ul_disable_content\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ul_disable_content\DisableContentServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * UL Disable Content module settings form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * DisableContentService.
   *
   * @var \Drupal\ul_disable_content\DisableContentService
   */
  protected $ul_disable_content;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    return new static(
      $container->get('config.factory'),
      $container->get('ul_disable_content')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, DisableContentServiceInterface $ul_disable_content) {
    parent::__construct($config_factory);
    $this->ul_disable_content = $ul_disable_content;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ul_disable_content.metadata.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ul_disable_content_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get current saved values:
    $saved = \Drupal::service('ul_disable_content')->getDisabledContentTypes();

    // Get list of content types:
    $types = \Drupal::service('ul_disable_content')->getContentTypes();
    // Sort types by alpha order, keeping key association:
    asort($types);

    $form['#prefix'] = '<div id="hidecontent-settings">';
    $form['#suffix'] = '</div>';

    $form['content_types'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Disable Content Types'),
    ];

    $form['content_types']['options'] = [
      '#type' => 'checkboxes',
      '#title' => t('Check each content type you wish to disable. Once disabled, authors will not be able to create new nodes using these content types.'),
      '#options' => $types,
      '#default_value' => $saved,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('ul_disable_content.metadata.settings')
      ->set('options', $form_state->getValues('options'))
      ->save();
  }

}

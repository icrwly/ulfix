<?php

namespace Drupal\ul_chat\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ul_chat\ChatServiceInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Settings Form for Chat.
 */
class SettingsForm extends ConfigFormBase {

/*
   * ChatService object.
   *
   * @var \Drupal\ul_chat\ChatServiceInterface
   */
  protected $chat;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    return new static(
      $container->get('config.factory'),
      $container->get('ul_chat'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, ChatServiceInterface $chat) {
    parent::__construct($config_factory);
    $this->chat = $chat;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ul_chat.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ul_chat_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Chat settings:
    $config = $this->config('ul_chat.settings');
    // The form object:
    $form['ul_sf_chat'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('UL Chat'),
    ];

    $form['ul_sf_chat']['select_enviroment']  = [
      '#type' => 'select',
      '#title' => $this->t('Enviroment'),
      '#options' => [
        'dev' => $this->t('DEV'),
        'uat' => $this->t('TEST'),
        'prod' => $this->t('PROD'),
      ],
      '#default_value' => $config->get('select_enviroment'),
      '#required' => TRUE,
    ];

    $form['ul_sf_chat']['script_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('JS to load SF Chat API'),
      '#description' => $this->t('JS to load SF Chat API.'),
      '#size' => '60',
      '#default_value' => $config->get('script_url'),
    ];

    $form['ul_sf_chat']['sf_script_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat Services URL'),
      '#description' => $this->t('The SF Chat Services URL'),
      '#size' => '60',
      '#default_value' => $config->get('sf_script_url'),
    ];
    // Allowed Pages
    $form['ul_sf_chat']['allowed_pages'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Please specify allowed pages (Separate each value with a comma).'),
      '#description' => $this->t('Please specify allowed pages (Separate each value with a comma).'),
      '#size' => '60',
      '#default_value' => $config->get('allowed_pages'),
    ];

    // The form object:
    $form['ul_sf_chat_prod'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('UL Chat SF PROD Settings'),
    ];

    $form['ul_sf_chat_prod']['sf_subdomain_url_prod'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - embedded_svc'),
      '#description' => $this->t('The SF Chat - embedded_svc'),
      '#size' => '60',
      '#default_value' => $config->get('sf_subdomain_url_prod'),
    ];
    $form['ul_sf_chat_prod']['sf_community_url_prod'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - embedded_svc'),
      '#description' => $this->t('The SF Chat - embedded_svc'),
      '#size' => '60',
      '#default_value' => $config->get('sf_community_url_prod'),
    ];
    $form['ul_sf_chat_prod']['sf_org_id_prod'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - gslbBaseURL(org_id)'),
      '#description' => $this->t('The SF Chat - gslbBaseURL(org_id)'),
      '#size' => '60',
      '#default_value' => $config->get('sf_org_id_prod'),
    ];
    $form['ul_sf_chat_prod']['sf_chat_poc_prod'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - gslbBaseURL(chat_poc)'),
      '#description' => $this->t('The SF Chat - gslbBaseURL(chat_poc)'),
      '#size' => '60',
      '#default_value' => $config->get('sf_chat_poc_prod'),
    ];
    $form['ul_sf_chat_prod']['baseLiveAgentContentURL_prod'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - baseLiveAgentContentURL'),
      '#description' => $this->t('The SF Chat - baseLiveAgentContentURL'),
      '#size' => '60',
      '#default_value' => $config->get('baseLiveAgentContentURL_prod'),
    ];

    $form['ul_sf_chat_prod']['deploymentId_prod'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - deploymentId'),
      '#description' => $this->t('The SF Chat - deploymentId'),
      '#size' => '60',
      '#default_value' => $config->get('deploymentId_prod'),
    ];

    $form['ul_sf_chat_prod']['buttonId_prod'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - buttonId'),
      '#description' => $this->t('The SF Chat - buttonId'),
      '#size' => '60',
      '#default_value' => $config->get('buttonId_prod'),
    ];

    $form['ul_sf_chat_prod']['baseLiveAgentURL_prod'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - baseLiveAgentURL'),
      '#description' => $this->t('The SF Chat - baseLiveAgentURL'),
      '#size' => '60',
      '#default_value' => $config->get('baseLiveAgentURL_prod'),
    ];

    $form['ul_sf_chat_prod']['eswLiveAgentDevName_prod'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - eswLiveAgentDevName'),
      '#description' => $this->t('The SF Chat - eswLiveAgentDevName'),
      '#size' => '60',
      '#default_value' => $config->get('eswLiveAgentDevName_prod'),
    ];
    $form['ul_sf_chat_prod']['isOfflineSupportEnabled_prod'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - isOfflineSupportEnabled'),
      '#description' => $this->t('The SF Chat - isOfflineSupportEnabled'),
      '#size' => '60',
      '#default_value' => $config->get('isOfflineSupportEnabled_prod'),
    ];

    // The form object:
    $form['ul_sf_chat_uat'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('UL Chat SF UAT Settings'),
    ];

    $form['ul_sf_chat_uat']['sf_subdomain_url_uat'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - embedded_svc'),
      '#description' => $this->t('The SF Chat - embedded_svc'),
      '#size' => '60',
      '#default_value' => $config->get('sf_subdomain_url_uat'),
    ];
    $form['ul_sf_chat_uat']['sf_community_url_uat'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - embedded_svc'),
      '#description' => $this->t('The SF Chat - embedded_svc'),
      '#size' => '60',
      '#default_value' => $config->get('sf_community_url_uat'),
    ];
    $form['ul_sf_chat_uat']['sf_org_id_uat'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - gslbBaseURL(org_id)'),
      '#description' => $this->t('The SF Chat - gslbBaseURL(org_id)'),
      '#size' => '60',
      '#default_value' => $config->get('sf_org_id_uat'),
    ];
    $form['ul_sf_chat_uat']['sf_chat_poc_uat'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - gslbBaseURL(chat_poc)'),
      '#description' => $this->t('The SF Chat - gslbBaseURL(chat_poc)'),
      '#size' => '60',
      '#default_value' => $config->get('sf_chat_poc_uat'),
    ];
    $form['ul_sf_chat_uat']['baseLiveAgentContentURL_uat'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - baseLiveAgentContentURL'),
      '#description' => $this->t('The SF Chat - baseLiveAgentContentURL'),
      '#size' => '60',
      '#default_value' => $config->get('baseLiveAgentContentURL_uat'),
    ];


    $form['ul_sf_chat_uat']['deploymentId_uat'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - deploymentId'),
      '#description' => $this->t('The SF Chat - deploymentId'),
      '#size' => '60',
      '#default_value' => $config->get('deploymentId_uat'),
    ];

    $form['ul_sf_chat_uat']['buttonId_uat'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - buttonId'),
      '#description' => $this->t('The SF Chat - buttonId'),
      '#size' => '60',
      '#default_value' => $config->get('buttonId_uat'),
    ];

    $form['ul_sf_chat_uat']['baseLiveAgentURL_uat'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - baseLiveAgentURL'),
      '#description' => $this->t('The SF Chat - baseLiveAgentURL'),
      '#size' => '60',
      '#default_value' => $config->get('baseLiveAgentURL_uat'),
    ];

    $form['ul_sf_chat_uat']['eswLiveAgentDevName_uat'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - eswLiveAgentDevName'),
      '#description' => $this->t('The SF Chat - eswLiveAgentDevName'),
      '#size' => '60',
      '#default_value' => $config->get('eswLiveAgentDevName_uat'),
    ];
    $form['ul_sf_chat_uat']['isOfflineSupportEnabled_uat'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - isOfflineSupportEnabled'),
      '#description' => $this->t('The SF Chat - isOfflineSupportEnabled'),
      '#size' => '60',
      '#default_value' => $config->get('isOfflineSupportEnabled_uat'),
    ];


    // The form object:
    $form['ul_sf_chat_dev'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('UL Chat SF DEV Settings'),
    ];

    $form['ul_sf_chat_dev']['sf_subdomain_url_dev'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - embedded_svc'),
      '#description' => $this->t('The SF Chat - embedded_svc'),
      '#size' => '60',
      '#default_value' => $config->get('sf_subdomain_url_dev'),
    ];
    $form['ul_sf_chat_dev']['sf_community_url_dev'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - embedded_svc'),
      '#description' => $this->t('The SF Chat - embedded_svc'),
      '#size' => '60',
      '#default_value' => $config->get('sf_community_url_dev'),
    ];
    $form['ul_sf_chat_dev']['sf_org_id_dev'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - gslbBaseURL(org_id)'),
      '#description' => $this->t('The SF Chat - gslbBaseURL(org_id)'),
      '#size' => '60',
      '#default_value' => $config->get('sf_org_id_dev'),
    ];
    $form['ul_sf_chat_dev']['sf_chat_poc_dev'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - gslbBaseURL(chat_poc)'),
      '#description' => $this->t('The SF Chat - gslbBaseURL(chat_poc)'),
      '#size' => '60',
      '#default_value' => $config->get('sf_chat_poc_dev'),
    ];
    $form['ul_sf_chat_dev']['baseLiveAgentContentURL_dev'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - baseLiveAgentContentURL'),
      '#description' => $this->t('The SF Chat - baseLiveAgentContentURL'),
      '#size' => '60',
      '#default_value' => $config->get('baseLiveAgentContentURL_dev'),
    ];


    $form['ul_sf_chat_dev']['deploymentId_dev'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - deploymentId'),
      '#description' => $this->t('The SF Chat - deploymentId'),
      '#size' => '60',
      '#default_value' => $config->get('deploymentId_dev'),
    ];

    $form['ul_sf_chat_dev']['buttonId_dev'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - buttonId'),
      '#description' => $this->t('The SF Chat - buttonId'),
      '#size' => '60',
      '#default_value' => $config->get('buttonId_dev'),
    ];

    $form['ul_sf_chat_dev']['baseLiveAgentURL_dev'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - baseLiveAgentURL'),
      '#description' => $this->t('The SF Chat - baseLiveAgentURL'),
      '#size' => '60',
      '#default_value' => $config->get('baseLiveAgentURL_dev'),
    ];

    $form['ul_sf_chat_dev']['eswLiveAgentDevName_dev'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - eswLiveAgentDevName'),
      '#description' => $this->t('The SF Chat - eswLiveAgentDevName'),
      '#size' => '60',
      '#default_value' => $config->get('eswLiveAgentDevName_dev'),
    ];
    $form['ul_sf_chat_dev']['isOfflineSupportEnabled_dev'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The SF Chat - isOfflineSupportEnabled'),
      '#description' => $this->t('The SF Chat - isOfflineSupportEnabled'),
      '#size' => '60',
      '#default_value' => $config->get('isOfflineSupportEnabled_dev'),
    ];

    $form['ul_sf_chat_offline'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('UL Chat Offline Settings'),
    ];
    $form['ul_sf_chat_offline']['chat_offlile_modal_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Chat Offline Modal Title'),
      '#description' => $this->t('Chat Offline Modal Title'),
      '#size' => '60',
      '#default_value' => $config->get('chat_offlile_modal_title'),
    ];
    $form['ul_sf_chat_offline']['chat_offlile_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Chat Offline Title'),
      '#description' => $this->t('Chat Offline Title'),
      '#size' => '60',
      '#default_value' => $config->get('chat_offlile_title'),
    ];
    $form['ul_sf_chat_offline']['chat_offlile_message'] = [
      '#type' => 'text_format',
      '#title' => 'Chat Offline Message',
      '#description' => $this->t('Chat Offline Message'),
      '#required' => FALSE,
      '#default_value' => $config->get('chat_offlile_message'),
      '#format' => 'full_html',
      '#base_type' => 'textarea',
    ];


    return parent::buildForm($form, $form_state);
  }



  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $offline_message = $form_state->getValue('chat_offlile_message')['value'];
    $offline_message = check_markup($offline_message,'full_html');

    $this->config('ul_chat.settings')
      ->set('chat_enable', $form_state->getValue('chat_enable'))
      ->set('select_enviroment', $form_state->getValue('select_enviroment'))
      ->set('script_url', $form_state->getValue('script_url'))
      ->set('sf_script_url', $form_state->getValue('sf_script_url'))
      ->set('allowed_pages', $form_state->getValue('allowed_pages'))
      ->set('sf_subdomain_url_prod', $form_state->getValue('sf_subdomain_url_prod'))
      ->set('sf_community_url_prod', $form_state->getValue('sf_community_url_prod'))
      ->set('sf_org_id_prod', $form_state->getValue('sf_org_id_prod'))
      ->set('sf_chat_poc_prod', $form_state->getValue('sf_chat_poc_prod'))
      ->set('baseLiveAgentContentURL_prod', $form_state->getValue('baseLiveAgentContentURL_prod'))
      ->set('deploymentId_prod', $form_state->getValue('deploymentId_prod'))
      ->set('buttonId_prod', $form_state->getValue('buttonId_prod'))
      ->set('baseLiveAgentURL_prod', $form_state->getValue('baseLiveAgentURL_prod'))
      ->set('eswLiveAgentDevName_prod', $form_state->getValue('eswLiveAgentDevName_prod'))
      ->set('isOfflineSupportEnabled_prod', $form_state->getValue('isOfflineSupportEnabled_prod'))
      ->set('sf_subdomain_url_uat', $form_state->getValue('sf_subdomain_url_uat'))
      ->set('sf_community_url_uat', $form_state->getValue('sf_community_url_uat'))
      ->set('sf_org_id_uat', $form_state->getValue('sf_org_id_uat'))
      ->set('sf_chat_poc_uat', $form_state->getValue('sf_chat_poc_uat'))
      ->set('baseLiveAgentContentURL_uat', $form_state->getValue('baseLiveAgentContentURL_uat'))
      ->set('deploymentId_uat', $form_state->getValue('deploymentId_uat'))
      ->set('buttonId_uat', $form_state->getValue('buttonId_uat'))
      ->set('baseLiveAgentURL_uat', $form_state->getValue('baseLiveAgentURL_uat'))
      ->set('eswLiveAgentDevName_uat', $form_state->getValue('eswLiveAgentDevName_uat'))
      ->set('isOfflineSupportEnabled_uat', $form_state->getValue('isOfflineSupportEnabled_uat'))
      ->set('sf_subdomain_url_dev', $form_state->getValue('sf_subdomain_url_dev'))
      ->set('sf_community_url_dev', $form_state->getValue('sf_community_url_dev'))
      ->set('sf_org_id_dev', $form_state->getValue('sf_org_id_dev'))
      ->set('sf_chat_poc_dev', $form_state->getValue('sf_chat_poc_dev'))
      ->set('baseLiveAgentContentURL_dev', $form_state->getValue('baseLiveAgentContentURL_dev'))
      ->set('deploymentId_dev', $form_state->getValue('deploymentId_dev'))
      ->set('buttonId_dev', $form_state->getValue('buttonId_dev'))
      ->set('baseLiveAgentURL_dev', $form_state->getValue('baseLiveAgentURL_dev'))
      ->set('eswLiveAgentDevName_dev', $form_state->getValue('eswLiveAgentDevName_dev'))
      ->set('isOfflineSupportEnabled_dev', $form_state->getValue('isOfflineSupportEnabled_dev'))
      ->set('chat_offlile_modal_title', $form_state->getValue('chat_offlile_modal_title'))
      ->set('chat_offlile_title', $form_state->getValue('chat_offlile_title'))
      ->set('chat_offlile_message', $offline_message)
      ->save();
  }

}

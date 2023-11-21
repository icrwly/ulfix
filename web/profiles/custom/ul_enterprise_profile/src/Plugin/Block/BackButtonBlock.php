<?php

namespace Drupal\ul_enterprise_profile\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a link back to WROT page if it is the referer.
 *
 * @Block(
 *   id = "wrot_block",
 *   admin_label = @Translation("Back button block"),
 *   category = @Translation("Navigation"),
 * )
 */
class BackButtonBlock extends BlockBase implements ContainerFactoryPluginInterface {

  public const PAGE_KEY = 'trust_entity';
  public const SITE_KEY = 'site_host';
  public const RETURN_LABEL = 'return_link_text';
  public const BACK_BUTTON_CLASS = 'wrot-link';

  /**
   * Language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Node entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager, Connection $database, EntityStorageInterface $node_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
    $this->database = $database;
    $this->nodeStorage = $node_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager'),
      $container->get('database'),
      $container->get('entity_type.manager')->getStorage('node')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $form[self::PAGE_KEY] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Back-to-page node name'),
      '#description' => $this->t('Enter the node title of the page to return to.'),
      '#target_type' => 'node',
    ];
    $form[self::SITE_KEY] = [
      '#type' => 'textfield',
      '#title' => $this->t('Return-to-site host'),
      '#description' => $this->t('The host of the site to return to (e.g. annualreport.ul.com).'),
      '#default_value' => $this->configuration[self::SITE_KEY] ?? '',
    ];
    if (!empty($this->configuration[self::PAGE_KEY])) {
      $form[self::PAGE_KEY]['#default_value'] = $this->nodeStorage->load($this->configuration[self::PAGE_KEY]);
    }
    $form[self::RETURN_LABEL] = [
      '#type' => 'textfield',
      '#title' => $this->t('Return text label'),
      '#description' => $this->t('The text to display as the link back to the page.'),
      '#default_value' => $this->configuration[self::RETURN_LABEL] ?? '',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    parent::blockValidate($form, $form_state);
    $page_value = $form_state->getValue(self::PAGE_KEY, NULL);
    $site_value = $form_state->getValue(self::SITE_KEY, NULL);
    $return_text = $form_state->getValue(self::RETURN_LABEL, NULL);
    if (!empty($page_value) && !empty($site_value)) {
      $form_state->setErrorByName(self::PAGE_KEY, 'Both back-to-page and return-to-site cannot have values at the same time.');
      $form_state->setErrorByName(self::SITE_KEY, 'Both back-to-page and return-to-site cannot have values at the same time.');
    }
    if (empty($page_value) && empty($site_value)) {
      $form_state->setErrorByName(self::PAGE_KEY, 'Either back-to-page or return-to-site need a value.');
      $form_state->setErrorByName(self::SITE_KEY, 'Either back-to-page or return-to-site need a value.');
    }
    if (empty($return_text)) {
      $form_state->setErrorByName(self::RETURN_LABEL, 'You must specify a back button text label.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration[self::PAGE_KEY] = $values[self::PAGE_KEY];
    $this->configuration[self::SITE_KEY] = $values[self::SITE_KEY];
    $this->configuration[self::RETURN_LABEL] = $values[self::RETURN_LABEL];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['container'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => $this->getBackButtonAttributes(),
      '#attached' => [
        'library' => ['ul_enterprise_profile/back-button'],
      ],
    ];
    return $build;
  }

  /**
   * Retrieves the attributes for the back button.
   *
   * @return array
   *   Render array-ready #attributes array.
   */
  protected function getBackButtonAttributes() {
    $attributes = [
      'class' => [self::BACK_BUTTON_CLASS],
      'data-return-text' => $this->configuration[self::RETURN_LABEL],
    ];
    if (empty($this->configuration[self::PAGE_KEY])) {
      return array_merge($attributes, $this->getBackToSiteAttributes());
    }
    else {
      return array_merge($attributes, $this->getBackToPageAttributes());
    }
  }

  /**
   * Retrieves the back-to-page attributes.
   *
   * @return array
   *   Render array-ready #attributes array.
   */
  protected function getBackToPageAttributes() {
    $current_language = $this->languageManager->getCurrentLanguage()->getId();
    $source = '/node/' . $this->configuration[self::PAGE_KEY];
    $aliases = $this->database->query('
    SELECT alias, langcode
    FROM {path_alias}
    WHERE path = :path
  ', [':path' => $source])->fetchAllAssoc('langcode', \PDO::FETCH_ASSOC);
    $possible_referrer_urls = [];
    foreach ($aliases as $langcode => $alias) {
      if ($current_language == $langcode) {
        $possible_referrer_urls[] = $alias['alias'];
      }
      else {
        $possible_referrer_urls[] = "/$langcode{$alias['alias']}";
      }
    }
    if ($current_language == $langcode) {
      $possible_referrer_urls[] = $source;
    }
    else {
      $possible_referrer_urls[] = "/$current_language" . $source;
    }
    $possible_referrer_urls = array_map(function ($referrer) {
      return '"' . $referrer . '"';
    }, $possible_referrer_urls);

    return [
      'data-aliases' => '[' . implode(', ', $possible_referrer_urls) . ']',
    ];
  }

  /**
   * Retrieves the back-to-site attributes.
   *
   * @return array
   *   Render array-ready #attributes array.
   */
  protected function getBackToSiteAttributes() {
    return [
      'data-return-site' => $this->configuration[self::SITE_KEY],
    ];
  }

}

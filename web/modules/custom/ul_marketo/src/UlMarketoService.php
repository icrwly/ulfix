<?php

namespace Drupal\ul_marketo;

use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\block_content\BlockContentInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\ul_marketo\Entity\MarketoFormInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ul_marketo\Entity\MarketoForm;

/**
 * Service to retrieve/provide Marketo form information.
 */
class UlMarketoService implements UlMarketoServiceInterface {

  use StringTranslationTrait;

  /**
   * ConfigFactory service.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Marketo Instance Settings.
   *
   * @var array
   */
  protected $instanceSettings;

  /**
   * Route Matcher.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new UlMarketoService object.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   Config factory object.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route matcher.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(ConfigFactory $config, Connection $connection, EntityTypeManagerInterface $entityTypeManager, RouteMatchInterface $route_match, LanguageManagerInterface $language_manager) {
    $this->config = $config;
    $this->connection = $connection;
    $this->entityTypeManager = $entityTypeManager;
    $this->routeMatch = $route_match;
    $this->languageManager = $language_manager;
    $this->instanceSettings = $this->getInstanceSettings();
  }

  /**
   * Return Marketo instance settings.
   *
   * @return array
   *   An array of the instance settings.
   */
  public function getInstanceSettings() {
    // New Marketo Instance:
    $settings['Enterprise'] = [
      'munchkin_code' => '117-ZLR-399',
      'title' => 'Marketo Enterprise',
      'base_url' => 'https://empoweringtrust.ul.com',
      'api_id' => FALSE,
      'api_secret' => FALSE,
      'api_url' => FALSE,
      'description' => $this->t('New Marketo Instance'),
    ];

    return $settings;
  }

  /**
   * Get Marketo Forms 2.0 JS API URL.
   *
   * @return string
   *   The instance javascript URL.
   */
  public function getInstanceScriptUrl() {
    // This is now hard-coded.
    return 'https://empoweringtrust.ul.com/js/forms2/js/forms2.min.js';
  }

  /**
   * Fetch all available environments.
   *
   * @return array
   *   The array of environments.
   */
  public function getAllEnvironments() {
    return [
      'prod' => $this->t('Production'),
      'stage' => $this->t('Staging'),
    ];
  }

  /**
   * L2O: {@inheritdoc}.
   */
  public function getSubCouOptions($sub_cou = NULL) {
    $sub_cou_list = \Drupal::service('ul_marketo.data_service')->getLastInterestBySubCou();
    $options = [];
    if (!empty($sub_cou) && is_string($sub_cou)) {
      if (is_array($sub_cou_list) && array_key_exists($sub_cou, $sub_cou_list)) {
        $data = $sub_cou_list[$sub_cou];
        foreach ($data['last_interests'] as $last_interest) {
          $options[$last_interest] = $last_interest;
        }
      }
    }
    else {
      foreach ($sub_cou_list as $value) {
        $options[$value['id']] = $value['display'];
      }
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntitySettings(EntityInterface $entity) {
    // Form by Marketo Routed Page (FALSE default).
    $form_by_route = FALSE;

    // Form settings (FALSE default).
    $form_settings = FALSE;

    // Array of Marketo entities (customizations).
    $form_entities = [];

    // Array of Marketo form types, keyed by CTA placement.
    // IE: $form_ids['cta_type'] = 'form_type'.
    $form_ids = [];

    // The current route:
    $route_name = $this->routeMatch->getRouteName();
    $route_array = explode('.', $route_name);

    // The current language:
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    // If entity is a NODE:
    if ($entity instanceof NodeInterface) {
      $parent = $entity;

      if ($entity->hasField('field_shared_marketo_custom')) {
        $form_entities = $entity->get('field_shared_marketo_custom')->referencedEntities();
      }

      if ($parent->hasField('field_shared_marketo_link')) {
        if ($parent->get('field_shared_marketo_link')->value) {
          $form_ids['hdr_cta'] = $parent->get('field_shared_marketo_link')->value;
        }
      }

      if ($parent->hasField('field_shared_rr_marketo_cta')) {
        if ($parent->get('field_shared_rr_marketo_cta')->value) {
          $form_ids['rr_cta'] = $parent->get('field_shared_rr_marketo_cta')->value;
        }
      }

      if ($form_by_route = $this->getFormByRoute()) {
        $form_ids['form_by_route'] = $form_by_route;
      }
    }

    // If the entity is a PARAGRAPH:
    elseif ($entity instanceof ParagraphInterface) {
      $parent = $entity->getParentEntity();

      if ($parent instanceof NodeInterface) {
        if ($parent->hasField('field_shared_marketo_custom')) {
          $form_entities = $parent->get('field_shared_marketo_custom')->referencedEntities();
        }
        if ($entity->hasField('field_marketo_form')) {
          $form_ids['paragraph_cta'] = $entity->get('field_marketo_form')->value;
        }
      }

      elseif ($parent instanceof BlockContentInterface) {
        if ($parent->hasField('field_marketo_form_customization')) {
          $form_entities = $parent->get('field_marketo_form_customization')->referencedEntities();
        }
        if ($entity->hasField('field_marketo_form')) {
          $form_ids['block_cta'] = $entity->get('field_marketo_form')->value;
        }
      }
    }

    // Else, return an empty array:
    else {
      return [];
    }

    // Does a Gated Form exist?
    $gated_form_exists = $this->gatedFormExists($form_entities);

    // Loop through the forms:
    foreach ($form_entities as $form) {
      $form_id = $form->bundle();

      // Viewing the form in a PARAGRAPH.
      if ($entity instanceof ParagraphInterface || in_array($form->bundle(), $route_array)) {
        /** @var \Drupal\ul_marketo\Entity\MarketoFormInterface $current_form */
        foreach ($form_ids as $form_id) {
          if ($form_settings = $this->getFormSettings($form, $form_id, $langcode)) {
            return $form_settings;
          }
        }
      }

      // Viewing the form on the page.
      elseif ($entity instanceof NodeInterface) {

        // If Marketo Routed Page:
        if ($form_by_route) {
          if ($form_by_route == $form_id) {
            $form_settings = $this->getFormSettings($form, $form_id, $langcode);
            break;
          }
        }

        // If there is a Gated Form:
        elseif ($gated_form_exists) {
          if ($form_id == 'gated_content_form') {
            $form_settings = $this->getFormSettings($form, $form_id, $langcode);
            break;
          }
        }

        // Else: Do we have a contact form?
        elseif (in_array($form_id, ['contact_form_configurable', 'generic_form'])) {
          $form_settings = $this->getFormSettings($form, $form_id, $langcode);
          break;
        }
      }
    }

    return $form_settings;
  }

  /**
   * {@inheritdoc}
   */
  public function setEntitySettings($entity_type_id, $id, array $settings) {
    $default_settings = $this->config->get('ul_marketo.metadata.settings');

    if (empty($default_settings)) {
      $default_settings = [];
    }

    // Check if overrides are different from default settings before saving.
    $override = FALSE;
    foreach ($settings as $key => $setting) {
      if (!isset($default_settings[$key]) || $setting != $default_settings[$key]) {
        $override = TRUE;
        break;
      }
    }

    // Save overrides for this entity if something is different.
    if ($override) {
      $this->connection->merge('ul_marketo_entity_overrides')
        ->key([
          'entity_type' => $entity_type_id,
          'entity_id' => $id,
        ])
        ->fields([
          'entity_type' => $entity_type_id,
          'entity_id' => $id,
          'marketo_settings' => serialize(array_merge($default_settings, $settings)),
        ])
        ->execute();
    }
    // Else unset override.
    else {
      $this->removeEntitySettings($entity_type_id, $id);
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeEntitySettings($entity_type_id, $entity_ids = NULL) {
    $query = $this->connection->delete('ul_marketo_entity_overrides')
      ->condition('entity_type', $entity_type_id);
    if (NULL !== $entity_ids) {
      $entity_ids = !is_array($entity_ids) ? [$entity_ids] : $entity_ids;
      $query->condition('entity_id', $entity_ids, 'IN');
    }
    $query->execute();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig($item = '') {
    $config_name = 'ul_marketo.metadata.settings';

    return $this->config->get($config_name);
  }

  /**
   * {@inheritdoc}
   */
  public function getThemeSettings($marketo_settings) {
    // @todo Find a better way to handle this.
    if (empty($marketo_settings['id'])) {
      return [];
    }

    $custom_settings = [];
    $custom_settings['button_text'] = $marketo_settings['field_shared_button_text'] ?? '';
    $custom_settings['cta_button_text'] = $marketo_settings['field_shared_cta_button_text'] ?? '';
    $custom_settings['form_title'] = $marketo_settings['field_shared_custom_title'] ?? '';
    $custom_settings['form_description'] = $marketo_settings['field_shared_form_description'] ?? '';

    $theme = [
      '#theme' => 'marketo_form',
      '#id' => $marketo_settings['id'],
      '#title' => $marketo_settings['label'],
      '#page_url' => $marketo_settings['settings']['path'],
      '#success_url' => $this->getSuccessUrl($marketo_settings),
      '#settings' => $custom_settings,
      '#marketo' => $marketo_settings,
      '#cache' => [
        'contexts' => [
          'url.path',
        ],
      ],
      '#attached' => [
        'library' => ['ul_marketo/ul_marketo'],
      ],
    ];

    // Pass entity values to theme including caching.
    if (!empty($this->entity)) {
      $theme['#entity'] = $this->entity;
      $theme['#url'] = $this->entity->toUrl();
      $theme['#cache']['tags'] = $this->entity->getCacheTags();
      $theme['#cache']['max-age'] = $this->entity->getCacheMaxAge();
    }

    return $theme;
  }

  /**
   * {@inheritdoc}
   */
  public function getSuccessUrl($marketo_settings) {
    return Url::fromRoute('ul_marketo.' . $marketo_settings['id'] . '.success');
  }

  /**
   * Method to get form settings.
   *
   * @param \Drupal\ul_marketo\Entity\MarketoFormInterface $form
   *   The form object.
   * @param string $form_id
   *   Current form id.
   * @param string $langcode
   *   Current langcode.
   *
   * @return bool|array
   *   Array if form settings exist, false otherwise.
   */
  private function getFormSettings(MarketoFormInterface $form, $form_id, $langcode) {
    $current_form = $form;
    if ($current_form->bundle() === $form_id) {
      $form_config = $this->config->get('ul_marketo.marketo_form_type.' . $current_form->bundle());
      $form_settings = $form_config->getRawData();
      $fields = $current_form->getFields();
      $field_values = array_filter($fields, function ($k) {
        if (strpos($k, 'field') !== FALSE) {
          return TRUE;
        }
        return FALSE;
      }, ARRAY_FILTER_USE_KEY);
      if ($current_form->hasTranslation($langcode)) {
        $current_form = $current_form->getTranslation($langcode);
      }
      foreach ($field_values as $name => $value) {
        if ($name == 'field_shared_thank_you_page') {
          $temp = $current_form->get('field_shared_thank_you_page')->getValue();
          if (is_array($temp) && isset($temp[0]['target_id'])) {
            $form_settings[$name] = Url::fromRoute('entity.node.canonical', ['node' => $temp[0]['target_id']])->toString();
          }
        }
        else {
          $form_settings[$name] = $current_form->get($name)->value;
        }
      }
      $form_settings['langcode'] = $langcode;
      $form_settings['form_entity'] = $current_form;

      return $form_settings;
    }
    return FALSE;
  }

  /**
   * Get settings of default(fallback) MarketoForm entity.
   *
   * @param \Drupal\ul_marketo\Entity\MarketoFormInterface $form
   *   The MarketForm enitity object.
   *
   * @return bool|array
   *   Array if form settings exist, false otherwise.
   */
  public function getFormDefaultSettings(MarketoFormInterface $form) {
    $current_form = $form;
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    $form_config = $this->config->get('ul_marketo.marketo_form_type.' . $current_form->bundle());
    $form_settings = $form_config->getRawData();

    $fields = $current_form->getFields();
    $field_values = array_filter($fields, function ($k) {
      if (strpos($k, 'field') !== FALSE) {
        return TRUE;
      }
      return FALSE;
    }, ARRAY_FILTER_USE_KEY);

    if ($current_form->hasTranslation($langcode)) {
      $current_form = $current_form->getTranslation($langcode);
    }
    foreach ($field_values as $name => $value) {
      $form_settings[$name] = $current_form->get($name)->value;
    }
    $form_settings['langcode'] = $langcode;
    $form_settings['form_entity'] = $current_form;
    return $form_settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentRoute() {
    return $this->routeMatch->getRouteName();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormByRoute() {
    $route = $this->getCurrentRoute();
    if (str_contains($route, 'ul_marketo')) {
      $route_array = explode('.', $route);
      if (isset($route_array[1]) && str_contains($route_array[1], '_form')) {
        return $route_array[1];
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentLanguage() {
    return $this->languageManager->getCurrentLanguage()->getId();
  }

  /**
   * {@inheritdoc}
   */
  public function getLanguageManager() {
    return $this->languageManager;
  }

  /**
   * Get or create a default(fallback) MarketoForm entity.
   *
   * @param string $formType
   *   The MarketForm bundle name.
   *
   * @return \Drupal\ul_marketo\Entity\MarketoFormInterface
   *   The MarketForm enitity object.
   */
  public function getOrCreateDefaultMarketoForm($formType) {
    // Check for a default form.
    // $connection = \Drupal::database();
    $results = $this->connection->select('marketo_form_field_data', 'm')
      ->fields('m', ['id'])
      ->condition('type', $formType, '=')
      ->condition('name', '%Default%', 'LIKE')
      ->isNull('parent_id')
      ->distinct()
      ->execute()
      ->fetchAll();

    $form_id = (count($results) > 0) ? $results[0]->id : NULL;

    if (!$form_id) {
      $entity_types = $this->entityTypeManager->getStorage('marketo_form_type')->loadMultiple();
      $label = "Default ";
      foreach ($entity_types as $entity_type) {
        if ($entity_type->id() == $formType) {
          $label .= $entity_type->label();
          break;
        }
      }

      $marketo_form = MarketoForm::create([
        'type' => $formType,
        'name' => $label,
      ]);
      // Set default values to newly created default form.
      $marketo_form->setDefaultValues();
      $marketo_form->save();
    }
    else {
      $marketo_form = $this->entityTypeManager->getStorage('marketo_form')->load($form_id);
    }

    return $marketo_form;
  }

  /**
   * Determine if there is a Gated Form Entity.
   */
  public function gatedFormExists($formArray) {
    if (is_array($formArray)) {
      foreach ($formArray as $form) {
        $form_id = $form->bundle();
        if ($form_id == 'gated_content_form') {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

}

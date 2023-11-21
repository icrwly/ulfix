<?php

namespace Drupal\ul_marketo\Plugin\Field\FieldFormatter;

use Drupal\block_content\BlockContentInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\ul_marketo\Plugin\Field\FieldType\MarketoForm;
use Drupal\ul_marketo\UlMarketoServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Plugin implementation of the Marketo Form Label formatter.
 *
 * @FieldFormatter(
 *   id = "marketo_form_label",
 *   label = @Translation("Marketo Form Label"),
 *   field_types = {
 *     "marketo_form"
 *   }
 * )
 *
 * @todo Rewrite this to work with new Entity Based approach.
 */
class MarketoFormLabelFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * Configuration object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The path validator service.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * Marketo Form Plugin Manager.
   *
   * @var \Drupal\ul_marketo\Plugin\MarketoFormPluginManager
   */
  protected $marketoFormPluginManager;

  /**
   * The Marketo service.
   *
   * @var Drupal\ul_marketo\UlMarketoServiceInterface
   */
  protected $marketo;

  /**
   * Entity type manager interface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('path.validator'),
      $container->get('config.factory'),
      $container->get('ul_marketo'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Constructs a new FormLabelFormatter.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Third party settings.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\ul_marketo\UlMarketoServiceInterface $marketo
   *   The marketo service.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The EnityTypeManager.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, PathValidatorInterface $path_validator, ConfigFactoryInterface $config_factory, UlMarketoServiceInterface $marketo, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->pathValidator = $path_validator;
    $this->config = $config_factory;
    $this->marketo = $marketo;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $entity = $items->getEntity();

    foreach ($items as $delta => $item) {
      $element[$delta] = [
        $this->buildLabel($item, $entity),
      ];
      $element[$delta] = $element[$delta][0];
    }

    return $element;
  }

  /**
   * Builds the Marketo Form label.
   *
   * @param \Drupal\ul_marketo\Plugin\Field\FieldType\MarketoForm $item
   *   The marketo field object.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The node entity.
   *
   * @return array
   *   Render array.
   */
  protected function buildLabel(MarketoForm $item, EntityInterface $entity) {
    // Grab the current language.
    $langcode = $this->marketo->getCurrentLanguage();
    $form_entities = [];
    // Figure out what the parent of the form is.
    // @todo Pull this section out into a shared method in UlMarketoService.
    if ($entity instanceof NodeInterface) {
      $parent = $entity;
      $form_entities = $entity->get('field_shared_marketo_custom')->referencedEntities();
    }
    elseif ($entity instanceof ParagraphInterface) {
      $parent = $entity->getParentEntity();
      if ($parent instanceof NodeInterface) {
        $form_entities = $parent->get('field_shared_marketo_custom')->referencedEntities();
      }
      if ($parent instanceof BlockContentInterface) {
        $form_entities = $parent->get('field_marketo_form_customization')->referencedEntities();
      }
    }

    // Instantiate the current form to prevent errors.
    $current_form = '';
    $field_name = $item->getFieldDefinition()->get('field_name');

    foreach ($form_entities as $form) {
      if ($parent->get($field_name)->value === $form->getMarketoFormType()->id()) {
        $current_form = $form;
        // Check if translation available to use for current language.
        if ($current_form->hasTranslation($langcode)) {
          $current_form = $current_form->getTranslation($langcode);
        }
        break;
      }
    }

    // Load the default MarketoForm (fallback entity) if no form_customization.
    if (empty($current_form)) {
      return [];
      /*
      $current_form = $this->getDefaultMarketoForm($item->getString());
      if ($current_form && $current_form->hasTranslation($langcode)) {
      $current_form = $current_form->getTranslation($langcode);
      }
       */
    }

    return [
      '#type' => 'inline_template',
      '#template' => '{{ label }}',
      '#context' => ['label' => $current_form->type->entity->label()],
    ];

  }

  /**
   * Get the default(fallback) MarketoForm entity.
   *
   * @param string $formType
   *   The MarketForm bundle name.
   *
   * @return \Drupal\ul_marketo\Entity\MarketoFormInterface
   *   The MarketForm enitity object.
   */
  public function getDefaultMarketoForm($formType) {
    // Call function of UlMarketoServiceInterface to get default MarketoForm.
    return $this->marketo->getOrCreateDefaultMarketoForm($formType);
  }

}

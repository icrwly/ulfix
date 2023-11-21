<?php

namespace Drupal\ul_marketo\Plugin\Field\FieldWidget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\ul_marketo\Plugin\MarketoFormPluginManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsWidgetBase;
use Drupal\ul_marketo\Entity\MarketoFormType;

/**
 * Entity browser widget.
 *
 * @FieldWidget(
 *   id = "marketo_form_widget",
 *   label = @Translation("Marketo Form"),
 *   provider = "ul_marketo",
 *   multiple_values = FALSE,
 *   field_types = {
 *     "marketo_form"
 *   }
 * )
 */
class MarketoFormWidget extends OptionsWidgetBase implements ContainerFactoryPluginInterface {

  /**
   * The marketo manager.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $marketoFormPluginManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a MarketoFormWidget object.
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\ul_marketo\Plugin\MarketoFormPluginManager $marketo_manager
   *   The marketo form plugin manager.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The marketo form plugin manager.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, MarketoFormPluginManager $marketo_manager, EntityTypeManager $entity_type_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->marketoFormPluginManager = $marketo_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('plugin.manager.marketo_form'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = [];
    return $element;
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
  protected function getOptions(FieldableEntityInterface $entity) {
    // Grab all of the form types to build options list.
    $form_types = $this->entityTypeManager->getStorage('marketo_form_type')->loadMultiple();
    $options = [];

    if (!empty($form_types)) {
      foreach ($form_types as $form) {
        if ($form instanceof MarketoFormType) {
          $form_type = $form->id();
          // Forms not allowed in the widget.
          $forms_to_remove = [
            'gated_content_form',
            'mkto_pref_ctr',
          ];

          if (!in_array($form_type, $forms_to_remove)) {
            $options[$form_type] = $form->label();
          }
        }
      }
    }

    // Add an empty option if the widget needs one.
    if ($empty_label = $this->getEmptyLabel()) {
      $options = ['_none' => $empty_label] + $options;
    }

    // Empty array of allowable form options:
    $allowed_options = [];

    // Current entity bundle:
    $bundle = $entity->bundle();

    // "Campaign Form" paragraph:
    if ($bundle == 'campaign_form') {
      $allowed_options = [
        '_none',
        'generic_form',
        'contact_form_configurable',
        'event_form',
      ];
    }

    // IF "Fifty/Fifty Text and Form" paragraph
    // OR "Marketo Inline Contact Form" paragraph
    // OR "Regional Pages" page:
    elseif (
      $bundle == 'text_and_form' ||
      $bundle == 'regional_pages' ||
      $bundle == 'mkto_inline_contact_form'
      ) {
      $allowed_options = [
        '_none',
        'generic_form',
        'contact_form_configurable',
      ];
    }

    // If the allowed options array has values,
    // then weed out all but the allowed options:
    if (count($allowed_options)) {
      foreach ($options as $id => $label) {
        if (!in_array($id, $allowed_options)) {
          unset($options[$id]);
        }
      }
    }

    // Sort the plugins alphabetically, while mainting the key assignments:
    asort($options);

    return $options;
  }

  /**
   * {@inheritdoc}
   *
   * This creates a select menu of different Marketo form
   * entities as the options.
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    // Options are filtered above, see "getOptions()".
    $options = $this->getOptions($items->getEntity());

    $element += [
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => $this->getSelectedOptions($items),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {

    $values = parent::massageFormValues($values, $form, $form_state);
    $new_values = [];
    foreach ($values as $value_array) {
      if (is_array($value_array)) {
        foreach ($value_array as $item) {
          if (!empty($item)) {
            $new_values[] = $item;
          }
        }
      }
    }

    return $new_values;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEmptyLabel() {
    if ($this->multiple) {
      // Multiple select: add a 'none' option for non-required fields.
      if (!$this->required) {
        return $this->t('- None -');
      }
    }
    else {
      // Single select: add a 'none' option for non-required fields,
      // and a 'select a value' option for required fields that do not come
      // with a value selected.
      if (!$this->required) {
        return $this->t('- None -');
      }
      if (!$this->has_value) {
        return $this->t('- Select a value -');
      }
    }
  }

}

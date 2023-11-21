<?php

namespace Drupal\ul_marketo\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldItemBase;

/**
 * Plugin implementation of the 'marketo_form' field type.
 *
 * @FieldType(
 *   id = "marketo_form",
 *   label = @Translation("Marketo Form"),
 *   description = @Translation(""),
 *   category = @Translation("Marketo"),
 *   default_widget = "marketo_form_widget",
 *   default_formatter = "marketo_form_formatter",
 * )
 */
class MarketoForm extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'varchar_ascii',
          'length' => 255,
        ],
      ],
      'indexes' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'marketo_forms' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // This is called very early by the user entity roles field. Prevent
    // early t() calls by using the TranslatableMarkup.
    $properties = [];
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Marketo form'))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraints = parent::getConstraints();

    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $elements = [];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = [];
    $options = [];

    $element['marketo_forms'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Marketo Forms'),
      '#description' => $this->t('Choose which Marketo Forms you want to use.'),
      '#options' => $options,
      '#default_value' => $this->getSetting('marketo_forms'),
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function getPreconfiguredOptions() {
    $options = [];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL || $value === '';
  }

}

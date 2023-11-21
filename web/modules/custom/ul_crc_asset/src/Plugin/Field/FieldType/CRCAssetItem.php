<?php

namespace Drupal\ul_crc_asset\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;

/**
 * Plugin implementation of the 'crc_asset_item' field type.
 *
 * @FieldType(
 *   id = "crc_asset_item",
 *   label = @Translation("CRC Asset Item"),
 *   description = @Translation("UL CRC Asset Item Field"),
 *   category = @Translation("Reference"),
 *   list_class = "\Drupal\ul_crc_asset\Plugin\Field\FieldType\CRCAssetFieldItemList",
 *   default_widget = "crc_asset_widget",
 *   default_formatter = "crc_asset_formatter",
 * )
 */
class CRCAssetItem extends EntityReferenceItem {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'target_type' => 'crc_asset',
      'display_field' => FALSE,
      'display_default' => FALSE,
    ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {

    $properties = parent::propertyDefinitions($field_definition);
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {

    $schema = [
      'columns' => [
        'target_id' => [
          'description' => 'The ID of the CRC Asset entity.',
          'type' => 'int',
          'unsigned' => TRUE,
        ],
      ],
      'indexes' => [
        'target_id' => ['target_id'],
      ],
      'foreign keys' => [
        'target_id' => [
          'table' => 'crc_asset',
          'columns' => ['target_id' => 'id'],
        ],
      ],
    ];

    return $schema;
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
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('target_id')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public static function getPreconfiguredOptions() {
    $options = [];
    return $options;
  }

}

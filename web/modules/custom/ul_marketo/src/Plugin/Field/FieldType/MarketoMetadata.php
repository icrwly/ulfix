<?php

namespace Drupal\ul_marketo\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'Marketo Metadata' field type.
 *
 * @FieldType (
 *   id = "marketo_meta",
 *   label = @Translation("Marketo Form Options"),
 *   description = @Translation("Stores the settings for a Marketo Form"),
 *   category = @Translation("Marketo"),
 *   default_widget = "marketo_meta",
 *   default_formatter = "string"
 * )
 */
class MarketoMetadata extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'instance' => [
          'type' => 'varchar_ascii',
          'length' => 255,
          'default_value' => '',
          'not_null' => TRUE,
        ],
        'mkto_campaign' => [
          'type' => 'varchar',
          'length' => 255,
          'default_value' => '',
          'not_null' => TRUE,
        ],
        'sub_cou' => [
          'type' => 'varchar',
          'length' => 255,
          'default_value' => '',
          'not_null' => TRUE,
        ],
        'last_interest' => [
          'type' => 'varchar',
          'length' => 255,
          'default_value' => '',
          'not_null' => TRUE,
        ],
      ],
      'indexes' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value[] = $this->get('instance')->getValue();
    $value[] = $this->get('mkto_campaign')->getValue();
    $value[] = $this->get('sub_cou')->getValue();
    $value[] = $this->get('last_interest')->getValue();

    foreach ($value as $v) {
      if (!empty($v)) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['instance'] = DataDefinition::create('string')
      ->setLabel(t('Instance'))
      ->setDescription(t('The Enterprise Instance.'));
    $properties['mkto_campaign'] = DataDefinition::create('string')
      ->setLabel(t('Marketo Campaign'))
      ->setDescription(t('The Marketo Campaign.'));
    $properties['sub_cou'] = DataDefinition::create('string')
      ->setLabel(t('Sub-COU'))
      ->setDescription(t('Choose the Sub-COU.'));
    $properties['last_interest'] = DataDefinition::create('string')
      ->setLabel(t('Last Interest'))
      ->setDescription(t('Choose the Last Interest.'));
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    // Treat the values as property value of the first property, if no array is
    // given.
    if (isset($values) && is_array($values)) {
      $keys = array_keys($this->definition->getPropertyDefinitions());
      foreach ($keys as $key) {
        $string = $values[$key] ?? '';
        $values[$key] = strip_tags($string);
      }
    }
    parent::setValue($values, $notify);
  }

}

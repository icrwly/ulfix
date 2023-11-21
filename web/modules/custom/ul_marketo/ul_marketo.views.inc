<?php

/**
 * @file
 * Provide Views data for the Address module.
 *
 * @ingroup views_module_handlers
 */

use Drupal\field\FieldStorageConfigInterface;

/**
 * Implements hook_field_views_data().
 */
function ul_marketo_field_views_data(FieldStorageConfigInterface $field) {
  $data = views_field_default_views_data($field);
  $field_type = $field->getType();
  $field_name = $field->getName();
  if ($field_type == 'marketo_meta') {
    $columns = [
      'instance' => 'standard',
      'sub_cou' => 'standard',
      'last_interest' => 'standard',
    ];

    foreach ($data as $table_name => $table_data) {
      foreach ($columns as $column => $plugin_id) {
        $data[$table_name][$field_name . '_' . $column]['field'] = [
          'id' => $plugin_id,
          'field_name' => $field_name,
          'property' => $column,
        ];
      }
    }
  }

  return $data;
}

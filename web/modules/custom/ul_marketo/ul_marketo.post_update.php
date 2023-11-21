<?php

/**
 * @file
 * Update functions.
 */

use Drupal\node\Entity\Node;
use Drupal\ul_marketo\Entity\MarketoForm;

/**
 * Updates forms from plugins to entities.
 */
function ul_marketo_post_update_translate_marketo(&$sandbox) {
  $contenthub_entity_tracking = \Drupal::service('acquia_contenthub.acquia_contenthub_entities_tracking');

  $connection = \Drupal::database();
  if (!isset($sandbox['total'])) {
    $fids = $connection->select('ul_marketo_entity_overrides', 'm')
      ->fields('m', ['id'])
      ->execute()
      ->fetchAll();
    $sandbox['total'] = count($fids);
    $sandbox['current'] = 0;
    $sandbox['updated_nodes'] = 0;

    if (empty($sandbox['total'])) {
      $sandbox['#finished'] = 1;
      return;
    }
  }
  if (!isset($sandbox['ach_info'])) {
    $ach_results = [];
    if ($connection->schema()->tableExists('acquia_contenthub_entities_tracking')) {
      $ach_fields = [
        'entity_type',
        'entity_id',
        'entity_uuid',
        'status_export',
        'status_import',
        'modified',
        'origin',
      ];
      $ach_results = $connection->select('acquia_contenthub_entities_tracking', 'a')
        ->fields('a', $ach_fields)
        ->condition($ach_fields[0], 'node')
        ->execute()
        ->fetchAllAssoc($ach_fields['1']);
      $sandbox['ach_info'] = $ach_results;
    }
  }
  if (!isset($sandbox['paragraph_forms'])) {
    $paragraph_results = [];
    if ($connection->schema()->tableExists('acquia_contenthub_entities_tracking')) {
      $query = $connection->select('paragraphs_item_field_data', 'p');
      $query->join('paragraph__field_marketo_form', 'm', 'p.id = m.entity_id');
      $paragraph_results = $query->fields('p', ['parent_id'])
        ->fields('m', ['field_marketo_form_value'])
        ->execute()
        ->fetchAll();
      $sandbox['paragraph_forms'] = $paragraph_results;
    }
  }

  if ($connection->schema()->tableExists('marketo_form')) {

    $forms_per_batch = 10;

    $node_results = $connection->select('ul_marketo_entity_overrides', 'm')
      ->fields('m')
      ->range($sandbox['current'], $forms_per_batch)
      ->execute()
      ->fetchAll();

    if (empty($node_results)) {
      $sandbox['#finished'] = 1;
      return;
    }
  }

  $node_keys = [
    'instance',
    'sub_cou',
    'last_interest',
  ];

  $form_keys = [
    'generic_form',
    'event_form',
    'email_form',
    'gated_content_form',
  ];

  foreach ($node_results as $n_result) {

    $export_status = $sandbox['ach_info'][$n_result->entity_id]->status_export ?? FALSE;
    $import_status = $sandbox['ach_info'][$n_result->entity_id]->status_import ?? FALSE;
    $update_this = FALSE;
    $new_forms = [];
    $node = Node::load($n_result->entity_id);
    if ($node->hasField('field_shared_marketo_link')) {
      $new_forms[] = $node->get('field_shared_marketo_link')->value;
    }
    if ($node->hasField('field_shared_rr_marketo_cta')) {
      $new_forms[] = $node->get('field_shared_rr_marketo_cta')->value;
    }
    foreach ($sandbox['paragraph_forms'] as $p_form) {
      if ($p_form->parent_id === $n_result->entity_id) {
        $new_forms[] = $p_form->field_marketo_form_value;
        $update_this = TRUE;
      }
    }

    // If in content hub and current site is source, update on this site.
    if (array_key_exists($n_result->entity_id, $sandbox['ach_info'])) {
      if ($export_status === 'EXPORTED'
        || $export_status === 'INITIATED'
        || $import_status === 'HAS_LOCAL_CHANGE') {
        $update_this = TRUE;
      }
    }
    // If not in content hub, then update on this site.
    if (!array_key_exists($n_result->entity_id, $sandbox['ach_info'])) {
      $update_this = TRUE;
    }
    if ($update_this) {
      $published = $node->isPublished();
      $settings = $n_result->marketo_settings;
      $unserialized_settings = unserialize($settings);
      $node_values = [];
      $custom_form_values = [];

      foreach ($node_keys as $node_key) {
        $node_values[$node_key] = $unserialized_settings[$node_key] ? $unserialized_settings[$node_key] : '';
        unset($unserialized_settings[$node_key]);
      }
      foreach ($form_keys as $form_key) {
        if (array_key_exists($form_key, $unserialized_settings)) {
          $custom_settings[$form_key] = $unserialized_settings[$form_key];
          $new_forms[] = $form_key;
        }
      }
      $new_forms = array_unique($new_forms);
      foreach ($new_forms as $new_key) {
        if ($new_key) {
          $custom_form_entity = MarketoForm::create([
            'type' => $new_key,
          ]);
          $is_changed = TRUE;
          if (is_array($custom_settings)) {
            foreach ($custom_settings[$new_key] as $setting => $value) {
              switch ($setting) {
                case 'form_title':
                  $custom_form_entity->set('field_shared_custom_title', $value);
                  break;

                case 'form_description':
                  $custom_form_entity->set('field_shared_form_description', $value);
                  break;

                case 'button_text':
                  $custom_form_entity->set('field_shared_button_text', $value);
                  break;

                case 'cta_button_text':
                  $custom_form_entity->set('field_shared_cta_button_text', $value);
                  break;

                case 'newsletter_item':
                  $custom_form_entity->set('field_newsletter_type', $value);
                  break;

                default:
                  break;
              }
            }
          }
          if ($new_key === 'gated_content_form') {
            $custom_form_entity->set('field_node_is_gated', '1');
          }
          if ($is_changed) {
            $custom_form_entity->isNew();
            $custom_form_entity->save();
            $custom_form_values = $node->get('field_shared_marketo_custom')->getValue();
            $custom_form_values[] = [
              'target_id' => $custom_form_entity->id(),
              'target_revision_id' => $custom_form_entity->getRevisionId(),
            ];
            $node->set('field_shared_marketo_custom', $custom_form_values);
          }

          $filtered_node_values = array_filter($node_values, function ($v) {
            return !empty($v);
          });

          if (count($filtered_node_values) > 0) {
            $node->set('field_shared_marketo_forms', $node_values);
          }
          if ($published) {
            $node->setPublished();
          }
          $node->save();
          if ($import_status === 'AUTO_UPDATE_ENABLED') {
            $contenthub_entity_tracking->setImportedEntity(
              $sandbox['ach_info'][$n_result->entity_id]->entity_type,
              $sandbox['ach_info'][$n_result->entity_id]->entity_id,
              $sandbox['ach_info'][$n_result->entity_id]->entity_uuid,
              $sandbox['ach_info'][$n_result->entity_id]->modified,
              $sandbox['ach_info'][$n_result->entity_id]->origin
            );
            $contenthub_entity_tracking->save();
            // @todo Remove this if no longer needed.
            // $contenthub_entity_tracking->setAutoUpdate(
            // $import_status === 'AUTO_UPDATE_ENABLED'
            // );
          }

          $sandbox['updated_nodes']++;
        }
      }
    }
    $sandbox['current']++;
  }

  \Drupal::messenger()
    ->addMessage($sandbox['current'] . ' forms processed.');

  if ($sandbox['current'] >= $sandbox['total']) {
    \Drupal::messenger()
      ->addMessage($sandbox['updated_nodes'] . ' forms updated to entities.');
    $sandbox['#finished'] = 1;
  }
  else {
    $sandbox['#finished'] = $sandbox['current'] / $sandbox['total'];
  }

}

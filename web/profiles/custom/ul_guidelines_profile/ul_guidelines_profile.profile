<?php

/**
 * @file
 * Enables modules and site configuration for a guideliens profile.
 */

use Drupal\Core\Config\FileStorage;

/**
 * Implements hook_config_ignore_settings_alter().
 */
function ul_guidelines_profile_config_ignore_settings_alter(array &$settings) {

  // Default ignore list.
  $ignore = [
    '~views.view.watchdog',
    '~views.view.comment',
    '~views.view.comments_recent',
    // Move views of content_moderation out of config_ignore.
    '~views.view.better_cm_workbench_edits_by_user',
    '~views.view.cm_current_user',
    '~views.view.cm_workbench_edits_by_user',
    'workflows.workflow.content_publication',
    '~views.view.workbench_edited',
    '~views.view.workbench_recent_content',
    '~block.block.claro*',
    '~block.block.seven*',
    '~block.block.seven*',
    '~block.block.seven_breadcrumbs',
    '~block.block.seven_content',
    '~block.block.seven_help',
    '~block.block.seven_local_actions',
    '~block.block.seven_login',
    '~block.block.seven_messages',
    '~block.block.seven_page_title',
    '~block.block.seven_primary_local_tasks',
    '~block.block.seven_secondary_local_tasks',
    '~block.block.views_block__cm_current_user_block_1',
  ];

  $active_config_storage = \Drupal::service('config.storage');
  $active_list = $active_config_storage->listAll();

  $default_config_storage = new FileStorage(drupal_get_path('profile', 'ul_guidelines_profile') . '/config/sync');
  $storage_list = $default_config_storage->listAll();

  // Find diff between active and stored config.
  $diff_list = array_diff($active_list, $storage_list);

  // Ignore any active blocks, views or image styles that were added to the
  // site but are not part of storage.
  $allowed_entities = [
    'block.block',
    'views.view',
    'image.style',
  ];
  if (!empty($diff_list)) {
    foreach ($diff_list as $item) {
      foreach ($allowed_entities as $entity) {
        if (substr_count($item, $entity) > 0) {
          $ignore[] = $item;
        }
      }
    }
  }

  foreach ($storage_list as $item) {
    // Blocks are unique, we want to keep them managed except for the weight.
    // We want to allow users to add custom blocks.
    if (substr_count($item, 'block.block') > 0) {
      $ignore[] = $item . ':weight';

      // Only ignore block visibility settings if the block is in active
      // config. Otherwise it triggers a fatal error.
      if (in_array($item, $active_list)) {
        $ignore[] = $item . ':visibility.request_path';
        $ignore[] = $item . ':dependencies';
      }
    }
  }

  // Merge any existing settings with hard coded settings.
  // Make sure they are unique.
  if (!empty($settings)) {
    $existing_settings = array_values($settings);
    $ignore = array_unique(array_merge($existing_settings, $ignore));
  }

  $settings = [];
  $index = 0;
  foreach ($ignore as $item) {
    $settings[$index] = $item;
    // Change index, config ignore exports indexes at increments of 2.
    $index += 2;
  }
}

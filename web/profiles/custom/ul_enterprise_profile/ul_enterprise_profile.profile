<?php

/**
 * @file
 * Enables modules and site configuration for a enterprise profile.
 */

use Drupal\Core\Cache\Cache;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\views\ViewExecutable;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\view_mode_selector\Plugin\Field\FieldWidget\ViewModeSelectorWidgetBase;

/**
 * Implements hook_config_ignore_settings_alter().
 */
function ul_enterprise_profile_config_ignore_settings_alter(array &$settings) {

  // Default ignore list.
  $ignore = [
    'acquia_lift.settings:credential.account_id',
    'acquia_lift.settings:credential.site_id',
    'acquia_lift.settings:field_mappings',
    'block.block.social_media_links_patforms:settings',
    'block.block.ul_com_theme*',
    'ul_marketo.marketo_form_type.email_form',
    'ul_marketo.marketo_form_type.event_form',
    'ul_marketo.marketo_form_type.gated_content_form',
    'ul_marketo.marketo_form_type.generic_form',
    'ul_marketo.marketo_form_type.mkto_pref_ctr',
    'ul_gmap.settings:ul_gmap.page_uuid',
    '~views.view.watchdog',
    '~views.view.webform_submissions',
    '~views.view.comment',
    '~views.view.comments_recent',
    '~views.view.published_thank_you_pages',
    '~views.view.tmgmt_*',
    'ul_chat.settings',
    // Move views of content_moderation out of config_ignore.
    '~views.view.better_cm_workbench_edits_by_user',
    '~views.view.cm_current_user',
    '~views.view.cm_workbench_edits_by_user',
    'ul_salesforce.settings:environment.dev.confirmation_url',
    'ul_salesforce.settings:environment.sit.confirmation_url',
    'ul_salesforce.settings:environment.uat.confirmation_url',
    'ul_salesforce.settings:environment.prod.confirmation_url',
    'workflows.workflow.content_publication',
    '~views.view.workbench_edited',
    '~views.view.workbench_recent_content',
    '~views.view.redirect',
    // 'acquia_connector.settings',
    '~views.view.content',
    '~views.view.media',
    '~views.view.files',
    '~block.block.claro*',
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
    '~views.view.experience_hub*',
    '~block.block.views_block__cm_current_user_block_1',
    '~views.view.ul_media_usage',
  ];

  $active_config_storage = \Drupal::service('config.storage');
  $active_list = $active_config_storage->listAll();

  $default_config_storage = new FileStorage(drupal_get_path('profile', 'ul_enterprise_profile') . '/config/sync');
  $storage_list = $default_config_storage->listAll();

  // Find diff between active and stored config.
  $diff_list = array_diff($active_list, $storage_list);

  // Ignore any active blocks, views or image styles that were added to the
  // site but are not part of storage.
  $allowed_entities = [
    'block.block',
    'views.view',
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

/**
 * Implements hook_field_widget_WIDGET_TYPE_form_alter().
 */
function ul_enterprise_profile_field_widget_entity_reference_paragraphs_form_alter(&$element, FormStateInterface $form_state, $context) {
  switch ($element['#paragraph_type']) {
    case 'video':
      $element['subform']['field_shared_view_mode']['widget'][0]['value']['#options']['grid'] = 'Two Column - Left';
      $element['subform']['field_shared_view_mode']['widget'][0]['value']['#options']['row'] = 'Two Column - Right';
      break;

    case 'spotlight':
      $element['subform']['field_shared_view_mode']['widget'][0]['value']['#options']['grid'] = 'Image - Left Side';
      $element['subform']['field_shared_view_mode']['widget'][0]['value']['#options']['row'] = 'Image - Right Side';
      break;

    case 'related_content':
      $element['subform']['field_shared_view_mode']['widget'][0]['value']['#options']['default'] = 'Mini Card';
      break;
  }
}

/**
 * Implements hook_field_widget_form_alter().
 */
function ul_enterprise_profile_field_widget_form_alter(&$element, FormStateInterface $form_state, $context) {
  // Prepopulate field_hash_target if empty.
  if ($context['items']->getName() == 'field_hash_target') {
    if (empty($element['value']['#default_value'])) {
      $date = new DateTime();
      $element['value']['#default_value'] = $date->getTimestamp();
    }
  }

  // Leaving the following in even though they don't seem to run anymore.
  // Override the View mode labels for paragraphs.
  if (array_key_exists('#entity_type', $element)) {
    if ($element['#entity_type'] == 'paragraph' && $context['widget'] instanceof ViewModeSelectorWidgetBase) {
      switch ($element['#bundle']) {
        case 'video':
          $element['value']['#options']['grid'] = 'Two Column - Left';
          $element['value']['#options']['row'] = 'Two Column - Right';
          break;

        case 'spotlight':
          $element['value']['#options']['grid'] = 'Image - Left Side';
          $element['value']['#options']['row'] = 'Image - Right Side';
          break;

        case 'related_content':
          $element['value']['#options']['default'] = 'Mini Card';
          break;
      }
    }
  }
}

/**
 * Implements hook_form_FORM_ID_form_alter().
 */
function ul_enterprise_profile_form_node_form_alter(array &$form, FormStateInterface $form_state, $form_id) {

  // Adding alterations to node scheduler advanced options in order to
  // provide clarity of when cron runs in relation to the user's
  // timezone.
  if (!empty($form['publish_on'])) {
    $tz = date_default_timezone_get();
    $date = new DrupalDateTime();
    $date->setTimezone(new \DateTimeZone($tz));
    $userTimezone = $date->getTimezone();
    $replacements = [
      '%tz' => $userTimezone->getName(),
    ];

    // The two times that cron runs. Midnight and Noon UTC.
    $cron = [
      'first' => '12:00:00',
      'second' => '00:00:00',
    ];

    foreach ($cron as $key => $time) {
      // Set cron times in UTC timezone, this is what Site Factory uses.
      $dateTime = new DrupalDateTime($time, 'UTC');

      // Switch to user timezone and output replacement values based on
      // the user's time that is still accurate with Site Factory's
      // cron interval.
      $dateTime->setTimezone($userTimezone);
      $replacements['%' . $key] = $dateTime->format('g:i A');
    }

    $description = t('Note: Scheduled publishing is batched and ONLY occurs at %first and %second %tz. Leave blank for no scheduled publishing.', $replacements);
    $form['publish_on']['widget'][0]['value']['#description'] = $description;
    $form['unpublish_on']['widget'][0]['value']['#description'] = $description;
  }

  // Landing Pages:
  if (str_contains($form_id, '_landing_page_')) {
    // Custom form validation.
    $form['#validate'][] = 'ul_enterprise_profile_form_validate';
  }
}

/**
 * Form validation handler called in hook_form_FORM_ID_alter.
 *
 * @param array $form
 *   Form object.
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   FormState Interface.
 */
function ul_enterprise_profile_form_validate(array $form, FormStateInterface &$form_state) {
  $values = $form_state->getValues();

  // Defaults:
  $nodeType = FALSE;

  /** @var \Drupal\node\NodeInterface $node */
  $node = $form_state->getFormObject()->getEntity();
  // If node, try to get the node type:
  if ($node) {
    $nodeType = $node->getType();
  }

  // If the Node Type is a Landing Page:
  if ($nodeType == 'landing_page') {
    if (
      // If using alternate display options:
      $values['field_alternate_display']['value'] == 1
      &&
      isset($values['field_login_link'][0]['title'])
      &&
      isset($values['field_create_account_link'][0]['title'])
      &&
      isset($values['field_link_description'][0]['value'])
    ) {
      // If there is a Link Description value:
      if ($values['field_link_description'][0]['value'] !== '') {
        // If there is no Create Account link
        // And there is no Login link:
        if (
          $values['field_login_link'][0]['title'] == ''
          &&
          $values['field_create_account_link'][0]['title'] == ''
        ) {
          $form_state->setErrorByName('field_link_description', t('When using the Link Description field you must also enter a Create Account link or a Login link.'));
        }
      }
      // Else there IS a Link Description:
      else {
        // If there is a Create Account link
        // Or there is a Login link:
        if (
          $values['field_login_link'][0]['title'] !== ''
          ||
          $values['field_create_account_link'][0]['title'] !== ''
        ) {
          $form_state->setErrorByName('field_link_description', t('When using the Create Account link or a Login link you must also enter a Link Description.'));
        }
      }
    }
  }
}

/**
 * Implements hook_entity_type_alter().
 */
function ul_enterprise_profile_entity_type_alter(array &$entity_types) {
  // This is a temporary measure to fix issues outlined in the JIRA tickets:
  // - https://vitral-ul.atlassian.net/browse/UL-1853
  // - https://vitral-ul.atlassian.net/browse/UL-2266
  // This solution was copied from the d.o issue:
  // - https://www.drupal.org/node/2938191
  // @todo Remove this hotfix when Drupal core solves the problem with 8.7.x
  // Issue can be tracked:
  // - https://www.drupal.org/project/drupal/issues/3025039
  $fix_types = ['node', 'paragraph'];
  foreach ($fix_types as $entity_type) {
    if (isset($entity_types[$entity_type])) {
      $constraints = $entity_types[$entity_type]->getConstraints();
      unset($constraints['EntityUntranslatableFields']);
      $entity_types[$entity_type]->setConstraints($constraints);
    }
  }
}

/**
 * Implements hook_views_query_alter().
 */
function ul_enterprise_profile_views_query_alter(ViewExecutable $view, QueryPluginBase $query) {
  if ($view->id() == 'hero') {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof NodeInterface) {
      $query->addWhere(0, 'node_field_revision.vid', $node->getRevisionId());
    }
  }
}

/**
 * Implements hook_entity_view_alter().
 */
function ul_enterprise_profile_entity_build_defaults_alter(array &$build, EntityInterface $entity, $view_mode) {
  $renderer = \Drupal::service('renderer');
  if ($entity instanceof Node && $view_mode === 'card') {

    $is_view = $entity->view;
    $image_style = $is_view ? 'grid_card_views' : 'grid_card';
    // Get the parent and its bundle to see if we're in related content.
    // Verify that methods are available before trying to call them.
    $referringItem = $entity->_referringItem;

    $parent = NULL;
    $bundle = NULL;
    // If first val passed to method_exists() is null, PHP8 produces a 503.
    if (!is_null($referringItem)) {
      $parent = method_exists($referringItem, 'getEntity') ? $referringItem->getEntity() : NULL;
      $bundle = method_exists($parent, 'bundle') ? $parent->bundle() : NULL;
    }
    // Conditionally set image styles based on the number of cards in related
    // content.
    if ($parent !== NULL && $bundle === 'related_content') {
      // Check for count of related content items.
      $referenced_entity_array = $parent->get('field_rc_content')->referencedEntities();
      $referenced_entity_count = count($referenced_entity_array);
      // Set the image style based on number of cards and pass it in $build.
      switch (TRUE) {
        case $referenced_entity_count === 1:
          $image_style = 'spotlight_card';
          break;

        case $referenced_entity_count === 2:
          $image_style = 'grid_card_two';
          break;

        case $referenced_entity_count >= 4:
          $image_style = 'grid_card_four';
          break;

        default:
          $image_style = 'grid_card';
          break;

      }
    }
    $build['#image_style'] = $image_style;
    // Cache each grid card view mode separately.
    $cache_contexts = $entity->getCacheContexts();
    $cache_contexts = Cache::mergeContexts($cache_contexts, ['card_count']);
    $build['#cache']['keys'] = ['node', $entity->id(), $image_style];
    $build['#cache']['contexts'] = $cache_contexts;
    $renderer->addCacheableDependency($build, $entity);
  }
}

/**
 * Implements template_preprocess_paragraph().
 */
function ul_enterprise_profile_preprocess_paragraph(&$variables) {
  $paragraph_type = $variables['elements']['#paragraph']->getParagraphType()->id();
  $sidebar_links = [];
  if ($paragraph_type === 'sidebar_curated_related') {
    $related = $variables['content']['field_rr_curated_references'];
    foreach ($related as $k => $v) {
      if (is_numeric($k)) {
        $sidebar_links[] = $v;
      }
    }
    $custom_link = $variables['content']['field_rr_curated_link'][0] ?? NULL;
    if ($custom_link) {
      $sidebar_links[] = $custom_link;
    }
    $variables['content']['sidebar_links'] = $sidebar_links;
  }
}

/**
 * Implements hook_entity_view_mode_alter().
 */
function ul_enterprise_profile_entity_view_mode_alter(&$view_mode, EntityInterface $entity, $context) {
  if ($entity->getEntityTypeId() === 'node' && $entity->hasField('field_shared_rr_checkbox') && $view_mode === 'full') {
    $checkbox_value = $entity->get('field_shared_rr_checkbox')->value;
    if ($checkbox_value) {
      $view_mode = 'right_rail_display';
    }
  }
}

<?php

/**
 * @file
 * Enables modules and site configuration for a UL Base site install.
 *
 * Note: This was taken from the standard.profile.
 */

use Drupal\Core\Url;
use Drupal\Core\Site\Settings;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\InstallStorage;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Implements hook_pathauto_punctuation_chars_alter().
 */
function ul_base_profile_pathauto_punctuation_chars_alter(array &$punctuation) {
  $punctuation['copyright'] = [
    'value' => '©',
    'name' => t('Copyright'),
  ];
  $punctuation['registered'] = [
    'value' => '®',
    'name' => t('Registered trademark'),
  ];
  $punctuation['trademark'] = [
    'value' => '™',
    'name' => t('Trademark'),
  ];
  $punctuation['opensinglecurlyquote'] = [
    'value' => '‘',
    'name' => t('Opening single curly quote'),
  ];
  $punctuation['closesinglecurlyquote'] = [
    'value' => '’',
    'name' => t('Closing single curly quote'),
  ];
  $punctuation['opendoublecurlyquote'] = [
    'value' => '“',
    'name' => t('Opening double curly quotes'),
  ];
  $punctuation['closedoublecurlyquote'] = [
    'value' => '”',
    'name' => t('Closing double curly quotes'),
  ];
  $punctuation['invertedquestion'] = [
    'value' => '¿',
    'name' => t('Inverted question mark'),
  ];
  $punctuation['invertedexclamation'] = [
    'value' => '¡',
    'name' => t('Inverted exclamation mark'),
  ];
}

/**
 * Implements hook_config_ignore_settings_alter().
 */
function ul_base_profile_config_ignore_settings_alter(array &$settings) {

  // Default ignore list.
  $ignore = [
    'config_split.config_split.*:status',
    'datalayer.settings',
    'google_tag.*',
    'language.entity.*',
    'language.negotiation',
    'metatag.metatag_defaults.global:tags.canonical_url',
    'metatag.metatag_defaults.node:tags.canonical_url',
    'metatag.metatag_defaults.front',
    'pathauto.pattern.*',
    'samlauth.*',
    'shield.settings:credentials',
    'shield.settings:shield_enable',
    'simple_sitemap.settings:base_url',
    'smtp.settings:smtp_password',
    'system.action.book_add_node_action.*',
    'system.action.book_remove_node_action.*',
    'system.site',
    'system.theme:default',
    'ul_disable_content.metadata.settings',
    'ul_marketo.metadata.settings',
    'ul_samlauth.*',
    'ul_crc.*',
    '~views.view.media_library',
    'views.view.experience_hub_events:display.default.display_options.empty.area.content.value',
    'tmgmt.translator.globallink:settings.pd_url',
    'tmgmt.translator.globallink:settings.pd_username',
    'tmgmt.translator.globallink:settings.pd_password',
    'tmgmt.translator.globallink:settings.pd_projectid',
    'field.storage.node.field_shared_marketo_forms:langcode',
    '~block.block.claro*',
  ];

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
 * Implements hook_robotstxt().
 */
function ul_base_profile_robotstxt() {

  // Adding sitemap URL to robotstxt.
  // Use Simple XML sitemap route if the module is enabled.
  try {
    $url = Url::fromRoute('simple_sitemap.sitemap_default', [], [
      'absolute' => TRUE,
    ]);

    // This gets passed to a cacheable response so we need to make sure that
    // the string doesn't get passed through the render pipeline too early
    // otherwise it will trigger a fatal error.
    $string = $url->toString(TRUE)->getGeneratedUrl();

    return [
      'Sitemap: ' . $string,
    ];
  }
  // Do nothing if Simple XML Sitemap is not enabled and route is not found.
  catch (RouteNotFoundException $e) {

  }

}

/**
 * Helper function for retrieving install config from a module.
 *
 * @param int $id
 *   The configuration object ID.
 * @param string $module
 *   The module having it's configuration retrieved.
 *
 * @return mixed
 *   The configuration information from the module.
 */
function ul_base_profile_read_config($id, $module) {
  // Statically cache all FileStorage objects, keyed by module.
  static $storage = [];

  if (empty($storage[$module])) {
    $dir = \Drupal::service('module_handler')->getModule($module)->getPath();
    $storage[$module] = new FileStorage($dir . '/' . InstallStorage::CONFIG_INSTALL_DIRECTORY);
  }
  return $storage[$module]->read($id);
}

/**
 * Reads a stored config file from config sync directory.
 *
 * @param string $id
 *   The config ID.
 *
 * @return array
 *   The config data.
 */
function ul_base_profile_read_config_from_sync($id, $config_key = 'sync') {
  // Statically cache FileStorage object.
  static $storage;

  if (empty($storage)) {
    $config_directory = Settings::get('config_sync_directory');
    $storage = new FileStorage($config_directory);
  }
  return $storage->read($id);
}

/**
 * Implements hook_entity_base_field_info_alter().
 */
function ul_base_profile_entity_base_field_info_alter(&$fields, EntityTypeInterface $entity_type) {
  if ($entity_type->id() === 'redirect') {
    if (isset($fields['redirect_source'])) {
      $fields['redirect_source']->addConstraint('RedirectSource', []);
    }
  }
}

/**
 * Implements hook_form_alter().
 */
function ul_base_profile_form_alter(&$form, FormStateInterface $form_state, $form_id) {

  // Make it impossible to add a new block of type: Marketo Form.
  if ($form_id == 'block_content_mkto_form_form') {
    unset(
      $form['info'],
      $form['revision_log'],
      $form['revision_information'],
      $form['revision'],
      $form['field_mkto_button_text'],
      $form['field_mkto_campaign'],
      $form['field_mkto_form_descr'],
      $form['field_mkto_form_id'],
      $form['field_mkto_form_title'],
      $form['field_mkto_lang'],
      $form['field_mkto_last_interest'],
      $form['field_mkto_thank_you'],
      $form['actions'],
      $form['footer'],
    );
    $form['field_message'] = [
      '#type' => 'item',
      '#title' => 'Deprecated!',
      '#markup' => 'Sorry, it is no longer possible to add this type of block. Please go back.',
    ];
  }
}

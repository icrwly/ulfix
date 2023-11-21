<?php

/**
 * @file
 * Contains ul_marketo.page.inc.
 *
 * Page callback for Marketo Form entities.
 */

use Drupal\Component\Uuid\Uuid;
use Drupal\ul_marketo\Entity\MarketoForm;
use Drupal\Core\Url;
use Drupal\Core\Routing\RouteObjectInterface;

/**
 * Prepares variables for Marketo templates.
 *
 * Default template: marketo_form.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_marketo_form(array &$variables) {
  /* Skip preprocessing for views & batch operations to avoid errors during csv exports. */
  $route_name = \Drupal::routeMatch()->getRouteName();
  if ($route_name == 'system.batch_page.json' || substr($route_name, 0, 5) == 'view.') {
    return;
  }

  // Is this a "default" form (aka "fallback" form).
  $is_default = FALSE;

  // Site configs.
  $site_config = \Drupal::config('system.site');

  // The Marketo Metadata Settings.
  $mkto_metadata = \Drupal::config('ul_marketo.metadata.settings');

  /** @var Drupal\ul_marketo\UlMarketoServiceInterface $marketo */
  $marketo = \Drupal::service('ul_marketo');

  // Get all the instance settings.
  $mkto_instance_settings = $marketo->getInstanceSettings();

  /** @var Drupal\ul_marketo\Entity\MarketoForm $marketo_form */
  if (isset($variables['elements']['#marketo_form'])) {
    $marketo_form = $variables['elements']['#marketo_form'] instanceof MarketoForm ? $variables['elements']['#marketo_form'] : '';
  }
  elseif (isset($variables['elements']['#marketo']['form_entity'])) {
    $marketo_form = $variables['elements']['#marketo']['form_entity'] instanceof MarketoForm ? $variables['elements']['#marketo']['form_entity'] : '';
  }
  else {
    $marketo_form = FALSE;
  }

  // The site name.
  $site_name = $site_config->get('name');
  $variables['marketo']['site_name'] = $site_name;

  // Module handler:
  $module_handler = \Drupal::service('module_handler');

  // Module path:
  $path = $module_handler->getModule('ul_marketo')->getPath();

  // Default "opt-in" file:
  $optin_file = '/' . $path . '/js/gdpr-mssg/default.js';

  // Emergo "opt-in" file:
  if (str_contains(strtolower($variables['marketo']['site_name']), 'emergo')) {
    $optin_file = '/' . $path . '/js/gdpr-mssg/emergo.js';
  }

  // Opt-in file:
  $variables['marketo']['optin_file'] = $optin_file;

  // Form Language.
  $form_language = \Drupal::languageManager()->getCurrentLanguage()->getId();
  $variables['marketo']['form_language'] = $form_language;

  // The reCaptcha site key:
  $recaptcha_site_key = $mkto_metadata->get('recaptcha_site_key');
  $variables['marketo']['recaptcha_site_key'] = $recaptcha_site_key;

  // "Environment".
  $mkto_env = $mkto_metadata->get('env');
  $variables['marketo']['mkto_env'] = $mkto_env;

  // Marketo instance.
  $mkto_instance = 'Enterprise';
  $variables['marketo']['instance'] = $mkto_instance;

  // Munchkin ID:
  $munchkin_code = $mkto_instance_settings[$mkto_instance]['munchkin_code'] ?? NULL;
  $variables['marketo']['munchkin_code'] = $munchkin_code;

  // Base URL:
  $base_url = $mkto_instance_settings[$mkto_instance]['base_url'] ?? NULL;
  $variables['marketo']['base_url'] = $base_url;

  // If $marketo_form object, get the mkto bundle (aka form type).
  if ($marketo_form) {
    $mkto_bundle = $marketo_form->bundle() ?? NULL;
    $variables['marketo']['mkto_bundle'] = $mkto_bundle;
  }

  // If NOT "preference center":
  if ($mkto_bundle !== 'mkto_pref_ctr') {

    $node_form_settings = FALSE;
    $uuid = FALSE;
    $node = $marketo_form ? $marketo_form->getParentNode($marketo_form) : NULL;

    // If node doesn't exist then look for node in the URL.
    // Workaround - node values should be saved w/marketo form on submit.
    if (empty($node)) {
      // Last Interest (default).
      $variables['marketo']['last_interest'] = 'General Contact';

      /** @var Drupal\Core\Entity\EntityRepositoryInterface $entity_repository */
      $entity_repository = \Drupal::service('entity.repository');

      /** @var Symfony\Component\HttpFoundation\ParameterBag $parameters */
      $parameters = \Drupal::routeMatch()->getParameters();

      $param_array = $parameters->all();
      if (isset($param_array['identifier']) && Uuid::isValid($param_array['identifier'])) {
        $uuid = $parameters->get('identifier');
      }

      if ($uuid) {
        $node = $entity_repository->loadEntityByUuid('node', $uuid);
        if ($node->hasField('field_shared_marketo_forms')) {
          if ($node->get('field_shared_marketo_forms')->isEmpty()) {
            // This is using a "default" form:
            $is_default = TRUE;
          }
        }
      }
    }
    // If node and is not a default form.
    elseif (!empty($node) && !$is_default) {
      // If the node has the Marketo Forms field:
      if ($node->hasField('field_shared_marketo_forms')) {
        // Node Form Settings:
        $node_form_settings = !$node->get('field_shared_marketo_forms')->isEmpty() ? $node->get('field_shared_marketo_forms') : FALSE;
        // Marketo Entity Settings:
        $settings = $marketo->getEntitySettings($node);
        // Add what is needed into the TWIG context:
        $variables['marketo']['node_nid'] = $node->id();
        $variables['marketo']['node_uuid'] = $node->uuid();
        $variables['marketo']['node_title'] = $node->label();
        $variables['marketo']['mkto_campaign'] = $node->field_shared_marketo_forms->mkto_campaign ?? NULL;
        $variables['marketo']['last_interest'] = $node_form_settings[0]->get('last_interest')->getString() ?? NULL;
      }

      // If $settings is an array, populate `$variables['marketo']` array.
      if (is_array($settings)) {
        $variables['marketo']['button_text'] = $settings['field_shared_button_text'];
        if (array_key_exists('field_shared_cta_button_text', $settings)) {
          $variables['marketo']['cta_button_text'] = $settings['field_shared_cta_button_text'];
        }
        $variables['marketo']['form_title'] = $settings['field_shared_custom_title'];
        $variables['marketo']['form_description'] = $settings['field_shared_form_description'];

        // Email form:
        if ($marketo_form == 'email_form') {
          // If there is a Newsletter Type:
          if ($settings['field_newsletter_type']) {
            $variables['marketo']['newsletter_item'] = $settings['field_newsletter_type'];
          }
        }

        // If Gated Content Form.
        if ($mkto_bundle == 'gated_content_form') {
          $variables['marketo']['button_text'] = $settings['field_shared_button_text'];
          $variables['marketo']['asset_lang'] = $settings['field_gated_asset_language'];
          $variables['marketo']['asset_title'] = $settings['field_gated_asset_title'];
          $variables['marketo']['num_paragraphs'] = $settings['field_num_paragraphs'];
        }
        // Elseif there is a newsletter item:
        elseif (isset($settings['field_newsletter_type'])) {
          $variables['marketo']['newsletter_item'] = $settings['field_newsletter_type'];
        }

        // If the form is configurable:
        if ('contact_form_configurable' == $mkto_bundle) {
          require dirname(__FILE__) . '/ul_marketo.configure_fields.php';
        }
      }

      // Get the Form IDs.
      $mkto_form_Ids = isset($settings['marketo_form_id']) ? $settings['marketo_form_id'] : [];
      // Filter to get the one form ID needed based on instance and environment:
      foreach ($mkto_form_Ids as $key => $value) {
        if (
          is_numeric($key)
          && $value['filters']['instance'] == $mkto_instance
          && $value['filters']['env'] == $mkto_env
        ) {
          $variables['marketo']['form_id'] = $value['value'];
          break;
        }
      }

      // If the marketo form does not have an ID then its probably being loaded
      // as a block or on the default page. In this case we can pass the rest of
      // the node settings, specifically the node's block override settings.
      if (!$marketo_form instanceof MarketoForm && is_array($settings)) {
        $variables['marketo'] += $settings;
      }
    }
  }
  // ELSE, this IS "preference center":
  else {
    $prefcenter_config = \Drupal::config('ul_marketo.marketo_form_type.mkto_pref_ctr');
    $prefcenter_data = $prefcenter_config->getRawData();
    $prefcenter_forms = $prefcenter_data['marketo_form_id'];
    foreach ($prefcenter_forms as $key => $value) {
      if (is_numeric($key)) {
        $env = $prefcenter_forms[$key]['filters']['env'];
        if ($env == $mkto_env) {
          $variables['marketo']['form_id'] = $value['value'];
          break;
        }
      }
    }
    // Unset the Pref Center title. We use a block instead.
    unset($variables['marketo']['form_title']);
    // Add a default description.
    $variables['marketo']['form_description'] = 'Preference Center';
    // Add Last Interest value.
    $variables['marketo']['last_interest'] = 'General Contact';
  }

  // If marketo form object:
  if ($marketo_form instanceof MarketoForm) {
    // Filters used to get the right form/ID:
    $filters = [
      'instance' => $mkto_instance,
      'langcode' => $form_language,
      'env' => $mkto_env,
    ];

    if ($mkto_bundle !== 'mkto_pref_ctr') {
      $variables['marketo']['form_id'] = $marketo_form->getMarketoFormId($filters);
      $variables['marketo']['form_title'] = $marketo_form->get('field_shared_custom_title')->view()['#items']->first()->getValue()['value'];
      if (array_key_exists('#items', $marketo_form->get('field_shared_form_description')->view())) {
        $variables['marketo']['form_description'] = $marketo_form->get('field_shared_form_description')->view()['#items']->first()->getValue()['value'];
      }
    }

    // Try to load "Default" values!
    $variables['marketo']['page_url'] = $marketo_form->getPageUrl();
    $variables['marketo']['success_url'] = $marketo_form->getSuccessUrl();
    $variables['marketo']['button_text'] = $marketo_form->get('field_shared_button_text')->getString();
  }

  // If there is a success url:
  if (isset($variables['marketo']['success_url']) && $marketo_form->hasField('field_shared_thank_you_page')) {
    // Custom Thank You page:
    $thankyou_page = $marketo_form->get('field_shared_thank_you_page')->getValue();

    if (is_array($thankyou_page) && isset($thankyou_page[0]['target_id'])) {
      // Node ID for custom thank you page.
      $thankyou_page_id = $thankyou_page[0]['target_id'];

      // Get the URL/Path from the Node ID:
      $url = Url::fromRoute(
        'entity.node.canonical',
        ['node' => $thankyou_page_id],
        ['absolute' => FALSE]
      );

      $variables['marketo']['success_url'] = $url->toString();
      $variables['marketo']['custom_thank_you_url'] = $url->toString();
    }
  }

  // If the form is configurable:
  if ($mkto_bundle == 'contact_form_configurable') {
    $variables['marketo']['configurable'] = TRUE;
    _mkto_check_vals($variables);
  }
  else {
    $variables['marketo']['configurable'] = FALSE;
  }

  // Is this a Horizontal?
  if (empty($variables['marketo']['last_interest']) || $variables['marketo']['last_interest'] == 'General Contact' || $variables['marketo']['last_interest'] == 'General Contact - Software') {
    $variables['marketo']['is_horizontal'] = TRUE;
    if ($variables['marketo']['last_interest'] == 'General Contact - Software') {
      $variables['marketo']['is_horizontal_soft'] = TRUE;
    }
    else {
      $variables['marketo']['is_horizontal_soft'] = FALSE;
    }
  }
  else {
    $variables['marketo']['is_horizontal'] = FALSE;
  }

  // Set the Data Layer Event (used on success event):
  switch ($mkto_bundle) {
    case 'event_form':
      $variables['marketo']['dataLayerEvent'] = 'Event Registration Form Submit';
      break;

    case 'email_form':
      $variables['marketo']['dataLayerEvent'] = 'Newsletter Signup Form Submit';
      break;

    case 'gated_content_form':
      $variables['marketo']['dataLayerEvent'] = 'Gated Content Form Submit';
      break;

    case 'mkto_pref_ctr':
      $variables['marketo']['dataLayerEvent'] = 'Preference Center Form Submit';
      break;

    default:
      $variables['marketo']['dataLayerEvent'] = 'Contact Us Form Submit';
      break;
  }

  // Pass values to Marketo for the generic contact pages that
  // do not have UUIDs:
  if (!isset($variables['marketo']['node_uuid'])) {
    // Contact form, old:
    if (str_contains($variables['marketo']['page_url'], '/sales-inquiries')) {
      $variables['marketo']['node_title'] = 'Generic Sales Inquiries';
      $variables['marketo']['last_interest'] = 'General Contact';
    }
    // Contact form, new:
    elseif (str_contains($variables['marketo']['page_url'], '/sales-inquiry')) {
      $variables['marketo']['node_title'] = 'Generic Sales Inquiry';
      $variables['marketo']['last_interest'] = 'General Contact';
    }
  }

  // Update page titles / browser tab titles:
  _update_page_titles($variables, $marketo_form);

  // Pass values into JS context:
  foreach ($variables['marketo'] as $key => $value) {
    $variables['#attached']['drupalSettings']['marketo'][$key] = $value;
  }

  // Attach library items:
  if (str_contains(strtolower($site_name), 'emergo')) {
    $variables['#attached']['library'][] = 'ul_marketo/ul_marketo.gdpr_emergo';
  }
  else {
    $variables['#attached']['library'][] = 'ul_marketo/ul_marketo.gdpr_default';
  }

  // Add Marketo JS files:
  $variables['#attached']['library'][] = 'ul_marketo/ul_marketo.mkto_api';
  $variables['#attached']['library'][] = 'ul_marketo/ul_marketo.form_helpers';

  // Add JS for configurable forms:
  if ($mkto_bundle == 'contact_form_configurable') {
    $variables['#attached']['library'][] = 'ul_marketo/ul_marketo.custom_validation';
  }

  // Should this form display in a modal?
  $variables['marketo']['is_modal'] = FALSE;

  // Forms that may be in a modal:
  $modal_forms = ['generic_form', 'contact_form_configurable'];

  if (in_array($mkto_bundle, $modal_forms)) {
    // Get the current URL/Path:
    $curr_path = \Drupal::service('path.current')->getPath();
    $old_contact_path = '/sales-inquiries';
    $new_contact_path = '/sales-inquiry';

    if ($curr_path != $old_contact_path
      && $curr_path != $new_contact_path
      && !str_contains($curr_path, $old_contact_path)
      && !str_contains($curr_path, $new_contact_path)
    ) {

      $node_type = FALSE;
      if ($node = \Drupal::routeMatch()->getParameter('node')) {
        $node_type = $node->bundle();
      }

      // If this is not a campaign page:
      if ($node_type && $node_type != 'campaign_page') {
        $variables['marketo']['is_modal'] = TRUE;
        $variables['#attached']['library'][] = 'ul_marketo/ul_marketo.modal';
      }
    }
  }
}

/**
 * Helper function to ensure we have required fields defined.
 */
function _mkto_check_vals(array &$variables) {
  // If the basic required fields are not set:
  if (!isset($variables['marketo']['fields']['required'])
    || !is_array($variables['marketo']['fields']['required'])
    || count($variables['marketo']['fields']['required']) < 5
  ) {
    $variables['marketo']['fields']['required'][] = '#FirstName';
    $variables['marketo']['fields']['required'][] = '#LastName';
    $variables['marketo']['fields']['required'][] = '#Company';
    $variables['marketo']['fields']['required'][] = '#Email';
    $variables['marketo']['fields']['required'][] = '#Phone';
    $variables['marketo']['fields']['required'][] = '#Country';
    $variables['marketo']['fields']['required'][] = '#purchaseTimeframe';
    $variables['marketo']['fields']['required'][] = '#customerComments';
    $variables['marketo']['fields']['required'][] = '#uLServiceSegment';
    $variables['marketo']['fields']['required'][] = '#uLCatalogIndustry';
    $variables['marketo']['fields']['required'][] = '#oPSPreferredLanguage';
  }
  // If form language is Japanese, add the extra required fields:
  if ($variables['marketo']['form_language'] == 'ja') {
    $variables['marketo']['fields']['required'][] = '#translatedLastName';
    $variables['marketo']['fields']['required'][] = '#translatedFirstName';
    $variables['marketo']['fields']['required'][] = '#translatedCompanyName';
  }
}

/**
 * Helper function to rewrite the page title and browser tab title.
 */
function _update_page_titles(array &$variables, $marketo_form) {
  // Page request:
  $request = \Drupal::request();
  // If routed page:
  if ($route = $request->attributes->get(RouteObjectInterface::ROUTE_OBJECT)) {
    // If Preference Center:
    if (isset($variables['marketo']['page_url']) && $variables['marketo']['page_url'] == '/preference-center') {
      $route->setDefault('_title', 'Communications Preferences');
    }
    // Else, other routed page:
    else {
      $title = $marketo_form->get('field_shared_custom_title')->getValue();
      if (isset($title[0]['value'])) {
        $route->setDefault('_title', $title[0]['value']);
      }
      else {
        $route->setDefault('_title', 'General Contact');
      }
    }
  }
}

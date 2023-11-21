<?php

/**
 * @file
 * Contains ul_marketo.page.inc.
 *
 * Page callback for Marketo Form entities.
 */

/**
 * These fields may be altered: Required, Optional, or Hidden.
 */
$configurable_fields = [
  'field__customer_comments',
  'field__phone_number',
  'field__preferred_language',
  'field__purchase_timeframe',
  'field__job_title',
  'field__department',
];

// Loop through, and try to get value (field setting):
foreach ($configurable_fields as $val) {
  if (isset($settings[$val])) {
    // Map the Drupal field name to the Marketo field name:
    // Adding "#" to the field ID, for jQuery:
    switch ($val) {
      case 'field__customer_comments':
        $field = '#customerComments';
        break;

      case 'field__phone_number':
        $field = '#Phone';
        break;

      case 'field__department':
        $field = '#Department';
        break;

      case 'field__job_title':
        $field = '#Title';
        break;

      case 'field__preferred_language':
        $field = '#oPSPreferredLanguage';
        break;

      case 'field__purchase_timeframe':
        $field = '#purchaseTimeframe';
        break;
    }

    // Add the field name to the correct category (option):
    if ($settings[$val] == 'hidden') {
      $variables['marketo']['fields']['hidden'][] = $field;
    }
    elseif ($settings[$val] == 'required') {
      $variables['marketo']['fields']['required'][] = $field;
    }
  }
}

// Always required fields:
$always_required = [
  '#FirstName',
  '#LastName',
  '#Company',
  '#Email',
  '#Country',
  '#uLServiceSegment',
  '#uLCatalogIndustry',
];

// Add them to the array we pass to TWIG:
foreach ($always_required as $val) {
  $variables['marketo']['fields']['required'][] = $val;
}

// If form language is Japanese, add the extra required fields:
if (isset($form_language) && $form_language == 'ja') {
  $variables['marketo']['fields']['required'][] = '#translatedLastName';
  $variables['marketo']['fields']['required'][] = '#translatedFirstName';
  $variables['marketo']['fields']['required'][] = '#translatedCompanyName';
}

<?php

namespace Drupal\ul_testing;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Provides tools for validating test node data and setting default values.
 */
class ValidationService {
  use StringTranslationTrait;

  /**
   * Constructs a MyClass object.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(TranslationInterface $string_translation) {
    // You can skip injecting this service, the trait will fall back to
    // \Drupal::translation(), but it is recommended to do so,
    // for easier testability.
    $this->stringTranslation = $string_translation;

    // Read the allowed newsletter types from the field configuration.
    $allowed_values = \Drupal::config('field.storage.marketo_form.field_newsletter_type')->get('settings')['allowed_values'];
    foreach ($allowed_values as $a) {
      $this->newsletter_types[] = $a['value'];
    }

    // Get the term ids for the content domain values.
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('content_domain');

    // Create an array mapping term name to term id.
    $content_domain_terms = [];

    foreach ($terms as $term) {
      $content_domain_terms[$term->name] = $term->tid;
    }

    // Update content_domain_term_id_map to map content type to the correct
    // Content Domain term id.
    foreach ($this->content_domain as $type => $term_name) {
      $this->content_domain_term_id_map[$type] = $content_domain_terms[$term_name];
    }

  }

  /**
   * The $types array defines the supported fields and values by content type.
   *
   * The array uses the format
   * <type> = [
   *   'content_field' => <name of content field>,
   *   'header_cta' => <array of allowed header_cta forms
   *   'rr_cta' => <array of allowed rr_cta forms
   *   'campaign_form' => <array of allowed campaing forms, campaign pages only>
   *   'marketo_form' => <array of allowed marketo forms>,
   *   'text_and_form' => <array of allowed marketo forms>,
   * ]
   *
   * Supported Types
   *   campaign_page
   *   events
   *   help
   *   homepage
   *   hub
   *   knowledge
   *   landing_page
   *   location
   *   news
   *   offering
   *   page
   *   person
   *   resource
   *   thankyou_pages
   *   tool
   *
   * @var types
   */
  protected $types = [
    'campaign_page' => [
      'content_field' => 'field_campaign_page_content',
      'campaign_form' => [
        'generic_form',
        'event_form',
      ],
      'content_paragraph_types' => [
        'campaign_cards',
      ],
      'auto_field' => [
        'field_shared_ref_description',
        'field_shared_mktg_support_ticket',
        'field_shared_marketo_forms',
        'field_shared_subtitle',
        'field_shared_content_owner',
        'field_shared_cou',
        'field_shared_header_media',
      ],
    ],
    'event' => [
      'content_field' => 'field_event_content',
      'header_cta' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'rr_cta' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'marketo_form' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'content_paragraph_types' => [
        'basic_content',
        'accordion',
        'crc_files',
        'related_content',
        'curated_separate_list',
        'files',
        'image_gallery',
        'image_row',
        'interrupter_cta',
        'people',
        'quote',
        'reference',
        'spotlight',
        'video',
        'view',
      ],
      'auto_field' => [
        'field_shared_ref_description',
        'field_shared_mktg_support_ticket',
        'field_shared_domain',
        'field_event_type',
        'field_event_date',
        'field_event_timezone',
        'field_shared_marketo_forms',
        'field_shared_content_owner',
        'field_shared_cou',
        'field_shared_header_media',
      ],
    ],
    'help' => [
      'content_field' => 'field_help_content',
      'header_cta' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'rr_cta' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'marketo_form' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'content_paragraph_types' => [
        'basic_content',
        'accordion',
        'crc_files',
        'related_content',
        'files',
        'image_gallery',
        'image_row',
        'interrupter_cta',
        'people',
        'quote',
        'reference',
        'spotlight',
        'video',
        'view',
      ],
      'auto_field' => [
        'field_shared_ref_description',
        'field_shared_mktg_support_ticket',
        'field_shared_domain',
        'field_help_type',
        'field_shared_marketo_forms',
        'field_shared_content_owner',
        'field_shared_cou',
        'field_shared_header_media',
      ],
    ],
    'homepage' => [
      'content_field' => 'field_homepage_content',
      'header_cta' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'marketo_form' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'content_paragraph_types' => [
        'basic_content',
        'accordion',
        'crc_files',
        'related_content',
        'files',
        'image_gallery',
        'image_row',
        'interrupter_cta',
        'people',
        'quote',
        'reference',
        'spotlight',
        'video',
        'view',
      ],
      'auto_field' => [
        'field_shared_marketo_forms',
        'field_shared_subtitle',
        'field_shared_content_owner',
        'field_shared_cou',
        'field_shared_header_media',
      ],
    ],
    'hub' => [
      'content_field' => 'field_hub_content',
      'header_cta' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'marketo_form' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'content_paragraph_types' => [
        'basic_content',
        'accordion',
        'crc_files',
        'related_content',
        'files',
        'image_gallery',
        'image_row',
        'interrupter_cta',
        'people',
        'quote',
        'reference',
        'spotlight',
        'video',
        'view',
      ],
      'auto_field' => [
        'field_shared_ref_description',
        'field_shared_mktg_support_ticket',
        'field_shared_domain',
        'field_shared_marketo_forms',
        'field_shared_subtitle',
        'field_shared_content_owner',
        'field_shared_cou',
      ],
    ],
    'knowledge' => [
      'content_field' => 'field_know_content',
      'header_cta' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'rr_cta' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'marketo_form' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'text_and_form' => [
        'generic_form',
      ],
      'content_paragraph_types' => [
        'basic_content',
        'accordion',
        'crc_files',
        'related_content',
        'curated_separate_list',
        'files',
        'image_gallery',
        'image_row',
        'interrupter_cta',
        'people',
        'quote',
        'reference',
        'spotlight',
        'video',
        'view',
      ],
      'auto_field' => [
        'field_shared_ref_description',
        'field_shared_mktg_support_ticket',
        'field_shared_domain',
        'field_know_type',
        'field_shared_marketo_forms',
        'field_shared_content_owner',
        'field_shared_cou',
        'field_shared_header_media',
      ],
    ],
    'landing_page' => [
      'content_field' => 'field_landing_page_content',
      'header_cta' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'rr_cta' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'marketo_form' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'text_and_form' => [
        'generic_form',
      ],
      'content_paragraph_types' => [
        'basic_content',
        'accordion',
        'crc_files',
        'related_content',
        'curated_separate_list',
        'files',
        'image_gallery',
        'image_row',
        'interrupter_cta',
        'people',
        'quote',
        'reference',
        'spotlight',
        'video',
        'view',
      ],
      'auto_field' => [
        'field_shared_ref_description',
        'field_shared_mktg_support_ticket',
        'field_shared_domain',
        'field_shared_marketo_forms',
        'field_shared_subtitle',
        'field_shared_content_owner',
        'field_shared_cou',
        'field_shared_header_media',
      ],
    ],
    'location' => [
      'auto_field' => [
        'field_location_region',
        'field_location_addresses',
        'field_shared_mktg_support_ticket',
        'field_shared_content_owner',
        'field_shared_cou',
      ],
    ],
    'news' => [
      'content_field' => 'field_news_content',
      'header_cta' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'rr_cta' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'marketo_form' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'content_paragraph_types' => [
        'basic_content',
        'crc_files',
        'related_content',
        'curated_separate_list',
        'files',
        'image_gallery',
        'image_row',
        'interrupter_cta',
        'people',
        'quote',
        'reference',
        'spotlight',
        'video',
        'view',
      ],
      'auto_field' => [
        'field_shared_ref_description',
        'field_shared_mktg_support_ticket',
        'field_shared_domain',
        'field_news_type',
        'field_shared_marketo_forms',
        'field_shared_content_owner',
        'field_shared_cou',
        'field_shared_header_media',
      ],
    ],
    'offering' => [
      'content_field' => 'field_of_content',
      'header_cta' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'rr_cta' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'marketo_form' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'content_paragraph_types' => [
        'basic_content',
        'accordion',
        'crc_files',
        'related_content',
        'curated_separate_list',
        'files',
        'image_gallery',
        'image_row',
        'interrupter_cta',
        'people',
        'quote',
        'reference',
        'spotlight',
        'video',
        'view',
      ],
      'auto_field' => [
        'field_shared_ref_description',
        'field_shared_mktg_support_ticket',
        'field_shared_domain',
        'field_view_mode_hero',
        'field_shared_header_description',
        'field_of_service_category',
        'field_shared_marketo_forms',
        'field_shared_content_owner',
        'field_shared_cou',
        'field_shared_header_media',
      ],
    ],
    'page' => [
      'content_field' => 'field_page_content',
      'header_cta' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'rr_cta' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'marketo_form' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'content_paragraph_types' => [
        'basic_content',
        'accordion',
        'crc_files',
        'related_content',
        'curated_separate_list',
        'files',
        'image_gallery',
        'image_row',
        'interrupter_cta',
        'people',
        'quote',
        'reference',
        'spotlight',
        'video',
        'view',
      ],
      'auto_field' => [
        'field_shared_ref_description',
        'field_shared_mktg_support_ticket',
        'field_shared_domain',
        'field_shared_marketo_forms',
        'field_shared_subtitle',
        'field_shared_content_owner',
        'field_shared_cou',
      ],
    ],
    'person' => [
      'auto_field' => [
        'field_person_first_name',
        'field_person_job_title',
        'field_person_short_bio',
        'body',
        'field_shared_ref_description',
        'field_shared_mktg_support_ticket',
        'field_shared_content_owner',
        'field_shared_cou',
      ],
    ],
    'resource' => [
      'content_field' => 'field_resource_content',
      'header_cta' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'rr_cta' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'marketo_form' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'text_and_form' => [
        'generic_form',
      ],
      'content_paragraph_types' => [
        'basic_content',
        'accordion',
        'crc_files',
        'related_content',
        'curated_separate_list',
        'files',
        'image_gallery',
        'image_row',
        'interrupter_cta',
        'people',
        'quote',
        'reference',
        'spotlight',
        'video',
        'view',
      ],
      'auto_field' => [
        'field_shared_ref_description',
        'field_shared_mktg_support_ticket',
        'field_shared_domain',
        'field_resource_type',
        'field_shared_marketo_forms',
        'field_shared_content_owner',
        'field_shared_cou',
        'field_shared_header_media',
      ],
    ],
    'thankyou_pages' => [
      'content_field' => 'field_thankyou_page_content',
      'content_paragraph_types' => [
        'basic_content',
        'call_to_action',
        'campaign_cards',
        'accordion',
        'crc_files',
        'related_content',
        'curated_separate_list',
        'text_and_form',
        'files',
        'image_gallery',
        'image_row',
        'interrupter_cta',
        'marketo_form',
        'people',
        'quote',
        'reference',
        'spotlight',
        'video',
        'view',
      ],
      'marketo_form' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'text_and_form' => [
        'generic_form',
      ],
      'auto_field' => [
        'field_shared_mktg_support_ticket',
        'field_shared_marketo_forms',
        'field_shared_content_owner',
        'field_shared_cou',
      ],
    ],
    'tool' => [
      'content_field' => 'field_tool_content',
      'header_cta' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'marketo_form' => [
        'generic_form',
        'email_form',
        'event_form',
      ],
      'text_and_form' => [
        'generic_form',
      ],
      'content_paragraph_types' => [
        'basic_content',
        'accordion',
        'crc_files',
        'related_content',
        'curated_separate_list',
        'files',
        'image_gallery',
        'image_row',
        'interrupter_cta',
        'people',
        'quote',
        'spotlight',
        'video',
        'view',
      ],
      'auto_field' => [
        'field_shared_ref_description',
        'field_shared_mktg_support_ticket',
        'field_shared_domain',
        'field_tool_types',
        'field_shared_marketo_forms',
        'field_shared_content_owner',
        'field_shared_cou',
        'field_shared_header_media',
      ],
    ],
  ];

  /**
   * Array of supported languages.
   *
   * @var languages
   */
  protected $languages = [
    'ar',
    'zh-hans',
    'zh-hant',
    'nl',
    'en',
    'fr',
    'fr-ca',
    'de',
    'it',
    'ja',
    'ko',
    'pl',
    'pt-br',
    'es',
    'th',
    'tr',
    'vi',
  ];

  /**
   * Default language.
   *
   * @var default_language
   */
  protected $default_language = 'en';

  /**
   * Allowed Newsletter Types.
   *
   * @var newsletter_types
   */
  protected $newsletter_types;

  /**
   * Allowed form customizations.
   *
   * @var customizations
   */
  protected $customizations = [
    'generic_form' => [
      'generic_form_button_text',
      'generic_form_custom_title',
      'generic_form_description',
      'generic_form_thank_you_page',
    ],
    'event_form' => [
      'event_form_button_text',
      'event_form_custom_title',
      'event_form_description',
      'event_form_thank_you_page',
    ],
    'email_form' => [
      'email_form_button_text',
      'email_form_custom_title',
      'email_form_description',
      'email_form_newsletter_type',
      'email_form_thank_you_page',
    ],
    'gated_content_form' => [
      'gated_content_form_button_text',
      'gated_content_form_custom_text',
      'gated_content_form_description',
      'gated_content_form_gated_asset_title',
      'gated_content_form_gated_asset_language',
    ],
  ];

  /**
   * Content Domain by content type when content_domain is required.
   *
   * @var content_domain
   */
  protected $content_domain = [
    'event' => 'Events',
    'help' => 'Help and Support',
    'knowledge' => 'Insights',
    'news' => 'News',
    'offering' => 'Offerings',
    'resource' => 'Resources',
    'tool' => 'Tools, Apps and Databases',
  ];

  /**
   * Assoc array of content type to content domain term id.
   *
   * @var content_domain_term_id_map
   */
  protected $content_domain_term_id_map = [];

  /**
   * Return info array for all supported types or the selected type.
   *
   * @param string $type
   *   Selected content type. If null, the entire $type sarray will be returned.
   *
   * @return array
   *   The associative array for the selected type or all types.
   */
  public function getTypeInfo($type = NULL) {
    if (!empty($type)) {
      return $this->types[$type];
    }
    else {
      return $this->types;
    }
  }

  /**
   * Return an array of all supported content types.
   *
   * @return array
   *   The list of supported content types.
   */
  protected function getAllowedTypes() {
    return array_keys($this->types);
  }

  /**
   * Get the content domain taxonomy term id for the supplied content type.
   *
   * @param string $type
   *   Selected content type.
   *
   * @return string
   *   Matching taxonomy term id.
   */
  public function getContentDomain(string $type) {
    if (isset($this->content_domain_term_id_map[$type])) {
      return $this->content_domain_term_id_map[$type];
    }
  }

  /**
   * Validates the array of data from the uploaded files. Sets default values.
   *
   * @param array $data
   *   Associated array of data from the uploaded file.
   *
   * @return array
   *   The associative array errors and warnings.
   */
  public function validateNodeData(array &$data) {
    $results = [
      'errors' => [],
      'warnings' => [],
    ];

    for ($i = 0; $i < count($data); $i++) {
      $this->validateNodeDataRow($data, $i, $results);
    }

    // dpm($data);
    return $results;
  }

  /**
   * Validated a single data row.
   *
   * @param array $data
   *   The data array.
   * @param int $i
   *   The row index from the data array.
   * @param array $results
   *   The results array for appending errors and warnings.
   */
  public function validateNodeDataRow(array &$data, $i, array &$results) {
    // The row number will be $i + 1.
    $r = $i + 1;

    // Keep track of marketo forms to validate customizations.
    $forms = [];

    // Check qa_id. If no qa_id is supplied default to the row number.
    $qa_id = $data[$i]['qa_id'];
    if (empty($qa_id)) {
      $data[$i]['qa_id'] = $r;
    }

    // Check Content Type.
    $type = $data[$i]['type'];

    if (empty($type)) {
      $results['errors'][] = $this->t("Row @row - content type is required", ['@row' => $r]);
    }
    elseif (!in_array($type, $this->getAllowedTypes())) {
      $results['errors'][] = $this->t("Row @row - '@type' is not a valid content type",
      ['@row' => $r, '@type' => $type]);
    }

    // Check header_cta.
    $header_cta = $data[$i]['header_cta'];

    if (!empty($header_cta)) {
      if (!isset($this->types[$type]['header_cta'])) {
        $data[$i]['header_cta'] = NULL;
        $results['warnings'][] = $this->t("Row @row - 'header_cta is not supported for content type '@type', this value will be ignored",
        ['@row' => $r, '@type' => $type]);
      }
      elseif (!in_array($header_cta, $this->types[$type]['header_cta'])) {
        $results['errors'][] = $this->t("Row @row - '@header_cta' is not a valid header_cta for content type '@type'",
        ['@row' => $r, '@type' => $type, '@header_cta' => $header_cta]);
      }
      else {
        $forms[] = $data[$i]['header_cta'];
      }
    }

    // Check rr_cta.
    $rr_cta = $data[$i]['rr_cta'];

    if (!empty($rr_cta)) {
      if (!isset($this->types[$type]['rr_cta'])) {
        $data[$i]['rr_cta'] = NULL;
        $results['warnings'][] = $this->t("Row @row - 'rr_cta is not supported for content type '@type', this value will be ignored",
        ['@row' => $r, '@type' => $type]);
      }
      elseif (!in_array($rr_cta, $this->types[$type]['rr_cta'])) {
        $results['errors'][] = $this->t("Row @row - '@rr_cta' is not a valid rr_cta for content type '@type'",
        ['@row' => $r, '@type' => $type, '@rr_cta' => $rr_cta]);
      }
      else {
        $forms[] = $data[$i]['rr_cta'];
      }
    }

    // Only one inline form allowed.
    $inline_forms = [];

    // Check campaign_form.
    $campaign_form = $data[$i]['campaign_form'];

    if (!empty($campaign_form)) {
      if (!isset($this->types[$type]['campaign_form'])) {
        $data[$i]['campaign_form'] = NULL;
        $results['warnings'][] = $this->t("Row @row - 'campaign_form' is not supported for content type '@type', this value will be ignored",
        ['@row' => $r, '@type' => $type]);
      }
      elseif (!in_array($campaign_form, $this->types[$type]['campaign_form'])) {
        $valid_form_types = implode(',', $this->types[$type]['campaign_form']);
        $results['errors'][] = $this->t("Row @row - '@campaign_form' is not a valid campaign_form for content type '@type'. Valid types are @valid_form_types",
        [
          '@row' => $r,
          '@type' => $type,
          '@campaign_form' => $campaign_form,
          '@valid_form_types' => $valid_form_types,
        ]);
      }
      else {
        $forms[] = $data[$i]['campaign_form'];
        if (in_array($data[$i]['campaign_form'],
          ['generic_form', 'event_form'])) {
          $inline_forms[] = $data[$i]['campaign_form'];
        }
      }
    }

    // Check marketo_form.
    $marketo_form = $data[$i]['marketo_form'];

    if (!empty($marketo_form)) {
      if (!isset($this->types[$type]['marketo_form'])) {
        $data[$i]['marketo_form'] = NULL;
        $results['warnings'][] = $this->t("Row @row - 'marketo_form' is not supported for content type '@type', this value will be ignored",
        ['@row' => $r, '@type' => $type]);
      }
      elseif (!in_array($marketo_form, $this->types[$type]['marketo_form'])) {
        $valid_form_types = implode(',', $this->types[$type]['marketo_form']);
        $results['errors'][] = $this->t("Row @row - '@marketo_form' is not a valid marketo_form for content type '@type' Valid types are @valid_form_types",
        [
          '@row' => $r,
          '@type' => $type,
          '@marketo_form' => $marketo_form,
          '@valid_form_types' => $valid_form_types,
        ]);
      }
      else {
        $forms[] = $data[$i]['marketo_form'];
        if (in_array($data[$i]['marketo_form'], ['email_form', 'event_form'])) {
          $inline_forms[] = $data[$i]['marketo_form'];
        }
      }

    }

    // Check text_and_form.
    $text_and_form = $data[$i]['text_and_form'];
    if (!empty($text_and_form)) {
      if (!isset($this->types[$type]['text_and_form'])) {
        $data[$i]['text_and_form'] = NULL;
        $results['warnings'][] = $this->t("Row @row - 'text_and_form' is not supported for content type '@type', this value will be ignored",
        ['@row' => $r, '@type' => $type]);
      }
      elseif (!in_array($text_and_form, $this->types[$type]['text_and_form'])) {
        $valid_form_types = implode(',', $this->types[$type]['text_and_form']);
        $results['errors'][] = $this->t("Row @row - '@text_and_form' is not a valid text_and_form for content type '@type'. Valid types are @valid_form_types",
        [
          '@row' => $r,
          '@type' => $type,
          '@text_and_form' => $text_and_form,
          '@valid_form_types' => $valid_form_types,
        ]);
      }
      else {
        $forms[] = $data[$i]['text_and_form'];
        if (in_array($data[$i]['text_and_form'], ['generic_form'])) {
          $inline_forms[] = $data[$i]['text_and_form'];
        }
      }
    }

    // Check gate_this_node.
    // Treat any non-empty value as TRUE.
    if (!empty($data[$i]['gate_this_node'])) {
      $data[$i]['gate_this_node'] = TRUE;
      $forms[] = 'gated_content_form';
      $inline_forms[] = 'gated_content_form';
    }

    // Only one inline form is allowed. The UI does not enforce this but
    // multiple forms will not display properly.
    if (count($inline_forms) > 1) {
      $results['errors'][] = $this->t("Row @row - 'Only one inline Marketo form is allowed. @inline_forms were entered.",
      ['@row' => $r, '@inline_forms' => implode(", ", $inline_forms)]);
    }

    // Check sub_cou and last_interest.
    $sub_cou = $data[$i]['sub_cou'];
    $sub_cou_valid = TRUE;

    $last_interest = $data[$i]['last_interest'];
    $last_interest_valid = TRUE;

    if (!empty($sub_cou)) {
      if (!\Drupal::service('ul_marketo.data_service')->isValidSubCou($sub_cou)) {
        $sub_cou_valid = FALSE;
        $results['errors'][] = $this->t("Row @row - '@sub_cou' is not a valid sub_cou",
        ['@row' => $r, '@sub_cou' => $sub_cou]);
      }
    }

    if (!empty($last_interest)) {
      if (!\Drupal::service('ul_marketo.data_service')->isValidLastInterest($last_interest, $sub_cou)) {
        $last_interest_valid = FALSE;
        $results['errors'][] = $this->t("Row @row - '@last_interest' is not a valid last_interest",
        ['@row' => $r, '@last_interest' => $last_interest]);
      }
    }

    if ($sub_cou_valid && $last_interest_valid) {

      // If sub_cou & last_interest are both empty, get a pair using the index.
      if (empty($sub_cou) && empty($last_interest)) {
        $pair = \Drupal::service('ul_marketo.data_service')->getLastInterest($i);
        $data[$i]['sub_cou'] = $pair['sub_cou'];
        $data[$i]['last_interest'] = $pair['last_interest'];
      }
      // Else if sub_cou is empty derive it from the last interest.
      elseif (empty($sub_cou)) {
        $data[$i]['sub_cou'] = \Drupal::service('ul_marketo.data_service')->getSubCouForLastInterest($last_interest);
      }
      // If sub_cou but no last interest, get a random one.
      elseif (empty($last_interest)) {
        $data[$i]['last_interest'] = \Drupal::service('ul_marketo.data_service')->getRandomLastInterestForSubCou($sub_cou);
      }
    }

    // Check language.
    $langcode = strtolower($data[$i]['langcode']);
    if (empty($langcode)) {
      $data[$i]['langcode'] = $this->default_language;
    }
    elseif (!in_array($langcode, $this->languages)) {
      $results['errors'][] = $this->t("Row @row - '@langcode' is not a valid language code",
      ['@row' => $r, '@langcode' => $langcode]);
    }

    // Check Marketo Form Customizations.
    // Get a unique list of marketo related forms that will be created to
    // validate the customizations.
    $forms = array_unique($forms);

    // If the form customizations from the uploaded data are not applicable
    // for the selected marketo form types, return a warning and ignore them.
    foreach ($this->customizations as $form_type => $custom_fields) {
      foreach ($custom_fields as $f) {
        if (!empty($data[$i][$f]) && !in_array($form_type, $forms)) {
          $data[$i][$f] = NULL;
          $results['warnings'][] = $this->t("Row @row - There is no @form_type for this node, @field will be ignored.",
          ['@row' => $r, '@form_type' => $form_type, '@field' => $f]);
        }
      }
    }

    // If email_form_newsletter_type is set, validate the newsletter type.
    if (!isset($data[$i]['email_form_newsletter_type'])) {
      $data[$i]['email_form_newsletter_type'] = '';
    }

    $newsletter_type = $data[$i]['email_form_newsletter_type'];
    if (!empty($newsletter_type) && !in_array($newsletter_type, $this->newsletter_types)) {
      $results['errors'][] = $this->t("Row @row - '@newsletter_type' is not a valid newsletter type",
      ['@row' => $r, '@newsletter_type' => $newsletter_type]);
    }

    // Set gated_content_form_gated_asset_language to empty string if not set.
    if (!isset($data[$i]['gated_content_form_gated_asset_language'])) {
      $data[$i]['gated_content_form_gated_asset_language'] = '';
    }

    // If gated_content_form_gated_asset_language is set, validate langcode.
    $gated_asset_language = $data[$i]['gated_content_form_gated_asset_language'];
    if (!empty($gated_asset_language)) {
      if (!in_array($gated_asset_language, $this->languages)) {
        $results['errors'][] = $this->t("Row @row - '@gated_asset_language' is not a valid Gated Asset Language",
        ['@row' => $r, '@gated_asset_language' => $gated_asset_language]);
      }
    }
    // Set default gated asset language for gated forms.
    elseif ($data[$i]['gate_this_node']) {
      $data[$i]['gated_content_form_gated_asset_language'] = $this->default_language;
    }

    // Check Content Paragraphs
    // Convert comm separated values to an array.
    $data[$i]['before_form_content'] = isset($data[$i]['before_form_content']) ? array_filter(explode(",", str_replace(" ", "", trim($data[$i]['before_form_content'])))) : [];
    $data[$i]['after_form_content'] = isset($data[$i]['after_form_content']) ? array_filter(explode(",", str_replace(" ", "", trim($data[$i]['after_form_content'])))) : [];

    // Make sure supplied content paragraphs are valid for the content type.
    if (isset($this->types[$type]['content_paragraph_types'])) {

      // Check before_form_content.
      $diff = array_diff($data[$i]['before_form_content'], $this->types[$type]['content_paragraph_types']);
      if (!empty($diff)) {
        $results['errors'][] = $this->t("Row @row - The paragraph type(s) '@diffs' are not supported content paragraphs for '@type'",
        ['@row' => $r, '@diffs' => implode(',', $diff), '@type' => $type]);
      }

      // Check after_form_content.
      if (!isset($data[$i]['after_form_content'])) {
        $data[$i]['after_form_content'] = '';
      }

      $diff = array_diff($data[$i]['after_form_content'], $this->types[$type]['content_paragraph_types']);
      if (!empty($diff)) {
        $results['errors'][] = $this->t("Row @row - The paragraph type(s) '@diffs' are not supported content paragraphs for '@type'",
        ['@row' => $r, '@diffs' => implode(',', $diff), '@type' => $type]);
      }

    }

  }

  /**
   * Checks if supplied $type supports Marketo forms.
   *
   * @param string $type
   *   Selected content type.
   *
   * @return bool
   *   True if the type supports Marketo forms.
   */
  public function isMarketoContentType($type) {
    if (isset($this->types[$type])) {
      return (
        isset($this->types[$type]['campaign_form'])
        ||
        isset($this->types[$type]['header_cta'])
        ||
        isset($this->types[$type]['rr_cta'])
        ||
        isset($this->types[$type]['marketo_form'])
      );
    }
    else {
      return FALSE;
    }
  }

}

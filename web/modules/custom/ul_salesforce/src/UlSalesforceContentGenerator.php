<?php

namespace Drupal\ul_salesforce;

use Drupal\node\Entity\Node;
use Drupal\block\Entity\Block;
use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\Yaml\Yaml;
use Drupal\pathauto\PathautoState;
use Drupal\locale\SourceString;
use Drupal\ul_marketo\Entity\MarketoForm;

/**
 * Provides tools for Sub COU and Last Interest values.
 */
class UlSalesforceContentGenerator {

  /**
   * ConfigFactory service.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Sitename.
   *
   * @var string
   */
  protected $sitename;

  /**
   * Database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Content from yml file.
   *
   * @var array
   */
  protected $content;

  /**
   * Translations from yml file.
   *
   * @var array
   */
  protected $translation_text;

  /**
   * Supported languages.
   *
   * @var array
   */
  protected $languages;

  /**
   * Report your concerns block id.
   *
   * @var string
   */
  protected $report_your_concerns_block_id;

  /**
   * Constructs a new UlMarketoService object.
   */
  public function __construct() {
    // DB connnection.
    $this->connection = \Drupal::database();
    // Site name.
    $this->sitename = \Drupal::config('system.site')->get('name');
    // Config path.
    $config_path = \Drupal::service('file_system')->realpath(\Drupal::service('module_handler')->getModule('ul_salesforce')->getPath()) . '/config/install/';
    // YML with content.
    $content_yml = file_get_contents($config_path . 'ul_salesforce.content.yml');
    // The content.
    $this->content = Yaml::parse($content_yml);
    // Translation text YML.
    $translation_text_yml = file_get_contents($config_path . 'ul_salesforce.translation_text.yml');
    // The translation text.
    $this->translation_text = Yaml::parse($translation_text_yml);
    // List of languages.
    $langs = \Drupal::languageManager()->getLanguages();
    // We're not translating `fr` or `vi` at this time.
    unset($langs['fr'], $langs['vi']);
    // The languages by key.
    $this->languages = array_filter(array_keys($langs));
  }

  /**
   * Clean up, generate and update content required by web-to-case.
   */
  public function generateWebToCaseContent() {

    \Drupal::logger('ul_salesforce')->notice('Cleaning up existing content.');
    $this->cleanup();

    \Drupal::logger('ul_salesforce')->notice('Getting and storing the text translations.');
    $this->createWebToCaseTranslations();

    \Drupal::logger('ul_salesforce')->notice('Checking the `Report Your Concerns` block.');
    $this->createReportYourConcernsBlock();

    \Drupal::logger('ul_salesforce')->notice('Checking the `Contact Us` landing page.');
    $this->createContactUsLandingPage();

    \Drupal::logger('ul_salesforce')->notice('Creating the `Customer Service - Thank You` page blocks.');
    $this->createThankYouBlock();

    \Drupal::logger('ul_salesforce')->notice('Updating config for blocks and forms.');
    $this->updateConfig();

    \Drupal::logger('ul_salesforce')->notice('Creating translations of Marketo generic form.');
    $this->createGenericForm();
  }

  /**
   * Delete existing entities for landing page, and blocks.
   */
  public function cleanup() {
    // Query string to clean up landing page.
    $q = "SELECT * FROM node_field_data WHERE title = 'Let\'s start a conversation'";
    // Query result.
    $result = $this->connection->query($q)->fetchAll();
    // If result.
    if (!empty($result)) {
      // Echo message, and delete.
      \Drupal::logger('ul_salesforce')->notice('Deleting existing `Contact Us` landing page.');
      $node = \Drupal::entityTypeManager()->getStorage("node")->load($result[0]->nid);
      $node->delete();
    }

    // Query to clean up the "report your concerns" block.
    $q = "SELECT * FROM block_content_field_data WHERE info = 'Report your concerns'";
    // Query result.
    $result = $this->connection->query($q)->fetchAll();
    // If result.
    if (!empty($result)) {
      // Echo message, and delete.
      \Drupal::logger('ul_salesforce')->notice('Deleting existing `Report Your Concerns` block');
      $block = \Drupal::entityTypeManager()->getStorage("block_content")->load($result[0]->id);
      $block->delete();
    }

    // Query to clean up "Customer Service - Thank You" blocks.
    $q = "SELECT * FROM block_content_field_data WHERE info LIKE 'Customer Service - Thank You%'";
    // Query result.
    $result = $this->connection->query($q)->fetchAll();
    // If result.
    if (!empty($result)) {
      // Echo message, and delete.
      \Drupal::logger('ul_salesforce')->notice('Deleting existing `Customer Service - Thank You` block.');
      $block = \Drupal::entityTypeManager()->getStorage("block_content")->load($result[0]->id);
      $block->delete();
    }
  }

  /**
   * Create translation items defined by ul_salesforce.translation_text.yml.
   */
  public function createWebToCaseTranslations() {
    // Get locale storage service.
    $storage = \Drupal::service('locale.storage');

    // Loop through the translations.
    foreach ($this->translation_text as $section => $keys) {
      foreach ($keys as $translations) {
        $string = NULL;
        if (is_array($translations)) {
          foreach ($translations as $lang => $translation) {
            // Create translation label for english.
            if ($lang == 'en') {
              $string = $storage->findString(['source' => $translation]);
              if (is_null($string)) {
                $string = new SourceString();
                $string->setString($translation);
                $string->setStorage($storage);
                $string->save();
              }
            }
            // Create translation for other languages.
            else {
              $storage->createTranslation([
                'lid' => $string->lid,
                'language' => $lang,
                'translation' => $translation,
              ])->save();
            }

          }
        }
      }
    }
  }

  /**
   * Generate the Report Your Concerns block.
   */
  public function createReportYourConcernsBlock() {
    // Check if the 'Report Your Concerns' block exists.
    $q = "SELECT * FROM block_content_field_data WHERE type = 'cta_banner' AND info = 'Report your concerns'";
    // Query result.
    $result = $this->connection->query($q)->fetchAll();
    // If result, block exists.
    if (!empty($result)) {
      \Drupal::logger('ul_salesforce')->notice('The `Report Your Concerns` block already exists.');
      $this->report_your_concerns_block_id = $result[0]->id;
    }
    // Else, block doesn't exist.
    else {
      \Drupal::logger('ul_salesforce')->notice('Creating the `Report Your Concerns` block.');
      $blockEntityManager = \Drupal::service('entity_type.manager')->getStorage('block_content');

      $block_values = [
        'uuid' => \Drupal::service('uuid')->generate(),
        'info' => "Report your concerns",
        'type' => 'cta_banner',
        'status' => TRUE,
        'created' => \Drupal::time()->getCurrentTime(),
        'langcode' => 'en',
      ];

      $block = $blockEntityManager->create($block_values);

      $block->set('field_cta_color', [
        'color' => '#FFFFFF',
        'opacity' => 1,
      ]);

      // Create "Report Your Concerns" CTA paragraph (within the block).
      \Drupal::logger('ul_salesforce')->notice('Adding the `Report Your Concerns` paragraph content.');
      $cta_paragraph = Paragraph::create([
        'type' => 'call_to_action',
      ]);

      $cta_paragraph->set('field_cta_title', $this->content['report_your_concerns']['report_your_concerns_title']);

      $cta_paragraph->set('field_cta_description', [
        'value' => $this->content['report_your_concerns']['report_your_concerns_content'],
        'format' => 'full_html',
      ]);

      $cta_paragraph->set('field_cta_link', [
        'uri' => 'https://market-surveillance.ul.com',
        'title' => $this->content['report_your_concerns']['make_a_report'],
      ]);

      // Save Paragraph.
      try {
        $cta_paragraph->save();
        \Drupal::logger('ul_salesforce')->notice('Saving the `Report Your Concerns` paragraph content.');
      }
      catch (\Exception $error) {
        // Log error.
      }

      // Add paragraph translations.
      foreach ($this->languages as $lang) {
        if (!$cta_paragraph->hasTranslation($lang)) {

          $cta_trans = $cta_paragraph->addTranslation($lang);

          $cta_trans->set('field_cta_title', $this->translation_text['contact_us_landing']['report_your_concerns'][$lang]);

          $cta_trans->set('field_cta_description', [
            'value' => $this->translation_text['contact_us_landing']['if_you_wish_to_report'][$lang],
            'format' => 'full_html',
          ]);

          $cta_trans->set('field_cta_link', [
            'uri' => 'https://market-surveillance.ul.com',
            'title' => $this->translation_text['contact_us_landing']['make_a_report'][$lang],
          ]);

          $cta_paragraph->save();
        }
      }

      $block->field_cta_cta[] = [
        'target_id' => $cta_paragraph->id(),
        'target_revision_id' => $cta_paragraph->getRevisionId(),
      ];

      $block->set('field_cta_color', [
        'color' => '#FFFFFF',
        'opacity' => 1,
      ]);

      \Drupal::logger('ul_salesforce')->notice('Saving the `Report Your Concerns` block.');
      $block->save();

      $this->report_your_concerns_block_id = $block->id();

      // Add block translations.
      \Drupal::logger('ul_salesforce')->notice('Creating the `Report Your Concerns` block translations.');
      foreach ($this->languages as $lang) {
        if (!$block->hasTranslation($lang) && isset($this->translation_text['contact_us_landing']['report_your_concerns'][$lang])) {
          $block_trans = $block->addTranslation($lang);
          $block_trans->setInfo($this->translation_text['contact_us_landing']['report_your_concerns'][$lang]);
          $block_trans->set('field_cta_color', [
            'color' => '#FFFFFF',
            'opacity' => 1,
          ]);

          try {
            $block->save();
          }
          catch (\Exception $error) {
            // Log error.
          }
        }
      }
      \Drupal::logger('ul_salesforce')->notice('Saving the `Report Your Concerns` block translations.');
    }
  }

  /**
   * Creates the Contact Us Landing Page.
   */
  public function createContactUsLandingPage() {
    \Drupal::logger('ul salesforce')->notice('Creating the `Contact Us` landing page.');

    $title = $this->translation_text['contact_us_landing']['lets_start_a_conversation']['en'];

    $values = [
      'nid' => NULL,
      'title' => $title,
      'type' => 'landing_page',
      'uid' => 1,
      'status' => TRUE,
      'promote' => 0,
      'created' => \Drupal::time()->getCurrentTime(),
      'langcode' => 'en',
      'path' => [
        'alias' => '/contact-us',
        'pathauto' => PathautoState::SKIP,
      ],
    ];

    $node = Node::create($values);

    // Set Subtitle.
    $node->set('field_shared_subtitle', $this->translation_text['contact_us_landing']['whether_you_have_a_sales']['en']);

    // Set Marketing Support Ticket Number.
    $node->set('field_shared_mktg_support_ticket', '1234');

    // Set Summary (aka Description).
    $node->set('field_shared_ref_description', 'Contact Us Landing Page');

    $storage = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term');

    // Get/Set Content Owner.
    $content_owner_tid = $storage->loadByProperties([
      'name' => 'Marketing',
      'vid' => 'content_owner',
    ]);
    $node->set('field_shared_content_owner', $content_owner_tid);

    // Get/Set COU.
    $content_owner_tid = $storage->loadByProperties([
      'name' => 'CO_Marketing',
      'vid' => 'customer_operating_unit',
    ]);
    $node->set('field_shared_cou', $cou_tid);

    // Set Use Right Rail.
    $node->set('field_shared_rr_checkbox', 1);

    // Save node.
    $node->save();

    // Add Report Your Concerns paragraph.
    \Drupal::logger('ul salesforce')->notice('Adding the `Report Your Concerns` interruptor paragraph.');

    $paragraph = Paragraph::create([
      'type' => 'interrupter_cta',
    ]);

    $paragraph->set('field_interrupter_cta_cta', ['target_id' => $this->report_your_concerns_block_id]);

    $paragraph->save();

    $node->field_landing_page_content[] = [
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ];

    // Add Global Contacts Paragraph.
    \Drupal::logger('ul_salesforce')->notice('Creating the `Global Contacts` paragraph.');
    $lang = 'en';
    $paragraph = Paragraph::create([
      'type' => 'basic_content',
    ]);

    $text = $this->content['contact_us_landing']['global_contacts'];
    $text = str_replace('@lang', '', $text);
    $text = str_replace('@global_contacts', $this->translation_text['contact_us_landing']['global_contacts'][$lang], $text);
    $text = str_replace('@view_detailed_information_for_our', $this->translation_text['contact_us_landing']['view_detailed_information_for_our'][$lang], $text);
    $text = str_replace('@find_contact_information', $this->translation_text['contact_us_landing']['find_contact_information'][$lang], $text);
    $text = str_replace('@help_and_support', $this->translation_text['contact_us_landing']['help_and_support'][$lang], $text);
    $text = str_replace('@get_additional_assistance_and_information', $this->translation_text['contact_us_landing']['get_additional_assistance_and_information'][$lang], $text);
    $text = str_replace('@visit_the_help_and_support', $this->translation_text['contact_us_landing']['visit_the_help_and_support'][$lang], $text);

    $paragraph->set('field_basic_content_content', [
      'value' => $text,
      'format' => 'full_html',
    ]);

    $paragraph->save();

    // Add Global Contacts translations.
    \Drupal::logger('ul_salesforce')->notice('Creating the `Global Contacts` paragraph translations.');

    foreach ($this->languages as $lang) {
      if (!$paragraph->hasTranslation($lang)) {
        $paragraph_trans = $paragraph->addTranslation($lang);
        $text = $this->content['contact_us_landing']['global_contacts'];
        $text = str_replace('@lang', '/' . $lang, $text);
        $text = str_replace('@global_contacts', $this->translation_text['contact_us_landing']['global_contacts'][$lang], $text);
        $text = str_replace('@view_detailed_information_for_our', $this->translation_text['contact_us_landing']['view_detailed_information_for_our'][$lang], $text);
        $text = str_replace('@find_contact_information', $this->translation_text['contact_us_landing']['customer_service'][$lang], $text);
        $text = str_replace('@help_and_support', $this->translation_text['contact_us_landing']['help_and_support'][$lang], $text);
        $text = str_replace('@get_additional_assistance_and_information', $this->translation_text['contact_us_landing']['get_additional_assistance_and_information'][$lang], $text);
        $text = str_replace('@visit_the_help_and_support', $this->translation_text['contact_us_landing']['visit_the_help_and_support'][$lang], $text);

        $paragraph_trans->set('field_basic_content_content', [
          'value' => $text,
          'format' => 'full_html',
        ]);

        $paragraph->save();

      }
    }

    $node->field_landing_page_content[] = [
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ];

    // Add Marketo Inline Contact Form paragraph.
    \Drupal::logger('ul salesforce')->notice('Adding the `Marketo Inline Contact Form` paragraph.');

    $paragraph = Paragraph::create([
      'type' => 'mkto_inline_contact_form',
    ]);

    $paragraph->set('field_marketo_form', 'generic_form');

    $paragraph->save();

    $node->field_landing_page_content[] = [
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ];

    // Create Translations for the Contact Us Landing page.
    \Drupal::logger('ul_salesforce')->notice('Creating the `Contact Us` node translations.');

    foreach ($this->languages as $lang) {
      if (!$node->hasTranslation($lang)) {
        $node_trans = $node->addTranslation($lang);
        $title = $this->translation_text['contact_us_landing']['lets_start_a_conversation'][$lang];
        $node_trans->setTitle($title);
        $node_trans->setPublished(TRUE);
        $node_trans->set('uid', 1);
        $node_trans->set('field_shared_subtitle', $this->translation_text['contact_us_landing']['whether_you_have_a_sales'][$lang]);
        $node_trans->set('field_shared_mktg_support_ticket', '1234');
        $node_trans->set('field_shared_ref_description', 'Contact Us Landing Page');
        $node_trans->set('field_shared_content_owner', $content_owner_tid);
        $node_trans->set('field_shared_cou', $cou_tid);

        // Add the path alias for translation.
        $path_alias = \Drupal::entityTypeManager()->getStorage('path_alias')->create([
          'path' => '/node/' . $node->id(),
          'alias' => '/contact-us',
          'langcode' => $lang,
        ]);
        $path_alias->save();

        try {
          $node->save();
        }
        catch (\Exception $error) {
          echo 'ERROR saving the paragraph translation, ' . $lang . ' ';
        }
      }
    }
    $node->setPublished(TRUE);
    $node->set('moderation_state', 'published');
    $node->save();

    // Add a Contact form to the node:
    \Drupal::logger('ul_salesforce')->notice('Creating the `Marketo Form Customization` for the new `Contact Us` page.');
    $this->addMarketoCustomizations($node);

    $node->save();
  }

  /**
   * Creates thank you pages in supported languages.
   */
  public function createThankYouBlock() {

    \Drupal::logger('ul_salesforce')->notice('Creating the `Thank You` page block.');

    $blockEntityManager = \Drupal::service('entity_type.manager')->getStorage('block_content');

    $block_values = [
      'uuid' => \Drupal::service('uuid')->generate(),
      'info' => "Customer Service - Thank You (en)",
      'type' => 'basic',
      'status' => TRUE,
      'created' => \Drupal::time()->getCurrentTime(),
      'langcode' => 'en',
    ];

    $block = $blockEntityManager->create($block_values);
    $lang = 'en';
    $text = $this->content['customer_service_thank_you']['content'];
    $text = str_replace('@thank_you_weve_received_your', $this->translation_text['thank_you_web_to_case']['thank_you_weve_received_your'][$lang], $text);
    $text = str_replace('@one_of_our_team_members', $this->translation_text['thank_you_web_to_case']['one_of_our_team_members'][$lang], $text);

    $block->set('body', ['value' => $text, 'format' => 'full_html']);

    $block->save();

    // Create translations.
    \Drupal::logger('ul_salesforce')->notice('Creating the `Thank You` page block translations.');

    foreach ($this->languages as $lang) {
      if (!$block->hasTranslation($lang)) {
        $block_values['info'] = "Customer Service - Thank You (" . $lang . ")";
        $block_values['langcode'] = $lang;
        $block_trans = $block->addTranslation($lang, $block_values);
        $text = $this->content['customer_service_thank_you']['content'];
        $text = str_replace('@thank_you_weve_received_your', $this->translation_text['thank_you_web_to_case']['thank_you_weve_received_your'][$lang], $text);
        $text = str_replace('@one_of_our_team_members', $this->translation_text['thank_you_web_to_case']['one_of_our_team_members'][$lang], $text);
        $block_trans->set('body', ['value' => $text, 'format' => 'full_html']);
        $block->save();
      }
    }
  }

  /**
   * Updated related configuration.
   */
  public function updateConfig() {
    // We make config changes in code b/c the config has a dependency
    // on ul_com_theme which will cause pipelines to fail.
    $config_factory = \Drupal::configFactory();

    switch ($this->sitename) {
      case 'UL LATAM':
        // Add thank you page paths for sales-inquiries and contact-ul.
        $block = Block::load('contactusthankyouen_2');
        if ($block) {
          $request_path = [
            'id' => 'request_path',
            'pages' => "/contact-us/thank-you\r\n/contact-us/*/thank-you\r\n/contact-sales/thank-you\r\n/contact-sales/*/thank-you\r\n/sales-inquiries/thank-you\r\n/contact-ul/thank-you\r\n/contact-ul/*/thank-you\r\n",
            'negate' => FALSE,
          ];
          $block->setVisibilityConfig('request_path', $request_path);
          $block->save();
        }

        // Don't show the breadcrumbs block on the contact-us page.
        \Drupal::logger('ul_salesforce')->notice('Removing the `breadcrumbs` block on the `Contact Us` landing page.');
        $block = Block::load('breadcrumbs');
        if ($block) {
          $request_path = [
            'id' => 'request_path',
            'pages' => "/\r\n/users\r\n/users/*\r\n/admin\r\n/admin/*\r\n/contact-us",
            'negate' => TRUE,
          ];
          $block->setVisibilityConfig('request_path', $request_path);
          $block->save();
        }

        // Don't show the "hero" block on the "Contact Us" page.
        \Drupal::logger('ul_salesforce')->notice('Removing the `hero` block from the `Contact Us` landing page.');
        $block = Block::load('ul_latam_theme_views_block__hero_block');
        if ($block) {
          $request_path = [
            'id' => 'request_path',
            'pages' => "/contact-us",
            'negate' => TRUE,
          ];
          $block->setVisibilityConfig('request_path', $request_path);
          $block->save();
        }
        break;

      default:
        // Don't show the "breadcrumbs" block on the "Contact Us" page.
        \Drupal::logger('ul_salesforce')->notice('Removing the `breadcrumbs` block from the `Contact Us` landing page.');
        $block = Block::load('breadcrumbs');
        if ($block) {
          $request_path = [
            'id' => 'request_path',
            'pages' => "/\r\n/users\r\n/users/*\r\n/admin\r\n/admin/*\r\n/contact-us",
            'negate' => TRUE,
          ];
          $block->setVisibilityConfig('request_path', $request_path);
          $block->save();
        }

        // Add thank you page paths for sales-inquiries and contact-ul.
        $block = Block::load('ul_com_theme_contactusthankyou');
        if ($block) {
          $request_path = [
            'id' => 'request_path',
            'pages' => "/contact-us/thank-you\r\n/contact-us/*/thank-you\r\n/contact-sales/thank-you\r\n/contact-sales/*/thank-you\r\n/sales-inquiries/thank-you\r\n/contact-ul/thank-you\r\n/contact-ul/*/thank-you\r\n",
            'negate' => FALSE,
          ];
          $block->setVisibilityConfig('request_path', $request_path);
          $block->save();
        }

        // Don't show the "hero" block on the "Contact Us" page.
        \Drupal::logger('ul_salesforce')->notice('Removing the `hero` block from the `Contact Us` landing page.');
        $block = Block::load('ul_com_theme_views_block__hero_block');
        if ($block) {
          $request_path = [
            'id' => 'request_path',
            'pages' => "/contact-us",
            'negate' => TRUE,
          ];
          $block->setVisibilityConfig('request_path', $request_path);
          $block->save();
        }
        break;
    }
  }

  /**
   * Create generic marketo form entity if it doesn't exist.
   */
  public function createGenericForm() {
    $form = \Drupal::service('ul_marketo')->getOrCreateDefaultMarketoForm('generic_form');

    foreach ($this->languages as $lang) {
      if (!$form->hasTranslation($lang)) {
        $form_trans = $form->addTranslation($lang);
        $form_trans->set('field_shared_custom_title', $this->translation_text['marketo_form_intro']['get_connected_with_our_sales'][$lang]);
        $form_trans->set('field_shared_form_description', $this->translation_text['marketo_form_intro']['thanks_for_your_interest_in'][$lang]);
        $form_trans->set('field_shared_button_text', $this->translation_text['web_to_case']['submit'][$lang]);
      }

      $form->save();
    }
  }

  /**
   * Add a marketo_form entity.
   *
   * @param object $node
   *   The node to add form entity to.
   */
  protected function addMarketoCustomizations(object &$node) {
    // Array of form_ids to be added to the customization field
    // - field_shared_marketo_custom.
    $form_ids = [];

    $form = $this->getOrCreateMarketoForm($node);

    $form_ids[] = [
      'target_id' => $form->id(),
      'target_revision_id' => $form->getRevisionId(),
    ];

    if (!empty($form_ids)) {
      $node->set('field_shared_marketo_custom', $form_ids);
      $node->save();
    }
  }

  /**
   * Get or create a MarketoForm entity.
   *
   * @param object $node
   *   The node to add form entity to.
   *
   * @return \Drupal\ul_marketo\Entity\MarketoFormInterface
   *   The MarketForm enitity object.
   */
  protected function getOrCreateMarketoForm(object &$node) {

    $values = [
      'type' => 'generic_form',
    ];

    $form = MarketoForm::create($values);

    $form->setNewRevision(TRUE);

    $defaults = \Drupal::service('ul_marketo')->getFormDefaultSettings($form);

    // Set default values.
    $defaults['label'] = 'Contact form, old';
    $defaults['field_shared_button_text'] = 'Contact us';
    $defaults['field_shared_custom_title'] = 'Get in touch';
    $defaults['field_shared_form_description'] = 'Have questions, need specifics? Let\'s get this conversation started.';
    $defaults['field_shared_thank_you_page'] = NULL;

    $form->set('langcode', $node->get('langcode')->value);
    $form->set('name', $defaults['label']);

    $fields = [
      'field_shared_button_text',
      'field_shared_custom_title',
      'field_shared_form_description',
      'field_shared_thank_you_page',
    ];

    foreach ($fields as $f) {
      $form->set($f, $defaults[$f]);
    }

    $form->save();

    return $form;
  }

  /**
   * Get or create a MarketoForm entity.
   */
  public function updateWebToCaseTranslations() {
    \Drupal::logger('ul_salesforce')->notice('Getting and storing the text translations.');
    $this->createWebToCaseTranslations();
  }

}

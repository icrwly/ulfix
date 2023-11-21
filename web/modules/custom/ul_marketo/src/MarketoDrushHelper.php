<?php

namespace Drupal\ul_marketo;

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\Yaml\Yaml;
use Drupal\pathauto\PathautoState;
use Drupal\locale\SourceString;
use Drupal\ul_marketo\Entity\MarketoForm;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides tools (accessible via Drush) for Marketo Forms.
 */
class MarketoDrushHelper {

  /**
   * ConfigFactory service.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $config_factory;

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
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(MessengerInterface $messenger, LoggerChannelFactoryInterface $logger_factory) {
    $this->config_factory = \Drupal::configFactory();
    $this->config = $this->config_factory->getEditable('ul_marketo.metadata.settings');
    // DB connnection.
    $this->connection = \Drupal::database();
    // Site name.
    $this->sitename = \Drupal::config('system.site')->get('name');
    // Config path.
    $config_path = \Drupal::service('file_system')->realpath(\Drupal::service('module_handler')->getModule('ul_marketo')->getPath()) . '/config/install/';
    // YML with content.
    $content_yml = file_get_contents($config_path . 'ul_marketo.landingpage_content.yml');
    // The content.
    $this->content = Yaml::parse($content_yml);
    // Translation text YML.
    $translation_text_yml = file_get_contents($config_path . 'ul_marketo.translation_text.yml');
    // The translation text.
    $this->translation_text = Yaml::parse($translation_text_yml);
    // List of languages.
    $langs = \Drupal::languageManager()->getLanguages();
    // The languages by key.
    $this->languages = array_filter(array_keys($langs));
    $this->messenger = $messenger;
    $this->loggerFactory = $logger_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('logger.factory')
    );
  }

  /**
   * Enable `Dev` form IDs.
   */
  public function useDevForms() {
    $this->loggerFactory->get('ul_marketo')->info('Enabling the Marketo `dev` form IDs.');
    $this->messenger->addStatus('Enabling the Marketo `dev` form IDs.');

    $this->config->set('env', 'stage')->save();
  }

  /**
   * Enable `Prod` form IDs.
   */
  public function useProdForms() {
    $this->loggerFactory->get('ul_marketo')->info('Enabling the Marketo `prod` form IDs.');
    $this->messenger->addStatus('Enabling the Marketo `prod` form IDs.');
    $this->config->set('env', 'prod')->save();
  }

  /**
   * Create a `Contact Us` Landing Page.
   */
  public function createContactUsPage() {
    $this->loggerFactory->get('ul_marketo')->info('Creating a `Contact Us` Landing Page.');
    $this->messenger->addStatus('Creating a `Contact Us` Landing Page.');
    $this->cleanup();
    $this->createWebToCaseTranslations();
    $this->createContactUsLandingPage();
  }

  /**
   * Delete existing entities for landing page, and blocks.
   */
  public function cleanup() {
    // Query string to clean up existing Landing Page.
    $q = "SELECT * FROM node_field_data WHERE title = 'Contact'";
    // Query result.
    $result = $this->connection->query($q)->fetchAll();
    // If result.
    if (!empty($result)) {
      // Echo message, and delete.
      $this->loggerFactory->get('ul_marketo')->info('Deleting existing `Contact Us` landing page.');
      $this->messenger->addStatus('Deleting existing `Contact Us` landing page.');

      $node = \Drupal::entityTypeManager()->getStorage("node")->load($result[0]->nid);
      $node->delete();
    }
  }

  /**
   * Create translation items defined by ul_marketo.translation_text.yml.
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
   * Creates the Contact Us Landing Page.
   */
  public function createContactUsLandingPage() {
    $this->loggerFactory->get('ul_marketo')->info('Creating the `Contact Us` landing page.');
    $this->messenger->addStatus('Creating the `Contact Us` landing page.');

    $title = $this->translation_text['contact_us_landing']['request_more_info']['en'];

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
    $node->set('field_shared_subtitle', $this->translation_text['contact_us_landing']['thanks_for_your_interest']['en']);

    // Set Marketing Support Ticket Number.
    $node->set('field_shared_mktg_support_ticket', '1234');

    // Set Summary (aka Description).
    $node->set('field_shared_ref_description', 'Contact Us Landing Page');

    // Get/Set Content Owner.
    $content_owner_tid = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties([
        'name' => 'andrew.micek@ul.com',
        'vid' => 'content_owner',
      ]);
    $node->set('field_shared_content_owner', $content_owner_tid);

    // Get/Set COU.
    $cou_tid = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties([
        'name' => 'EA_ProductLifeCycle',
        'vid' => 'customer_operating_unit',
      ]);
    $node->set('field_shared_cou', $cou_tid);

    // Save node.
    $node->save();

    // Add Marketo Inline Contact Form paragraph.
    $this->loggerFactory->get('ul_marketo')->info('Adding the `Marketo Inline Contact Form` paragraph.');
    $this->messenger->addStatus('Adding the `Marketo Inline Contact Form` paragraph.');

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
    $this->loggerFactory->get('ul_marketo')->info('Creating the `Contact Us` node translations.');
    $this->messenger->addStatus('Creating the `Contact Us` node translations.');

    foreach ($this->languages as $lang) {
      if (!$node->hasTranslation($lang)) {
        $node_trans = $node->addTranslation($lang);
        $title = $this->translation_text['contact_us_landing']['request_more_info'][$lang];
        $node_trans->setTitle($title);
        $node_trans->setPublished(TRUE);
        $node_trans->set('uid', 1);
        $node_trans->set('field_shared_subtitle', $this->translation_text['contact_us_landing']['thanks_for_your_interest'][$lang]);
        $node_trans->set('field_shared_mktg_support_ticket', '0001');
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
    $this->loggerFactory->get('ul_marketo')->info('Creating the `Marketo Form Customization` for the new `Contact Us` page.');
    $this->messenger->addStatus('Creating the `Marketo Form Customization` for the new `Contact Us` page.');

    $this->addMarketoCustomizations($node);

    $node->save();
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

}

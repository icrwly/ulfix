<?php

namespace Drupal\ul_testing\Plugin\DevelGenerate;

use Drupal\comment\CommentManagerInterface;
use Drupal\Component\Datetime\Time;
use Drupal\content_translation\ContentTranslationManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\devel_generate\DevelGenerateBase;
use Drupal\node\NodeInterface;
use Drupal\path_alias\PathAliasStorage;
use Drupal\user\UserStorageInterface;
use Drush\Utils\StringUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\ul_marketo\Entity\MarketoFormInterface;
use Drupal\ul_marketo\Entity\MarketoForm;

/**
 * Provides a ContentDevelGenerate plugin.
 *
 * @DevelGenerate(
 *   id = "ul_content",
 *   label = @Translation("UL Test Content"),
 *   description = @Translation("Generate a given number of UL test nodes. Optionally delete current UL test content."),
 *   url = "ul_content",
 *   permission = "administer devel_generate",
 *   settings = {
 *     "num" = 50,
 *     "kill" = FALSE,
 *     "max_comments" = 0,
 *     "title_length" = 4,
 *     "add_type_label" = FALSE,
 *     "test_group" = "ul_test_data",
 *     "batch_size" = 50
 *   }
 * )
 */
class ContentDevelGenerate extends DevelGenerateBase implements ContainerFactoryPluginInterface {

  /**
   * The node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * The node type storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeTypeStorage;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The extension path resolver.
   *
   * @var \Drupal\Core\Extension\ExtensionPathResolver
   */
  protected $extensionPathResolver;

  /**
   * The comment manager service.
   *
   * @var \Drupal\comment\CommentManagerInterface
   */
  protected $commentManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The content translation manager.
   *
   * @var \Drupal\content_translation\ContentTranslationManagerInterface
   */
  protected $contentTranslationManager;

  /**
   * The url generator service.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * The alias storage.
   *
   * @var \Drupal\path_alias\PathAliasStorage
   */
  protected $aliasStorage;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The Drush batch flag.
   *
   * @var bool
   */
  protected $drushBatch;

  /**
   * Provides system time.
   *
   * @var \Drupal\Core\Datetime\Time
   */
  protected $time;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * UL Testing settings, stores last file id uploaded.
   *
   * @var Object
   */
  protected $ul_testing_settings;

  /**
   * Array of test node data converted from uploaded file.
   *
   * @var array
   */
  protected $ul_test_data = [];

  /**
   * Number of body content paragraphs to create.
   *
   * @var int
   */
  protected $num_content = 2;

  /**
   * Max # campaign cards to create for campaign landing pages.
   *
   * @var int
   */
  protected $max_cards = 10;

  /**
   * The construct.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $node_storage
   *   The node storage.
   * @param \Drupal\Core\Entity\EntityStorageInterface $node_type_storage
   *   The node type storage.
   * @param \Drupal\user\UserStorageInterface $user_storage
   *   The user storage.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Extension\ExtensionPathResolver $extension_path_resolver
   *   The extension path resolver.
   * @param \Drupal\comment\CommentManagerInterface $comment_manager
   *   The comment manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\content_translation\ContentTranslationManagerInterface $content_translation_manager
   *   The content translation manager service.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator service.
   * @param \Drupal\path_alias\PathAliasStorage $alias_storage
   *   The alias storage.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Datetime\Time $time
   *   Provides system time.
   * @param \Drupal\Core\Database\Connection $database
   *   Database connection.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, EntityStorageInterface $node_storage, EntityStorageInterface $node_type_storage, UserStorageInterface $user_storage, ModuleHandlerInterface $module_handler, ExtensionPathResolver $extension_path_resolver, CommentManagerInterface $comment_manager = NULL, LanguageManagerInterface $language_manager, ContentTranslationManagerInterface $content_translation_manager = NULL, UrlGeneratorInterface $url_generator, PathAliasStorage $alias_storage, DateFormatterInterface $date_formatter, Time $time, Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->moduleHandler = $module_handler;
    $this->$extensionPathResolver = $extension_path_resolver;
    $this->nodeStorage = $node_storage;
    $this->nodeTypeStorage = $node_type_storage;
    $this->userStorage = $user_storage;
    $this->commentManager = $comment_manager;
    $this->languageManager = $language_manager;
    $this->contentTranslationManager = $content_translation_manager;
    $this->urlGenerator = $url_generator;
    $this->aliasStorage = $alias_storage;
    $this->dateFormatter = $date_formatter;
    $this->time = $time;
    $this->database = $database;
    $this->ul_testing_settings = \Drupal::config('ul_testing.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $entity_type_manager = $container->get('entity_type.manager');
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $entity_type_manager->getStorage('node'),
      $entity_type_manager->getStorage('node_type'),
      $entity_type_manager->getStorage('user'),
      $container->get('module_handler'),
      $container->has('comment.manager') ? $container->get('comment.manager') : NULL,
      $container->get('language_manager'),
      $container->has('content_translation.manager') ? $container->get('content_translation.manager') : NULL,
      $container->get('url_generator'),
      $entity_type_manager->getStorage('path_alias'),
      $container->get('date.formatter'),
      $container->get('datetime.time'),
      $container->get('database'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $validators = [
      'file_validate_extensions' => ['csv xls xlsx'],
    ];

    $form['file'] = [
      '#type' => 'managed_file',
      '#name' => 'marketo_csv',
      '#title' => 'Marketo CSV or Excel',
      '#required' => TRUE,
      '#upload_location' => 'public://import/',
      '#upload_validators' => [
        'file_validate_extensions' => $validators,
      ],
    ];

    $form['kill'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('<strong>Delete all UL test content</strong> before generating new content.'),
      '#default_value' => $this->getSetting('kill'),
    ];

    $form['ul_test_data'] = [
      '#type' => 'value',
      '#value' => [],
    ];

    // Include link to test node view.
    $form['view_link'] = [
      '#type' => 'link',
      '#title' => $this->t('View UL Test Nodes'),
      '#url' => Url::fromRoute('view.test_node.page_ul_test_data'),
    ];

    $form['#redirect'] = FALSE;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsFormValidate(array $form, FormStateInterface $form_state) {

    $trigger = $form_state->getTriggeringElement();
    if (in_array($trigger['#name'], ['file_remove_button', 'file_upload_button'])) {
      return;
    }

    /*
     * The settings form requires that a file is uploaded, csv or excel.
     * Since we need to convert the uploaded file into an array to verify
     * that is has the required columns, we'll process the upload in the
     * validate function.
     */
    $file = $form_state->getValue('file');

    if (!empty($file)) {
      $fid = $form_state->getValue('file')[0];
      $config = \Drupal::configFactory()->getEditable('ul_testing.settings');
      $config->set('marketo_test_data', ['data_file' => $fid])->save();

      // Parse the uploaded file and store in the ul_test_data class variable.
      $this->getMarketoData($fid);

      $results = \Drupal::service('ul_testing.validation_service')->validateNodeData($this->ul_test_data);

      // Data in class variables is not retained after validation,
      // so it needs to be stored in the form_state values.
      $form_state->setValue('ul_test_data', $this->ul_test_data);

      // Show Errors.
      if (!empty($results['errors'])) {
        $errors = "";
        foreach ($results['errors'] as $e) {
          $errors .= '<li>' . $e . '</li>';
        }
        $rendered_message = Markup::create('<ul>' . $errors . '</ul>');
        $error_message = new TranslatableMarkup('@message', ['@message' => $rendered_message]);
        $form_state->setError($form['file'], $error_message);
      }

      // Show warnings.
      if (!empty($results['warnings'])) {
        for ($w = 0; $w < count($results['warnings']); $w++) {
          \Drupal::messenger()->addWarning($results['warnings'][$w]);
        }
      }

    }
  }

  /**
   * {@inheritdoc}
   */
  protected function generateElements(array $values) {

    // Get the validated ul_test_data from the form state values.
    $this->ul_test_data = $values['ul_test_data'];

    $values['index'] = 0;
    if ($this->isBatch($values)) {
      $this->generateBatchContent($values);
    }
    else {
      $this->generateContent($values);
    }
  }

  /**
   * Generates a title based on the data parameters.
   *
   * @param int $index
   *   The row index from the data array.
   *
   * @return string
   *   The generated title string.
   */
  protected function getTitle($index) {
    $data = $this->ul_test_data[$index];
    $title = $data['title'];

    // If title is empty derive it from the data row.
    if (empty($title)) {

      // Array of attributes describing the data row.
      $attributes = [];

      if (!empty($data['header_cta'])) {
        $attributes[] = 'Header CTA';
      }

      if (!empty($data['rr_cta'])) {
        $attributes[] = 'Right Rail CTA';
      }

      if (!empty($data['campaign_form'])) {
        $attributes[] = 'Campaign Form';
      }

      if (!empty($data['marketo_form'])) {
        $attributes[] = 'Marketo Interrupter';
      }

      if (!empty($data['text_and_form'])) {
        $attributes[] = 'Fifty/Fifty Text and Form';
      }

      if (!empty($data['gate_this_node'])) {
        $attributes[] = 'Gated Content';
      }

      $title = 'QA #' . $data['qa_id'] . ' - ' . $data['type'];

      if (!empty($attributes)) {
        $title .= ' with ' . implode(', ', $attributes);
      }

    }

    return $title;
  }

  /**
   * Converts the uploaded file into an associative array.
   *
   * @param int $fid
   *   The file id of the uploaded file.
   */
  protected function getMarketoData($fid) {
    $this->ul_test_data = \Drupal::service('ul_testing.file_service')->fileToArray($fid);
  }

  /**
   * Generate content when not in batch mode.
   *
   * This method is used when the number of elements is under 50.
   *
   * @param array $values
   *   Values form the settings form.
   */
  private function generateContent(array $values) {

    if (!empty($values['kill'])) {
      $this->contentKill($values);
    }

    if (!empty($this->ul_test_data)) {
      // Generate nodes.
      $this->develGenerateContentPreNode($values);
      $start = time();
      for ($i = 0; $i < count($this->ul_test_data); $i++) {
        $this->develGenerateContentAddNode($values);
        if (isset($values['feedback']) && $i % $values['feedback'] == 0) {
          $now = time();
          $options = [
            '@feedback' => $values['feedback'],
            '@rate' => ($values['feedback'] * 60) / ($now - $start),
          ];
          $this->messenger()->addStatus(dt('Completed @feedback nodes (@rate nodes/min)', $options));
          $start = $now;
        }
      }
    }
    $this->setMessage($this->formatPlural(count($this->ul_test_data), 'Created 1 node', 'Created @count nodes'));
  }

  /**
   * Generate content in batch mode.
   *
   * This method is used when the number of elements is 50 or more.
   */
  private function generateBatchContent($values) {
    // If it is drushBatch then this operation is already run in the
    // self::validateDrushParams().
    if (!$this->drushBatch) {
      // Setup the batch operations and save the variables.
      $operations[] = ['devel_generate_operation',
        [$this, 'batchContentPreNode', $values],
      ];
    }

    // Add the kill operation.
    if ($values['kill']) {
      $operations[] = ['devel_generate_operation',
        [$this, 'batchContentKill', $values],
      ];
    }

    // Add the operations to create the nodes.
    for ($num = 0; $num < count($this->ul_test_data); $num++) {
      $operations[] = ['devel_generate_operation',
        [$this, 'batchContentAddNode', $values],
      ];
    }

    // Set the batch.
    $batch = [
      'title' => $this->t('Generating Content'),
      'operations' => $operations,
      'finished' => 'devel_generate_batch_finished',
      'file' => $this->extensionPathResolver->getPath('module', 'devel_generate') . '/devel_generate.batch.inc',
    ];
    batch_set($batch);
    if ($this->drushBatch) {
      drush_backend_batch_process();
    }
  }

  /**
   * Batch wrapper for calling ContentPreNode.
   */
  public function batchContentPreNode($vars, &$context) {
    $context['results'] = $vars;
    $context['results']['num'] = 0;
    $context['results']['num_translations'] = 0;
    $this->develGenerateContentPreNode($context['results']);
  }

  /**
   * Batch wrapper for calling ContentAddNode.
   */
  public function batchContentAddNode($vars, &$context) {
    if ($this->drushBatch) {
      $this->develGenerateContentAddNode($vars);
    }
    else {
      $this->develGenerateContentAddNode($context['results']);
    }
    $context['results']['num']++;
    if (!empty($vars['num_translations'])) {
      $context['results']['num_translations'] += $vars['num_translations'];
    }
  }

  /**
   * Batch wrapper for calling ContentKill.
   */
  public function batchContentKill($vars, &$context) {
    if ($this->drushBatch) {
      $this->contentKill($vars);
    }
    else {
      $this->contentKill($context['results']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateDrushParams(array $args, array $options = []) {
    $add_language = StringUtils::csvToArray($options['languages']);
    // Intersect with the enabled languages to make sure the language args
    // passed are actually enabled.
    $valid_languages = array_keys($this->languageManager->getLanguages(LanguageInterface::STATE_ALL));
    $values['add_language'] = array_intersect($add_language, $valid_languages);

    $translate_language = StringUtils::csvToArray($options['translations']);
    $values['translate_language'] = array_intersect($translate_language, $valid_languages);

    $values['add_type_label'] = $options['add-type-label'];
    $values['kill'] = $options['kill'];
    $values['feedback'] = $options['feedback'];
    $values['title_length'] = 6;
    $values['num'] = array_shift($args);
    $values['max_comments'] = array_shift($args);
    $all_types = array_keys(node_type_get_names());
    $default_types = array_intersect(['page', 'article'], $all_types);
    $selected_types = StringUtils::csvToArray($options['bundles'] ?: $default_types);

    if (empty($selected_types)) {
      throw new \Exception(dt('No content types available'));
    }

    $values['node_types'] = array_combine($selected_types, $selected_types);
    $node_types = array_filter($values['node_types']);

    if (!empty($values['kill']) && empty($node_types)) {
      throw new \Exception(dt('To delete content, please provide the content types (--bundles)'));
    }

    // Checks for any missing content types before generating nodes.
    if (array_diff($node_types, $all_types)) {
      throw new \Exception(dt('One or more content types have been entered that don\'t exist on this site'));
    }

    $values['authors'] = is_null($options['authors']) ? [] : explode(',',
      $options['authors']);

    if ($this->isBatch($values)) {
      $this->drushBatch = TRUE;
      $this->develGenerateContentPreNode($values);
    }

    return $values;
  }

  /**
   * Determines if the content should be generated in batch mode.
   */
  protected function isBatch($values) {
    return count($this->ul_test_data) > $this->getDefaultSettings()['batch_size'];
  }

  /**
   * Deletes all nodes of given node types.
   *
   * @param array $values
   *   The input values from the settings form.
   */
  protected function contentKill(array $values) {

    $result = $this->database->select('ul_testing_test_node', 't')
      ->fields('t', ['nid'])
      ->execute()
      ->fetchAll(\PDO::FETCH_OBJ);

    $nids = [];
    foreach ($result as $row) {
      $nids[] = $row->nid;
    }

    if (!empty($nids)) {
      $nodes = $this->nodeStorage->loadMultiple($nids);
      $this->nodeStorage->delete($nodes);
      $this->setMessage($this->t('Deleted %count test nodes.', ['%count' => count($nids)]));

      $result = $this->database->delete('ul_testing_test_node')
        ->condition('nid', $nids, 'IN')
        ->execute();
    }
  }

  /**
   * Preprocesses $results before adding content.
   *
   * @param array $results
   *   Results information.
   */
  protected function develGenerateContentPreNode(array &$results) {
  }

  /**
   * Create one node. Used by both batch and non-batch code branches.
   *
   * @param array $results
   *   Results information.
   */
  protected function develGenerateContentAddNode(array &$results) {

    $index = $results['index'];
    // Translation langcode.
    $tLangcode = $this->ul_test_data[$index]['langcode'];
    $tLangcode = empty($tLangcode) ? 'en' : $tLangcode;
    $title = $this->getTitle($index);

    $values = [
      'nid' => NULL,
      'title' => $title,
      'type' => $this->ul_test_data[$index]['type'],
      'uid' => 1,
      'status' => TRUE,
      'promote' => 0,
      'created' => $this->time->getRequestTime(),
      // Always create default 'en' node first.
      'langcode' => 'en',
    ];

    $node = $this->nodeStorage->create($values);

    // Set values for the fields that can be auto-generated.
    \Drupal::service('ul_testing.utility_service')->setAutoFields($node, $this->ul_test_data, $index);

    $this->addBeforeFormContent($node, $index);

    // The is done in setAutoFields
    // $this->addLastInterest($node, $index);.
    $this->addMarketoForms($node, $index);

    $this->addAfterFormContent($node, $index);

    // Set node published.
    if ($node instanceof EntityPublishedTrait) {
      $node->setPublished(TRUE);
    }
    if ($node instanceof ContentEntityBase) {
      $node->set('moderation_state', 'published');
    }

    // Create a new node only if the translation language exists.
    $tLanguage = $this->languageManager->getLanguage($tLangcode);
    if ($tLanguage instanceof LanguageInterface) {

      if ($tLangcode !== 'en') {

        try {
          if ($node instanceof ContentEntityBase) {
            $node->addTranslation($tLangcode, $values);
          }
        }
        catch (\InvalidArgumentException $e) {
          \Drupal::messenger()->addMessage($e);
        }
      }

      $node->save();

      // Add test node to ul_testing_test_node table.
      $this->database->insert('ul_testing_test_node')
        ->fields([
          'nid' => $node->id(),
          'test_group' => $this->getDefaultSettings()['test_group'],
          'type' => $node->bundle(),
          'qa_id' => $this->ul_test_data[$index]['qa_id'],
        ])
        ->execute();
    }
    else {
      \Drupal::messenger()->addMessage("The lanauge ($tLangcode) is NOT installed on the site, ignore the Node ($title).");
    }
    // @todo add translations?
    $results['index']++;
  }

  /**
   * Add content to a node based on its type before any marketo forms.
   *
   * @param object $node
   *   The node to add content to.
   * @param int $index
   *   The row index from the data array.
   */
  protected function addBeforeFormContent(object &$node, $index) {

    // Make sure node has a content field defined.
    $bundle = $node->bundle();
    if (isset(\Drupal::service('ul_testing.validation_service')->getTypeInfo($bundle)['content_field'])) {

      // If no before_form_content or after_form_content is set,
      // generate default content for the content type.
      if (empty($this->ul_test_data[$index]['before_form_content'])) {
        $this->addDefaultContent($node, $this->ul_test_data, $index);
      }
      else {
        foreach ($this->ul_test_data[$index]['before_form_content'] as $paragraph_type) {
          $this->addContentParagraph($node, $paragraph_type, $this->ul_test_data, $index);
        }
      }

    }
  }

  /**
   * Add content to a node based on its type after any marketo forms.
   *
   * @param object $node
   *   The node to add content to.
   * @param int $index
   *   The row index from the data array.
   */
  protected function addAfterFormContent(object &$node, $index) {

    // Make sure node has a content field defined.
    $bundle = $node->bundle();
    if (isset(\Drupal::service('ul_testing.validation_service')->getTypeInfo($bundle)['content_field'])) {
      foreach ($this->ul_test_data[$index]['after_form_content'] as $paragraph_type) {
        $this->addContentParagraph($node, $paragraph_type, $this->ul_test_data, $index);
      }
    }
  }

  /**
   * Add custom content paragraph.
   *
   * @param object $node
   *   The node to add content to.
   * @param string $paragraph_type
   *   The row index from the data array.
   * @param array $data
   *   The uploaded test data array.
   * @param int $index
   *   Optional index in the test data array.
   */
  protected function addContentParagraph(&$node, $paragraph_type, array &$data, $index) {

    $content_field = \Drupal::service('ul_testing.validation_service')->getTypeInfo($node->bundle())['content_field'];

    \Drupal::service('ul_testing.utility_service')->addParagraph($node, $content_field, $paragraph_type, NULL, $data, $index);
  }

  /**
   * Add default content to a node based on its type.
   *
   * @param object $node
   *   The node to add content to.
   * @param array $data
   *   The uploaded test data array.
   * @param int $index
   *   Optional index in the test data array.
   */
  protected function addDefaultContent(object &$node, array &$data, $index) {

    $bundle = $node->bundle();

    $content_field = \Drupal::service('ul_testing.validation_service')->getTypeInfo($bundle)['content_field'];

    // Add content paragraphs which are required to verify gated content
    // forms are working.
    switch ($bundle) {

      // The campaign_page content type is the only one that does not use
      // basic_content it uses the body field + card paragraphs.
      case 'campaign_page':
        // Add body content.
        $node->body->generateSampleItems();

        // Add card paragraphs.
        \Drupal::service('ul_testing.utility_service')->addParagraph($node, $content_field, 'campaign_cards', NULL, $data, $index);

        break;

      default:
        $paragraph_type = 'basic_content';

        for ($p = 0; $p < $this->num_content; $p++) {
          \Drupal::service('ul_testing.utility_service')->addParagraph($node, $content_field, $paragraph_type, NULL, $data, $index);
        }

        break;
    }
  }

  /**
   * Add marketo forms for CTA and paragraph fields.
   *
   * @param object $node
   *   The node to add forms to.
   * @param int $index
   *   The row index from the uploaded data.
   */
  protected function addMarketoForms(object &$node, $index) {
    $header_cta = $this->ul_test_data[$index]['header_cta'];
    $rr_cta = $this->ul_test_data[$index]['rr_cta'];

    $campaign_form = $this->ul_test_data[$index]['campaign_form'];
    $marketo_form = $this->ul_test_data[$index]['marketo_form'];
    $text_and_form = $this->ul_test_data[$index]['text_and_form'];

    // Array of marketo form entities that need to be created.
    $customizations = [];

    // Header CTA.
    if (!empty($header_cta)) {

      $customizations[] = $header_cta;

      if ($node->hasField('field_shared_marketo_link')) {
        $node->set('field_shared_marketo_link', $header_cta);
      }

    }

    // Right Rail CTA.
    if (!empty($rr_cta)) {

      $customizations[] = $rr_cta;

      if ($node->hasField('field_shared_rr_marketo_cta')) {
        $node->set('field_shared_rr_marketo_cta', $rr_cta);
        $node->set('field_shared_rr_checkbox', 1);
      }

    }

    // Campaign Form.
    if (!empty($campaign_form)) {
      $customizations[] = $campaign_form;

      $this->addMarketoFormParagraph('campaign_form', $node, $index);
    }

    // Marketo Form.
    if (!empty($marketo_form)) {
      $customizations[] = $marketo_form;

      $this->addMarketoFormParagraph('marketo_form', $node, $index);
    }

    // Text and Form.
    if (!empty($text_and_form)) {
      $customizations[] = $text_and_form;

      $this->addMarketoFormParagraph('text_and_form', $node, $index);
    }

    // Gated Content.
    // Unlike other form types, gated_forms don't have a corresponding CTA
    // or paragraph. Use the gate_this_node value from the spreadsheet upload.
    if ($this->ul_test_data[$index]['gate_this_node']) {
      $customizations[] = 'gated_content_form';
    }

    // Make sure customization types are unique.
    $customizations = array_unique($customizations);

    $this->addMarketoCustomizations($node, $customizations, $index);

    $node->save();

  }

  /**
   * Add marketo form paragraphs.
   *
   * @param string $paragraph_type
   *   The paragraph_type that supports marketo forms.
   * @param object $node
   *   The node to add paragraphs to.
   * @param int $index
   *   The row index from the data array.
   */
  protected function addMarketoFormParagraph($paragraph_type, object &$node, $index) {
    $bundle = $node->bundle();
    $content_field = \Drupal::service('ul_testing.validation_service')->getTypeInfo($bundle)['content_field'];

    // All marketo related paragraphs have field_marketo_form.
    $field_values = [
      'field_marketo_form' => $this->ul_test_data[$index][$paragraph_type],
    ];

    switch ($paragraph_type) {
      case 'campaign_form':
        switch ($bundle) {
          case 'campaign_page':
            \Drupal::service('ul_testing.utility_service')->addParagraph($node, 'field_campaign_page_marketo_form', $paragraph_type, $field_values, $this->ul_test_data, $index);
            break;
        }

        break;

      case 'marketo_form':
        // Add marketo_form specific fields.
        $field_values['field_shared_bg_color'] = NULL;
        $field_values['field_hash_target'] = NULL;
        \Drupal::service('ul_testing.utility_service')->addParagraph($node, $content_field, $paragraph_type, $field_values, $this->ul_test_data, $index);
        break;

      case 'text_and_form':
        // Add text_and_form specific fields.
        $field_values['field_shared_bg_color'] = NULL;
        $field_values['field_text_and_form_content'] = NULL;
        $field_values['field_shared_view_mode'] = NULL;
        \Drupal::service('ul_testing.utility_service')->addParagraph($node, $content_field, $paragraph_type, $field_values, $this->ul_test_data, $index);
        break;
    }

  }

  /**
   * Adds marketo_form entities.
   *
   * @param object $node
   *   The node to add form entities to.
   * @param array $customizations
   *   The array of marketo_form types to add.
   * @param int $index
   *   The row index from the data array.
   */
  protected function addMarketoCustomizations(object &$node, array $customizations, $index) {
    // Array of form_ids to be added to the customization field
    // - field_shared_marketo_custom.
    $form_ids = [];

    foreach ($customizations as $c) {
      // Fix issue of generating Bad Node which contains a loing-string
      // name/title, sub-title and etc.
      // The bad node also messed up the Main menu on Latam site.
      // "Thank you page" entity reference in market_form caused issues.
      $form = $this->getOrCreateMarketoForm($node, $c, $index);

      $form_ids[] = [
        'target_id' => $form->id(),
        'target_revision_id' => $form->getRevisionId(),
      ];

    }

    if (!empty($form_ids)) {
      $node->set('field_shared_marketo_custom', $form_ids);
      $node->save();
    }

  }

  /**
   * Get or create a MarketoForm entity.
   *
   * @param object $node
   *   The node to add form entities to.
   * @param string $c
   *   The MarketForm bundle name.
   * @param int $index
   *   The row index from the data array.
   *
   * @return \Drupal\ul_marketo\Entity\MarketoFormInterface
   *   The MarketForm enitity object.
   */
  protected function getOrCreateMarketoForm(object &$node, string $c, $index) {

    $values = [
      'type' => $c,
    ];

    $form = MarketoForm::create($values);

    $form->setNewRevision(TRUE);

    $defaults = \Drupal::service('ul_marketo')->getFormDefaultSettings($form);

    // Override the default values with custom values if they are set.
    switch ($c) {
      case 'generic_form':
        if (!empty($this->ul_test_data[$index]['generic_form_button_text'])) {
          $defaults['field_shared_button_text'] = $this->ul_test_data[$index]['generic_form_button_text'];
        }
        if (!empty($this->ul_test_data[$index]['generic_form_custom_title'])) {
          $defaults['field_shared_custom_title'] = $this->ul_test_data[$index]['generic_form_custom_title'];
        }
        if (!empty($this->ul_test_data[$index]['generic_form_description'])) {
          $defaults['field_shared_form_description'] = $this->ul_test_data[$index]['generic_form_description'];
        }

        if (!empty($this->ul_test_data[$index]['generic_form_thank_you_page'])) {
          $defaults['field_shared_thank_you_page'] = \Drupal::service('ul_testing.field_generator_service')->getThankYouPage($this->ul_test_data[$index]['generic_form_thank_you_page']);
        }
        break;

      case 'email_form':
        if (!empty($this->ul_test_data[$index]['email_form_button_text'])) {
          $defaults['field_shared_button_text'] = $this->ul_test_data[$index]['email_form_button_text'];
        }
        if (!empty($this->ul_test_data[$index]['email_form_custom_title'])) {
          $defaults['field_shared_custom_title'] = $this->ul_test_data[$index]['email_form_custom_title'];
        }
        if (!empty($this->ul_test_data[$index]['email_form_description'])) {
          $defaults['field_shared_form_description'] = $this->ul_test_data[$index]['email_form_description'];
        }
        if (!empty($this->ul_test_data[$index]['email_form_newsletter_type'])) {
          $defaults['field_newsletter_type'] = $this->ul_test_data[$index]['email_form_newsletter_type'];
        }

        if (!empty($this->ul_test_data[$index]['email_form_thank_you_page'])) {
          $defaults['field_shared_thank_you_page'] = \Drupal::service('ul_testing.field_generator_service')->getThankYouPage($this->ul_test_data[$index]['email_form_thank_you_page']);
        };

        break;

      case 'event_form':
        if (!empty($this->ul_test_data[$index]['event_form_button_text'])) {
          $defaults['field_shared_button_text'] = $this->ul_test_data[$index]['event_form_button_text'];
        }
        if (!empty($this->ul_test_data[$index]['event_form_custom_title'])) {
          $defaults['field_shared_custom_title'] = $this->ul_test_data[$index]['event_form_custom_title'];
        }
        if (!empty($this->ul_test_data[$index]['event_form_description'])) {
          $defaults['field_shared_form_description'] = $this->ul_test_data[$index]['event_form_description'];
        }

        if (!empty($this->ul_test_data[$index]['event_form_thank_you_page'])) {
          $defaults['field_shared_thank_you_page'] = \Drupal::service('ul_testing.field_generator_service')->getThankYouPage($this->ul_test_data[$index]['event_form_thank_you_page']);
        };

        break;

      case 'gated_content_form':
        if (!empty($this->ul_test_data[$index]['gated_content_form_button_text'])) {
          $defaults['field_shared_button_text'] = $this->ul_test_data[$index]['gated_content_form_button_text'];
        }
        if (!empty($this->ul_test_data[$index]['gated_content_form_custom_title'])) {
          $defaults['field_shared_custom_title'] = $this->ul_test_data[$index]['gated_content_form_custom_title'];
        }
        if (!empty($this->ul_test_data[$index]['gated_content_form_description'])) {
          $defaults['field_shared_form_description'] = $this->ul_test_data[$index]['gated_content_form_description'];
        }
        if (!empty($this->ul_test_data[$index]['gated_content_form_gated_asset_title'])) {
          $defaults['field_gated_asset_title'] = $this->ul_test_data[$index]['gated_content_form_gated_asset_title'];
        }
        if (!empty($this->ul_test_data[$index]['gated_content_form_gated_asset_language'])) {
          $defaults['field_gated_asset_language'] = $this->ul_test_data[$index]['gated_content_form_gated_asset_language'];
        }
        break;
    }

    $form->set('langcode', $node->get('langcode')->value);
    $form->set('name', $defaults['label']);

    $fields = [
      'field_shared_button_text',
      'field_shared_custom_title',
      'field_shared_form_description',
      'field_newsletter_type',
      'field_gated_asset_title',
      'field_gated_asset_language',
      'field_shared_thank_you_page',
    ];

    foreach ($fields as $f) {
      if (!empty($defaults[$f])) {
        $form->set($f, $defaults[$f]);
      }
      elseif ($form->hasField($f)) {
        \Drupal::service('ul_testing.field_generator_service')->setEntityFieldValue($form, $f, $this->ul_test_data, $index);
      }
    }

    $form->save();

    return $form;
  }

  /**
   * Add last interest & sub cou values based on uploaded data or random values.
   *
   * @param object $node
   *   The node to add last interest values to.
   * @param int $index
   *   The row index of the uploaded data.
   */
  protected function addLastInterest(object &$node, $index) {
    $last_interest = [
      'instance' => 'Enterprise',
      'sub_cou' => $this->ul_test_data[$index]['sub_cou'],
      'last_interest' => $this->ul_test_data[$index]['last_interest'],
    ];

    $node->set('field_shared_marketo_forms', $last_interest);

    // We're using the last interest and sub cou values for the sub title
    // so that those values are readily availble during testing.
    if ($node->hasField('field_shared_subtitle')) {
      $node->set('field_shared_subtitle', 'Sub COU: ' . $last_interest['sub_cou'] . ' Last Interest: ' . $last_interest['last_interest']);
    }

  }

  /**
   * Create translation for the given node.
   *
   * @param array $results
   *   Results array.
   * @param \Drupal\node\NodeInterface $node
   *   Node to add translations to.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function develGenerateContentAddNodeTranslation(array &$results, NodeInterface $node) {
    if (is_null($this->contentTranslationManager)) {
      return;
    }
    if (!$this->contentTranslationManager->isEnabled('node', $node->getType())) {
      return;
    }
    if ($node->langcode == LanguageInterface::LANGCODE_NOT_SPECIFIED || $node->langcode == LanguageInterface::LANGCODE_NOT_APPLICABLE) {
      return;
    }

    if (!isset($results['num_translations'])) {
      $results['num_translations'] = 0;
    }
    // Translate node to each target language.
    $skip_languages = [
      LanguageInterface::LANGCODE_NOT_SPECIFIED,
      LanguageInterface::LANGCODE_NOT_APPLICABLE,
      $node->langcode->value,
    ];
    foreach ($results['translate_language'] as $langcode) {
      if (in_array($langcode, $skip_languages)) {
        continue;
      }
      $translation_node = $node->addTranslation($langcode);
      $translation_node->devel_generate = $results;
      $translation_node->setTitle($node->getTitle() . ' (' . $langcode . ')');
      $this->populateFields($translation_node);
      $translation_node->save();
      if ($translation_node->id() > 0 && !empty($results['add_alias'])) {
        $path_alias = $this->aliasStorage->create([
          'path' => '/node/' . $translation_node->id(),
          'alias' => '/node-' . $translation_node->id() . '-' . $translation_node->bundle() . '-' . $langcode,
          'langcode' => $langcode,
        ]);
        $path_alias->save();
      }
      $results['num_translations']++;
    }
  }

}

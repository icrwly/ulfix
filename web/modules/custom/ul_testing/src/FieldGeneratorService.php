<?php

namespace Drupal\ul_testing;

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Component\Utility\Random;
use Drupal\Component\Datetime\DateTimePlus;
use Drupal\node\Entity\Node;

/**
 * Auto-generating field values based on entity type, field & field type.
 */
class FieldGeneratorService {

  /**
   * Max number of items for fields with unlimited cardinality.
   *
   * @var int
   */
  public static $MAX_ITEMS = 10;

  /**
   * Max days into the future when generating event dates.
   *
   * @var int
   */
  public static $MAX_EVENT_DAYS = 100;

  /**
   * Max duration of events in minutes.
   *
   * @var int
   */
  public static $MAX_EVENT_DURATION = 480;

  /**
   * Number of minutes to round event times to.
   *
   * @var int
   */
  public static $EVENT_MINUTE_INTERVAL = 30;

  /**
   * Random value generator.
   *
   * @var random
   */
  protected $random;

  /**
   * Ul Testing validation_service.
   *
   * @var ulService
   */
  public $ulService;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a UtilityService object.
   */
  public function __construct() {
    // You can skip injecting this service, the trait will fall back to
    // Drupal::translation() but it is recommended to do so,
    // for easier testability.
    $this->random = new Random();
    $this->ulService = \Drupal::service('ul_testing.validation_service');
    $this->database = \Drupal::database();
    $this->entityTypeManager = \Drupal::entityTypeManager();
    $this->time = \Drupal::time();
  }

  /**
   * Returns an an array of custom fields for an entity.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   The bundle.
   * @param string $field
   *   The field name.
   *
   * @return int
   *   The cardinality value, -1 meaning unlimited.
   */
  public function getCardinality($entity_type, $bundle, $field) {
    return \Drupal::service('entity_field.manager')->getFieldDefinitions($entity_type, $bundle)[$field]->get('fieldStorage')->get('cardinality');
  }

  /**
   * Round minutes to the nearest interval of a DateTime object.
   *
   * @param object $dateTime
   *   Datetime to round.
   * @param int $minuteInterval
   *   Minute interval to round to.
   *
   * @return object
   *   Rounded datetime
   */
  public function roundToNearestMinuteInterval($dateTime, $minuteInterval = 30) {
    return $dateTime->setTime(
      $dateTime->format('H'),
      round($dateTime->format('i') / $minuteInterval) * $minuteInterval,
      0
    );
  }

  /**
   * Return a string of custom field method name.
   *
   * Returns a string representing what the custom field method name would be
   * based on entity_type and field name.
   *
   * @param string $entity_type
   *   The entity type the field belongs to.
   * @param string $field
   *   The name of the entity field.
   *
   * @return string
   *   The name of the custom method.
   */
  public function getCustomFieldMethod($entity_type, $field) {
    $field = $entity_type . " " . str_replace("_", " ", $field);
    $field = ucwords($field);
    $field = str_replace(" ", "", $field);
    return 'set' . $field;
  }

  /**
   * Return a string of custom field type method name.
   *
   * Returns a string representing what the custom field type method name would
   * be based on entity_type and field type.
   *
   * @param string $entity_type
   *   The entity type the field belongs to.
   * @param string $field_type
   *   The name of the entity field.
   *
   * @return string
   *   The name of the custom method in the form get<Entity_type>
   *   <Field Part 1><Field Part 2> etc. Paragraph field_content would be
   *   'getParagraphFieldContent'.
   */
  public function getCustomFieldTypeMethod($entity_type, $field_type) {
    $field_type = $entity_type . " " . str_replace("_", " ", $field_type);
    $field_type = ucwords($field_type);
    $field_type = str_replace(" ", "", $field_type);
    return 'set' . $field_type;
  }

  /**
   * Sets the value ofan entity field using custom method or random generation.
   *
   * @param object $entity
   *   The entity to set the field value on.
   * @param string $field
   *   The name of the field to set.
   * @param array $data
   *   The uploaded test data array.
   * @param int $index
   *   Optional index in the test data array.
   * @param object $parent_entity
   *   Optional parent entity, used when paragraphs need index in the test
   *   data array.
   */
  public function setEntityFieldValue(&$entity, $field, array &$data, $index, &$parent_entity = NULL) {

    $entity_type = $entity->getEntityType()->id();
    $bundle = $entity->bundle();

    $field_info = $entity->get($field)->getFieldDefinition();
    $field_type = $field_info->getType();

    $custom_field_method = $this->getCustomFieldMethod($entity_type, $field);
    $custom_field_type_method = $this->getCustomFieldTypeMethod($entity_type, $field_type);

    // First check to see if a custom method is defined for this entity field.
    // Call the set method of the custom field class if it is defined.
    if (method_exists($this, $custom_field_method)) {
      $this->{$custom_field_method}($entity, $data, $index, $parent_entity);
    }
    elseif (method_exists($this, $custom_field_type_method)) {
      $this->{$custom_field_type_method}($entity, $field_info, $data, $index);
    }
    // Else call generateSampleItems with a proper value based on cardinalty.
    else {
      $cardinality = $this->getCardinality($entity_type, $bundle, $field);
      $entity->{$field}->generateSampleItems($cardinality == -1 ? rand(1, self::$MAX_ITEMS) : 1);
    }

  }

  /**
   * Return a random sentence with min_words teruncarted to max_length.
   *
   * Returns a random sentence with $min_words minimum words teruncarted to
   * $max_length if set.
   *
   * @param int $min_word
   *   Minimum number of words.
   * @param int $max_length
   *   Optional max length to truncate sentence to.
   *
   * @return string
   *   Generated random sentence.
   */
  public function getRandomSentence($min_word, $max_length = NULL) {
    $value = $this->random->sentences($min_word, TRUE);
    if (!empty($max_length)) {
      $value = substr($value, 0, $max_length);
    }

    return $value;
  }

  /**
   * Sets an entity reference field of a node.
   *
   * Then node is randomly selected entity following the field definition rules.
   *
   * @param object $entity
   *   The node to set the string value in.
   * @param object $field_definition
   *   Field Def object of the field to set.
   * @param array $data
   *   The uploaded test data array.
   * @param int $index
   *   Optional index in the test data array.
   */
  public function setNodeEntityReference(&$entity, $field_definition, array &$data, $index) {
    $field_name = $field_definition->getName();
    // An associative array keyed by the reference type, target type, and
    // bundle.
    $manager = \Drupal::service('plugin.manager.entity_reference_selection');

    // Instead of calling $manager->getSelectionHandler($field_definition)
    // replicate the behavior to be able to override the sorting settings.
    $options = [
      'target_type' => $field_definition->getFieldStorageDefinition()->getSetting('target_type'),
      'handler' => $field_definition->getSetting('handler'),
      'entity' => NULL,
    ] + $field_definition->getSetting('handler_settings') ?: [];

    $entity_type = $this->entityTypeManager->getDefinition($options['target_type']);
    $options['sort'] = [
      'field' => $entity_type->getKey('id'),
      'direction' => 'DESC',
    ];
    $selection_handler = $manager->getInstance($options);

    // Select a random number of references between the last 50 referenceable
    // entities created.
    if ($referenceable = $selection_handler->getReferenceableEntities(NULL, 'CONTAINS', 50)) {
      $group = array_rand($referenceable);
      $target_id = array_rand($referenceable[$group]);
      $entity->set($field_name, $target_id);
    }
  }

  /**
   * Set an entity reference field of a paragraph.
   *
   * Call setNodeEntityReference to set an entity reference field of a paragraph
   * to a randomly selected entity following the field definition rules.
   *
   * @param object $entity
   *   The paragraph to set the string value in.
   * @param object $field_definition
   *   Field Def object of the field to set.
   * @param array $data
   *   The uploaded test data array.
   * @param int $index
   *   Optional index in the test data array.
   */
  public function setParagraphEntityReference(&$entity, $field_definition, array &$data, $index) {
    $this->setNodeEntityReference($entity, $field_definition, $data, $index);
  }

  /**
   * Sets an entity field value.
   *
   * @param object $entity
   *   Entity to set the value for.
   * @param array $data
   *   The uploaded test data array.
   * @param int $index
   *   Optional index in the test data array.
   */
  public function setNodeFieldEventDate(&$entity, array &$data, $index) {

    $start_date = new DateTimePlus();
    $num_days = rand(1, self::$MAX_EVENT_DAYS);

    $num_minutes = rand(1, self::$MAX_EVENT_DURATION);

    $start_interval = \DateInterval::createFromDateString($num_days . ' day');

    $start_date->add($start_interval);
    $this->roundToNearestMinuteInterval($start_date, self::$EVENT_MINUTE_INTERVAL);

    $end_date = clone $start_date;

    $end_interval = \DateInterval::createFromDateString($num_minutes . ' minute');
    $end_date->add($end_interval);
    $this->roundToNearestMinuteInterval($end_date, self::$EVENT_MINUTE_INTERVAL);

    $entity->set('field_event_date', [
      'value' => $start_date->format('Y-m-d\Th:i:s'),
      'end_value' => $end_date->format('Y-m-d\Th:i:s'),
    ]);
  }

  /**
   * Sets an entity field value.
   *
   * @param object $entity
   *   Entity to set the value for.
   * @param array $data
   *   The uploaded test data array.
   * @param int $index
   *   Optional index in the test data array.
   */
  public function setNodeFieldSharedDomain(&$entity, array &$data, $index) {
    // The content domain can get set by the setParagraphFieldViewView function.
    // Do not overwrite if already set.
    if (empty($domain)) {
      $bundle = $entity->bundle();
      $entity->set('field_shared_domain', $this->ulService->getContentDomain($bundle));
    }

  }

  /**
   * Sets the field_location_addresses value of a node.
   *
   * @param object $entity
   *   Entity to set the value for.
   * @param array $data
   *   The uploaded test data array.
   * @param int $index
   *   Optional index in the test data array.
   */
  public function setNodeFieldLocationAddresses(&$entity, array &$data, $index) {
    $entity->field_location_addresses->generateSampleItems(rand(1, self::$MAX_ITEMS));
    $items = $entity->field_location_addresses->getValue();

    // Use getRandomSentence to prevent long non-breaking strings.
    foreach ($items as $item) {
      $p = Paragraph::load($item['target_id']);

      $field_values = \Drupal::service('ul_testing.utility_service')->getCustomFields('paragraph', $p->bundle(), TRUE);

      foreach ($field_values as $f => $v) {
        if (empty($v)) {
          $this->setEntityFieldValue($p, $f, $data, $index);
        }
        else {
          $p->set($f, $v);
        }
      }

      $p->save();
    }
  }

  /**
   * Sets an entity field value.
   *
   * @param object $entity
   *   Entity to set the value for.
   * @param array $data
   *   The uploaded test data array.
   * @param int $index
   *   Optional index in the test data array.
   */
  public function setNodeFieldSharedMarketoForms(&$entity, array &$data, $index) {

    $last_interest = [
      'instance' => 'Enterprise',
      'sub_cou' => $data[$index]['sub_cou'],
      'last_interest' => $data[$index]['last_interest'],
    ];

    $entity->set('field_shared_marketo_forms', $last_interest);

  }

  /**
   * Sets a 4 digit support ticket number.
   *
   * @param object $entity
   *   Entity to set the marketing support ticket for.
   * @param array $data
   *   The uploaded test data array.
   * @param int $index
   *   Optional index in the test data array.
   */
  public function setNodeFieldSharedMktgSupportTicket(&$entity, array &$data, $index) {
    // Add support ticket - random number between 1 and 9999.
    $entity->set('field_shared_mktg_support_ticket', 11111);
  }

  /**
   * Sets an entity field value.
   *
   * @param object $entity
   *   Entity to set the value for.
   * @param array $data
   *   The uploaded test data array.
   * @param int $index
   *   Optional index in the test data array.
   */
  public function setNodeFieldSharedSubtitle(&$entity, array &$data, $index) {

    // We're using the last interest and sub cou values for the sub title
    // so that those values are readily availble during testing.
    // The shared_subtitle fields uses values from last interest and sub cou
    // so those fields should be set before calling this set method.
    $last_interest = $entity->get('field_shared_marketo_forms')->getValue()[0];
    $entity->set('field_shared_subtitle', 'Sub COU: ' . $last_interest['sub_cou'] . ' Last Interest: ' . $last_interest['last_interest']);
  }

  /**
   * Sets an entity field value.
   *
   * @param object $entity
   *   Entity to set the value for.
   * @param array $data
   *   The uploaded test data array.
   * @param int $index
   *   Optional index in the test data array.
   */
  public function setParagraphFieldAccrdnItems(&$entity, array &$data, $index) {
    $entity->field_accrdn_items->generateSampleItems(rand(1, self::$MAX_ITEMS));
    $items = $entity->field_accrdn_items->getValue();
    foreach ($items as $item) {
      $p = Paragraph::load($item['target_id']);
      $p->set('field_accrdn_item_title', $this->getRandomSentence(1, 32));
      $p->save();
    }
  }

  /**
   * Sets an entity field value.
   *
   * @param object $entity
   *   Entity to set the value for.
   * @param array $data
   *   The uploaded test data array.
   * @param int $index
   *   Optional index in the test data array.
   */
  public function setParagraphFieldCmpgnCardsCards(&$entity, array &$data, $index) {
    $entity->field_cmpgn_cards_cards->generateSampleItems(rand(1, self::$MAX_ITEMS));
    $cards = $entity->field_cmpgn_cards_cards->getValue();
    foreach ($cards as $card) {
      $p = Paragraph::load($card['target_id']);
      $title = $this->getRandomSentence(1, 32);
      $p->set('field_campaign_card_title', $title);
      $p->save();
    }

  }

  /**
   * Sets an entity field value.
   *
   * @param object $entity
   *   Entity to set the value for.
   * @param array $data
   *   The uploaded test data array.
   * @param int $index
   *   Optional index in the test data array.
   * @param object $parent_entity
   *   Parent node to set the content domain field if necessary.
   */
  public function setParagraphFieldViewView(&$entity, array &$data, $index, $parent_entity) {

    // Randomly retrieve an exsting entry from the
    // paragraph__field_view_view table.
    $q = "SELECT * FROM paragraph__field_view_view ORDER BY rand() LIMIT 1";
    $result = $this->database->query($q)->fetch();
    $value = [
      'target_id' => $result->field_view_view_target_id,
      'display_id' => $result->field_view_view_display_id,
      'data' => $result->field_view_view_data,
    ];

    $entity->set('field_view_view', $value);

    $ulService = $this->ulService;

    // Experience Hub views need to have matching content domains.
    if ($parent_entity->hasField('field_shared_domain')) {
      switch ($value['target_id']) {
        case 'experience_hub_events':
          $parent_entity->set('field_shared_domain', $ulService->getContentDomain('event'));
          break;

        case 'experience_hub_help':
          $parent_entity->set('field_shared_domain', $ulService->getContentDomain('help'));
          break;

        case 'experience_hub_industry_insights':
          $parent_entity->set('field_shared_domain', $ulService->getContentDomain('knowledge'));
          break;

        case 'experience_hub_insights':
          $parent_entity->set('field_shared_domain', $ulService->getContentDomain('knowledge'));
          break;

        case 'experience_hub_knowledge':
          $parent_entity->set('field_shared_domain', $ulService->getContentDomain('knowledge'));
          break;

        case 'experience_hub_news':
          $parent_entity->set('field_shared_domain', $ulService->getContentDomain('news'));
          break;

        case 'experience_hub_offerings':
          $parent_entity->set('field_shared_domain', $ulService->getContentDomain('offering'));
          break;

        case 'experience_hub_resources':
          $parent_entity->set('field_shared_domain', $ulService->getContentDomain('resource'));
          break;

        case 'experience_hub_tools':
          $parent_entity->set('field_shared_domain', $ulService->getContentDomain('tool'));
          break;
      }

      $parent_entity->save();
    }
  }

  /**
   * Generates a random sentence string.
   *
   * @param object $entity
   *   The node to set the string value in.
   * @param object $field_info
   *   Field Info object of the field to set.
   * @param array $data
   *   The uploaded test data array.
   * @param int $index
   *   Optional index in the test data array.
   */
  public function setNodeString(&$entity, $field_info, array &$data, $index) {
    $field_name = $field_info->getName();
    $settings = $field_info->getSettings();
    $value = $this->getRandomSentence(1, $settings['max_length']);
    $entity->set($field_name, $value);
  }

  /**
   * Generates a random sentence string.
   *
   * @param object $entity
   *   The node to set the string value in.
   * @param string $field_name
   *   Field Info object of the field to set.
   * @param array $data
   *   The uploaded test data array.
   * @param int $index
   *   Optional index in the test data array.
   */
  public function setNodeSharedHeaderDescription(&$entity, $field_name, array &$data, $index) {
    $value = $this->getRandomSentence(40, 320);
    $value = ucfirst(strtolower($value));
    $entity->set($field_name, $value);
  }

  /**
   * Generates a random sentence string.
   *
   * @param object $entity
   *   The node to set the string value in.
   * @param object $field_info
   *   Field Info object of the field to set.
   * @param array $data
   *   The uploaded test data array.
   * @param int $index
   *   Optional index in the test data array.
   */
  public function setParagraphString(&$entity, $field_info, array &$data, $index) {
    $field_name = $field_info->getName();
    $settings = $field_info->getSettings();
    $value = $this->getRandomSentence(1, $settings['max_length']);
    $entity->set($field_name, $value);
  }

  /**
   * Returns the nid of a published thank you page.
   *
   * @param string $thank_you_page
   *   * for random page or string to selected by Thank You Page title.
   * @param string $langcode
   *   The langcode to match the Tahnk You page to, defaults to en.
   *
   * @return int
   *   Node ID of selected/random thank you page.
   */
  public function getThankYouPage($thank_you_page, $langcode = 'en') {
    $nid = FALSE;

    if ($thank_you_page == '*') {
      $q = "
        SELECT nid FROM {node_field_data} n
        WHERE n.type = 'thankyou_pages'  AND n.langcode = :langcode  AND n.status = 1
        ORDER BY RAND() LIMIT 1";
      $result = $this->database->query($q, ['langcode' => $langcode])->fetch();
      if ($result) {
        $nid = $result->nid;
      }

    }
    elseif (intval($thank_you_page) > 0) {
      $q = "
        SELECT nid FROM {node_field_data} n
        LEFT JOIN {node__field_shared_mktg_support_ticket} t ON n.nid = t.entity_id AND n.langcode = t.langcode
        WHERE n.type = 'thankyou_pages' AND n.langcode = :langcode AND n.status = 1
          AND t.field_shared_mktg_support_ticket_value = :support_ticket
        ORDER BY RAND() LIMIT 1";

      $result = $this->database->query($q,
      [
        'langcode' => $langcode,
        'support_ticket' => $thank_you_page,
      ])->fetch();
      if ($result) {
        $nid = $result->nid;
      }

    }
    elseif (!empty($thank_you_page)) {
      $q = "
        SELECT nid FROM {node_field_data} n
        WHERE n.type = 'thankyou_pages'  AND n.langcode = :langcode
          AND n.status = 1 AND n.title LIKE :thank_you_page
        ORDER BY RAND() LIMIT 1";

      $result = $this->database->query($q,
      [
        'langcode' => $langcode,
        'thank_you_page' => $thank_you_page . '%',
      ])->fetch();
      if ($result) {
        $nid = $result->nid;
      }
      else {
        $values = [
          'title' => $thank_you_page,
          'type' => "thankyou_pages",
          'uid' => 1,
          'status' => 1,
          'promote' => 0,
          'created' => $this->time->getRequestTime(),
        ];

        $node = Node::create($values);
        if ($langcode <> 'en') {
          $node->addTranslation($langcode, $values);
        }
        $node->enforceIsNew();
        $node->save();
        $nid = $node->id();
      }
    }

    return $nid;

  }

  /**
   * Set field_shared_ref_description value of node with 160 char limit.
   *
   * @param object $entity
   *   Entity to set the value for.
   * @param array $data
   *   The uploaded test data array.
   * @param int $index
   *   Optional index in the test data array.
   */
  public function setNodeFieldSharedRefDescription(&$entity, array &$data, $index) {
    $text = $this->getRandomSentence(10, 160);

    $entity->set('field_shared_ref_description', $text);
  }

  /**
   * Set field_shared_content_owner value required.
   *
   * @param object $entity
   *   Entity to set the value for.
   * @param array $data
   *   The uploaded test data array.
   * @param int $index
   *   Optional index in the test data array.
   */
  public function setFieldSharedContentOwner(&$entity, array &$data, $index) {
    $termId = $this->getRandomTermId('content_owner') ?? 2381;
    $entity->set('field_shared_content_owner', $termId);
  }

  /**
   * Set field_shared_cou value required.
   *
   * @param object $entity
   *   Entity to set the value for.
   * @param array $data
   *   The uploaded test data array.
   * @param int $index
   *   Optional index in the test data array.
   */
  public function setFieldSharedCou(&$entity, array &$data, $index) {
    // Get the term ids for the content domain values.
    $termId = $this->getRandomTermId('customer_operating_unit') ?? 2106;
    $entity->set('field_shared_cou', $termId);
  }

  /**
   * Get a term id randomly selected.
   *
   * @param string $vid
   *   Entity to set the value for.
   *
   * @return int
   *   A random selected Taxonomy term id.
   */
  public function getRandomTermId(string $vid) {
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree($vid);
    $count = count($terms);
    if ($count > 0) {
      return $terms[random_int(0, $count - 1)]->tid;
    }
    return FALSE;
  }

  /**
   * Set pargraph field_image_gallery_images value required.
   *
   * @param object $entity
   *   Entity to set the value for.
   * @param array $data
   *   The uploaded test data array.
   * @param int $index
   *   Optional index in the test data array.
   */
  public function setParagraphFieldImageGalleryImages(&$entity, array &$data, $index) {
    $num = random_int(2, 4);
    $sql = "SELECT field_image_gallery_images_target_id AS target_id FROM {paragraph__field_image_gallery_images} GROUP BY target_id ORDER BY RAND() LIMIT $num";

    $result = $this->database->query($sql);
    foreach ($result as $record) {
      $targets[] = $record->target_id;
    }
    if (!empty($targets)) {
      $entity->set('field_image_gallery_images', $targets);
    }

  }

  /**
   * Set node__field_shared_header_media value required.
   *
   * @param object $entity
   *   Entity to set the value for.
   * @param array $data
   *   The uploaded test data array.
   * @param int $index
   *   Optional index in the test data array.
   */
  public function setNodeFieldSharedHeaderMedia(&$entity, array &$data, $index) {
    $sql = 'SELECT field_shared_header_media_target_id AS target_id FROM {node__field_shared_header_media} GROUP BY target_id ORDER BY RAND() LIMIT 1';

    $result = $this->database->query($sql)->fetch();
    if ($result) {
      $target_id = $result->target_id;
    }

    if (!empty($target_id)) {
      $entity->set('field_shared_header_media', $target_id);
    }

  }

  /**
   * Sets paragraph  field field_spotlight_content value.
   *
   * Entity reference is selected from View `published_nodes_for_paragraphs`.
   *
   * @param object $entity
   *   Entity to set the value for.
   * @param array $data
   *   The uploaded test data array.
   * @param int $index
   *   Optional index in the test data array.
   */
  public function setParagraphFieldSpotlightContent(&$entity, array &$data, $index) {
    $num = random_int(1, 10);
    if ($num >= 4) {
      return;
    }
    else {
      $this->setGeneralFieldReferenceValue($entity, 'field_spotlight_content', 'paragraph', 1);
    }
  }

  /**
   * Sets paragraph field field_rc_content value.
   *
   * @param object $entity
   *   Entity to set the value for.
   * @param array $data
   *   The uploaded test data array.
   * @param int $index
   *   Optional index in the test data array.
   */
  public function setParagraphFieldRcContent(&$entity, array &$data, $index) {
    // Get 2-5 reference nodes.
    $num = random_int(2, 5);
    $this->setGeneralFieldReferenceValue($entity, 'field_rc_content', 'paragraph', $num);
  }

  /**
   * Set a field of entity reference value.
   *
   * Entity reference is selected from View `published_nodes_for_paragraphs`.
   *
   * @param object $entity
   *   Entity to set the value for.
   * @param string $field_name
   *   The field name of entity.
   * @param string $type
   *   The entity type, node or paragraph.
   * @param int $limit
   *   The number of entity references.
   */
  public function setGeneralFieldReferenceValue(&$entity, $field_name, $type, $limit = 2) {
    $strTargetId = $field_name . "_target_id";
    $strTable = $type . "__" . $field_name;
    $targets = [];
    $sql = "
      SELECT $strTargetId AS target_id FROM {$strTable} f
      LEFT JOIN {node_field_data} n ON n.nid=f.$strTargetId AND n.langcode=f.langcode
      WHERE n.type in ('event', 'help', 'knowledge', 'news', 'offering', 'tool', 'resource')
        AND n.status=1
      GROUP BY target_id ORDER BY RAND() LIMIT $limit
      ";
    $result = $this->database->query($sql);
    foreach ($result as $record) {
      $targets[] = $record->target_id;
    }

    if (!empty($targets)) {
      $entity->set($field_name, $targets);
    }

  }

}

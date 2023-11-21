<?php

namespace Drupal\ul_datalayer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class ULDataLayerService - provides info to the DataLayer.
 */
class ULDataLayerService implements ULDataLayerServiceInterface {

  const EMPTY_PROPERTY = "(not available)";
  const DATE_FORMAT = "Y-m-d";
  const ARRAY_SEPERATOR = ",";

  /**
   * Drupal AccountProxyInterface object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Drupal Route Match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Drupal Entity Manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Datalayer properties related to User object.
   *
   * @var array
   */
  protected $userProperties = [
    'ul_user_type',
  ];

  /**
   * Datalayer properties related to Content Entity object.
   *
   * @var array
   */
  protected $entityProperties = [
    'ul_content_type',
    'ul_page_created',
    'ul_page_updated',
  ];

  /**
   * Datalayer properties related to Content Entity Fields.
   *
   * @var array
   */
  protected $fieldProperties = [
    'ul_industries' => [
      'fields' => ['field_shared_industry'],
    ],
    'ul_topic' => [
      'fields' => ['field_shared_topic'],
    ],
    'ul_business_needs' => [
      'fields' => ['field_shared_stakeholder_needs'],
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public function __construct(AccountProxyInterface $current_user, RouteMatchInterface $routeMatch, EntityTypeManagerInterface $entityTypeManager) {
    $this->currentUser = $current_user;
    $this->routeMatch = $routeMatch;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getDataLayer() {

    $datalayer = $this->getDefaultDataLayer();

    // Get datalayer data for user.
    if ($user_datalayer = $this->getUserDataLayer($this->currentUser)) {
      $datalayer = array_merge($datalayer, $user_datalayer);
    }

    // Get route entity.
    if ($current_entity = $this->getRouteEntity()) {
      // @todo Test that this variable is unused, and remove if so.
      // $ref = 0;
      // Get datalayer for entity.
      if ($entity_datalayer = $this->getEntityDataLayer($current_entity)) {
        $datalayer = array_merge($datalayer, $entity_datalayer);
      }
    }

    return $datalayer;
  }

  /**
   * {@inheritdoc}
   */
  public function getUserDataLayer(AccountProxyInterface $user) {
    $datalayer = [];

    // Loop through each user properties.
    foreach ($this->userProperties as $property) {
      $property_value = FALSE;
      switch ($property) {
        case 'ul_user_type':
          $property_value = $user->isAuthenticated() ? 'authenticated' : 'anonymous';
          break;
      }

      if ($property_value) {
        $datalayer[$property] = $property_value;
      }
    }

    return $datalayer;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityDataLayer(ContentEntityInterface $entity) {
    $datalayer = [];

    // Loop through entity properties.
    foreach ($this->entityProperties as $property) {
      $property_value = FALSE;

      switch ($property) {
        case 'ul_content_type':
          $property_value = $this->getContentType($entity);
          break;

        case 'ul_page_created':
          // Get the formatted created date.
          // Some content entities (like taxonomy terms) do not support
          // getCreatedTime().
          if (method_exists($entity, 'getCreatedTime')) {
            $created_date = $entity->getCreatedTime();
            $property_value = $this->formatDate($created_date);
          }
          break;

        case 'ul_page_updated':
          // Get the formatted update date.
          // Some content entities (like taxonomy terms) do not support
          // getChangedTime().
          if (method_exists($entity, 'getChangedTime')) {
            $change_date = $entity->getChangedTime();
            $property_value = $this->formatDate($change_date);
          }
          break;
      }

      if ($property_value) {
        $datalayer[$property] = $property_value;
      }
    }

    // Get field data.
    if ($field_datalayer = $this->getFieldDataLayer($entity)) {
      $datalayer = array_merge($datalayer, $field_datalayer);
    }

    return $datalayer;
  }

  /**
   * Retrieves field data for datalayer related to the passed entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   A Drupal content entity object.
   *
   * @return array
   *   Empty array or associative array of entity field data.
   */
  private function getFieldDataLayer(ContentEntityInterface $entity) {
    $datalayer = [];

    // Loop through each datalayer property set for fields.
    foreach ($this->fieldProperties as $property_name => $property) {

      $data = [];

      // Loop through each associated field for this property.
      foreach ($property['fields'] as $field_name) {

        // Check that entity object has this field and can get the object.
        if ($entity->hasField($field_name) && ($field = $entity->get($field_name))) {
          // Get the field type.
          // Call helper function to get actual field value.
          $field_data = $this->getFieldData($field);
          // Add to data array.
          if ($field_data) {
            // If field data is an array merge it's values into the data array.
            if (is_array($field_data)) {
              $data = array_merge($data, $field_data);
            }
            // Add field data to the data array.
            else {
              $data[] = $field_data;
            }
          }
        }
      }

      // Have field data?
      if (!empty($data)) {
        // Check if this field property has a length limit.
        if (isset($property['length'])) {
          // Trim the array of data to the limit.
          $this->trimArray($data, $property['length']);
        }
        // Add property data to datalayer array.
        $datalayer[$property_name] = implode(',', $data);
      }
    }

    return $datalayer;
  }

  /**
   * Retrieves value from an entity field.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $field
   *   Field object to retrieve value from.
   *
   * @return array|mixed
   *   String or array of values.
   */
  private function getFieldData(FieldItemListInterface $field) {
    switch ($field->getFieldDefinition()->getType()) {

      case 'entity_reference':
        $data = [];
        $referenced_entities = $field->referencedEntities();
        foreach ($referenced_entities as $entity) {
          $data[] = $entity->label();
        }
        break;

      default:
        $data = $field->getValue();
        break;
    }

    return $data;

  }

  /**
   * Helper function to format a date timestamp into a specific format.
   *
   * @param int $timestamp
   *   Timestamp integer.
   *
   * @return false|string
   *   PHP Date string.
   */
  private function formatDate($timestamp) {
    return date(self::DATE_FORMAT, $timestamp);
  }

  /**
   * Helper function to build the default datalayer array.
   *
   * @return array
   *   Datalayer array with empty values.
   */
  private function getDefaultDataLayer() {
    // Return a data layer of empty items.
    $datalayer = [];

    // Add all user properties.
    foreach ($this->userProperties as $property) {
      $datalayer[$property] = self::EMPTY_PROPERTY;
    }

    // Add all entity properties.
    foreach ($this->entityProperties as $property) {
      $datalayer[$property] = self::EMPTY_PROPERTY;
    }

    // Add all field properties.
    foreach ($this->fieldProperties as $property => $data) {
      $datalayer[$property] = self::EMPTY_PROPERTY;
    }

    return $datalayer;
  }

  /**
   * Helper function to get the Content Type name for an entity object.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Drupal Content Entity object.
   *
   * @return string
   *   String value of either the bundle or the entity type name.
   */
  private function getContentType(ContentEntityInterface $entity) {

    // Get entity type and bundle.
    $type = $entity->getEntityTypeId();
    $entity_info = $this->entityTypeManager->getDefinition($type);
    $entity_keys = $entity_info->getKeys();
    $bundle = FALSE;

    // Check to see if entity has a bundle.
    if (isset($entity->{$entity_keys['bundle']})) {
      // Get the bundle name.
      $bundle = $entity->{$entity_keys['bundle']}->getString();
    }

    // Return either the bundle or the entity type.
    return $bundle ?: $type;
  }

  /**
   * Helper function to get entity from current route.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface|mixed|null
   *   Return content entity object or NULL.
   */
  private function getRouteEntity() {
    $entity = NULL;
    // Figure out if a content entity is being viewed.
    foreach ($this->routeMatch->getParameters() as $parameter) {
      // Check if parameter is an content entity object.
      if ($parameter instanceof ContentEntityInterface) {
        $entity = $parameter;
        break;
      }
    }

    return $entity;
  }

  /**
   * Helper function to trim objects in array to a specific length.
   *
   * @param array $array
   *   Array of value to trim, passed by reference.
   * @param int $length
   *   Integer value for maximum string length.
   * @param string $seperator
   *   Character to use to seperate imploded array items.
   */
  private function trimArray(array &$array, $length, $seperator = self::ARRAY_SEPERATOR) {
    // Check if the flattened array is longer then the expected length.
    while (!empty($array) && (strlen(implode($seperator, $array)) > $length)) {
      // Remove the last item and try again.
      array_pop($array);
    }
  }

}

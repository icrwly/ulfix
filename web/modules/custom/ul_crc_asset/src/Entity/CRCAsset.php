<?php

namespace Drupal\ul_crc_asset\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the CRC Asset entity.
 *
 * @ingroup ul_crc_asset
 *
 * @ContentEntityType(
 *   id = "crc_asset",
 *   label = @Translation("CRC Asset"),
 *   handlers = {
 *     "view_builder" = "Drupal\ul_crc_asset\CRCAssetViewBuilder",
 *     "access" = "Drupal\ul_crc_asset\CRCAssetAccessControlHandler",
 *     "translation" = "Drupal\ul_marketo\CRCAssetTranslationHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\ul_crc_asset\CRCAssetHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "crc_asset",
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "uid" = "uid",
 *     "crc_id" = "crc_id",
 *     "crc_language" = "crc_language",
 *   },
 * )
 */
class CRCAsset extends ContentEntityBase implements CRCAssetInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function postLoad(EntityStorageInterface $storage, array &$entities) {
    foreach ($entities as $id => $entity) {
      if (empty($entity->ul_crc_asset)) {
        $crc_results = \Drupal::service('ul_crc')->getAsset($entity->getCrcId(), $entity->getLangcode());
        if (!empty($crc_results['data'])) {
          foreach ($crc_results['data'] as $key => $data) {
            $entities[$id]->ul_crc_asset[$key] = $data;
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * Return CRC Service data about this asset.
   *
   * @param string $field
   *   The field name from CRC.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface|mixed
   *   Returns the value from the CRC service.
   */
  public function getCrcData($field) {

    // ul_crc_asset property is set as part of self::postLoad().
    if (!empty($this->ul_crc_asset[$field])) {
      // Make sure that the label and name from CRC are in sync.
      if ($field == 'name') {
        if ($this->ul_crc_asset[$field] != $this->label()) {
          $this->setName($this->ul_crc_asset[$field]);
          $this->save();
        }
      }

      return $this->ul_crc_asset[$field];
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getLangcode() {
    return $this->get('langcode')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPermanent() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPermanent() {
    $this->set('status', TRUE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setTemporary() {
    $this->set('status', FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCrcId() {
    return $this->get('crc_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCrcId($crc_id) {
    $this->set('crc_id', $crc_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCrcLanguage() {
    return $this->get('crc_language')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCrcLanguage($crc_language) {
    $this->set('crc_language', $crc_language);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['id']->setLabel(t('Entity ID'))
      ->setDescription(t('The Entity ID.'));

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('Name of the asset with no path components.'));

    $fields['uuid']->setDescription(t('The asset UUID.'));

    $fields['langcode']->setLabel(t('Language code'))
      ->setDescription(t('The asset language code.'));

    // Keep this?
    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User ID'))
      ->setDescription(t('The user ID of the asset.'))
      ->setSetting('target_type', 'user');

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDescription(t('The status of the asset.'))
      ->setDefaultValue(FALSE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The timestamp that the asset was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The timestamp that the asset was last changed.'));

    $fields['crc_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('CRC ID'))
      ->setDescription(t('CRC Asset ID.'))
      ->setReadOnly(TRUE);

    $fields['crc_language'] = BaseFieldDefinition::create('string')
      ->setLabel(t('CRC Language'))
      ->setDescription(t('The Lanuage of a CRC Asset.'));

    return $fields;
  }

  /**
   * Return list of entities that are referencing this asset.
   *
   * @return array
   *   An array of entities that are using this asset.
   */
  public function getReferencingEntities() {
    // @todo Replace this with something more efficient.
    // This is a very expensive function but was necessary to save dev time. We
    // should create a service that tracks the usage of this asset similar to
    // this file entity.
    // Find all fields that are referencing this entity type.
    $properties = [
      'type' => 'crc_asset_item',
    ];
    $fieldConfig = \Drupal::entityTypeManager()->getStorage('field_storage_config')->loadByProperties($properties);

    // Get the different entity types with a reference to this entity
    // (node, taxonomy term etc.)
    $types = [];
    foreach ($fieldConfig as $config) {
      $type = $config->get('entity_type');
      if (!in_array($type, $types)) {
        $types[] = $type;
      }
    }

    // Query the content that is storing a reference to this entity.
    // Separate it out by entity type.
    $results = [];
    foreach ($types as $type) {
      $query = \Drupal::entityQuery($type);
      $group = $query->orConditionGroup();
      foreach ($fieldConfig as $config) {
        if ($config->get('entity_type') == $type) {
          $group->condition($config->getName() . '.entity:crc_asset.id', $this->id());
        }
      }
      $query->condition($group);
      $query->accessCheck(FALSE);
      $results[$type] = $query->execute();
    }

    // Return an array of entities.
    $entities = [];
    foreach ($results as $entity_type => $entity_ids) {
      foreach ($entity_ids as $entity_id) {
        $entity = \Drupal::entityTypeManager()
          ->getStorage($entity_type)
          ->load($entity_id);
        $entities[] = $entity;
      }
    }
    return $entities;
  }

}

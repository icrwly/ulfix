<?php

namespace Drupal\ul_legal_hold\Entity;

use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\ul_legal_hold\LegalHoldInterface;
use Drupal\node\NodeInterface;
use Drupal\node\Entity\Node;
use Drupal\user\UserInterface;

/**
 * Defines the legal hold entity class.
 *
 * @ContentEntityType(
 *   id = "ul_legal_hold",
 *   label = @Translation("Legal Holds"),
 *   label_collection = @Translation("Legal Holds"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\ul_legal_hold\LegalHoldListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\ul_legal_hold\LegalHoldAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\ul_legal_hold\Form\LegalHoldForm",
 *       "edit" = "Drupal\ul_legal_hold\Form\LegalHoldForm",
 *       "delete" = "Drupal\ul_legal_hold\Form\LegalHoldDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "ul_legal_hold",
 *   data_table = "ul_legal_hold_field_data",
 *   revision_table = "ul_legal_hold_revision",
 *   revision_data_table = "ul_legal_hold_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer legal hold",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log"
 *   },
 *   links = {
 *     "add-form" = "/node/{node}/legal-holds/add",
 *     "canonical" = "/node/{node}/legal-holds/{ul_legal_hold}",
 *     "edit-form" = "/node/{node}/legal-holds/{ul_legal_hold}/edit",
 *     "delete-form" = "/node/{node}/legal-holds/{ul_legal_hold}/delete",
 *     "collection" = "/admin/content/legal-hold",
 *     "version-history" = "/node/{node}/legal-holds/{ul_legal_hold}/revisions",
 *     "revision" = "/node/{node}/legal-holds/{ul_legal_hold}/revisions/",
 *   },
 * )
 */
class LegalHold extends RevisionableContentEntityBase implements LegalHoldInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   *
   * When a new legal hold entity is created, set the uid entity reference to
   * the current user as the creator of the entity.
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += ['uid' => \Drupal::currentUser()->id()];
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    $this->set('title', $title);
    return $this;
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
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setHeldContent(NodeInterface $node) {
    $this->set('held_content', $node);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    $raw = $this->get('description')->view();
    return $raw[0];
  }

  /**
   * {@inheritdoc}
   */
  public function getHeldContent() {
    $content = NULL;
    $held = $this->get('held_content');
    foreach ($held as $item) {
      $content = $item->getValue();
    }
    return Node::load($content['target_id']);
  }

  /**
   * {@inheritdoc}
   */
  public function getHeldContentId() {
    $content = NULL;
    $held = $this->get('held_content');
    foreach ($held as $item) {
      $content = $item->getValue();
    }
    return $content['target_id'];
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
  public function setHeldRevisions(array $targets) {
    $this->set('held_revisions', $targets);
  }

  /**
   * {@inheritdoc}
   */
  public function getHeldRevisions() {
    $revs = [];
    foreach ($this->get('held_revisions') as $rev) {
      $id = $rev->getValue()['target_revision_id'];
      $revs[$id] = $id;
    }
    return $revs;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the legal hold entity.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Description'))
      ->setDescription(t('A description of the legal hold.'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 10,
      ])
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setDescription(t('The user ID of the legal hold author.'))
      ->setSetting('target_type', 'user')
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['held_content'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Held Content'))
      ->setDescription(t('The node ID of the legal hold content.'))
      ->setSetting('target_type', 'node')
      ->setSetting('handler', 'default')
      ->setRequired(TRUE)
      ->setCardinality(1);

    $fields['held_revisions'] = BaseFieldDefinition::create('entity_reference_revisions')
      ->setLabel(t('Held Revisions'))
      ->setDescription(t('The content on Legal Hold'))
      ->setSetting('target_type', 'node')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'ul_legal_hold_revision_field_formatter',
        'weight' => 15,
      ])
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the legal hold was created.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 20,
      ]);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the legal hold was last edited.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    // Create the URL parameters so the routing system works.
    $held_content = $this->getHeldContent();
    $uri_route_parameters = parent::urlRouteParameters($rel);
    $uri_route_parameters['node'] = $held_content->id();
    return $uri_route_parameters;
  }

}

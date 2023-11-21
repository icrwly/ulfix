<?php

namespace Drupal\ul_marketo\Entity;

use Drupal\Core\Url;
use Drupal\user\UserInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\TypedData\TranslatableInterface;
use Drupal\entity_reference_revisions\EntityNeedsSaveTrait;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Defines the MarketoForm entity.
 *
 * @ingroup ul_marketo
 *
 * @ContentEntityType(
 *   id = "marketo_form",
 *   label = @Translation("Marketo Form"),
 *   bundle_label = @Translation("Marketo Form type"),
 *   handlers = {
 *     "storage" = "Drupal\ul_marketo\MarketoFormStorage",
 *     "view_builder" = "Drupal\ul_marketo\MarketoFormViewBuilder",
 *     "list_builder" = "Drupal\ul_marketo\MarketoFormListBuilder",
 *     "views_data" = "Drupal\ul_marketo\Entity\MarketoFormViewsData",
 *     "translation" = "Drupal\ul_marketo\MarketoFormTranslationHandler",
 *     "access" = "Drupal\ul_marketo\MarketoFormAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\ul_marketo\MarketoFormHtmlRouteProvider",
 *     },
 *     "form" = {
 *       "default" = "Drupal\ul_marketo\Form\MarketoFormEntityForm",
 *       "edit" = "Drupal\ul_marketo\Form\MarketoFormEntityForm",
 *     },
 *   },
 *   base_table = "marketo_form",
 *   data_table = "marketo_form_field_data",
 *   revision_table = "marketo_form_revision",
 *   revision_data_table = "marketo_form_field_revision",
 *   translatable = TRUE,
 *   entity_revision_parent_type_field = "parent_type",
 *   entity_revision_parent_id_field = "parent_id",
 *   entity_revision_parent_field_name_field = "parent_field_name",
 *   admin_permission = "administer marketo_form entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *     "label" = "name",
 *   },
 *   links = {
 *     "canonical" = "/admin/content/marketo_form/{marketo_form}",
 *     "collection" = "/admin/content/marketo_form/list",
 *   },
 *   bundle_entity_type = "marketo_form_type",
 *   field_ui_base_route = "entity.marketo_form_type.edit_form",
 *   common_reference_revisions_target = TRUE,
 *   content_translation_ui_skip = TRUE,
 *   render_cache = FALSE,
 *   default_reference_revision_settings = {
 *     "field_storage_config" = {
 *       "cardinality" = -1,
 *       "settings" = {
 *         "target_type" = "marketo_form"
 *       }
 *     },
 *     "field_config" = {
 *       "settings" = {
 *         "handler" = "default:marketo_form"
 *       }
 *     },
 *     "entity_form_display" = {
 *       "type" = "marketo_form_widget"
 *     },
 *     "entity_view_display" = {
 *       "type" = "entity_reference_revisions_entity_view"
 *     }
 *   },
 * )
 */
class MarketoForm extends ContentEntityBase implements MarketoFormInterface {

  use EntityChangedTrait;
  use EntityNeedsSaveTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getPageUrl() {
    $node = \Drupal::service('current_route_match')->getParameter('node');
    // If ID is set then load the route to that specific marketo form.
    if ($node instanceof NodeInterface) {
      try {
        return Url::fromRoute('ul_marketo.' . $this->bundle() . '.entity', [
          'entity' => $node->id(),
          'identifier' => $node->uuid(),
        ])->toString();
      }
      catch (RouteNotFoundException $exception) {

      }
    }
    // Otherwise return the route to the default page with no marketo form
    // argument.
    else {
      try {
        return Url::fromRoute('ul_marketo.' . $this->bundle() . '.default')->toString();
      }
      catch (RouteNotFoundException $exception) {

      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getSuccessUrl() {
    try {
      return Url::fromRoute('ul_marketo.' . $this->bundle() . '.success')
        ->toString();
    }
    catch (RouteNotFoundException $exception) {

    }
  }

  /**
   * Get the marketo Form ID.
   */
  public function getMarketoFormId($filters = ['env' => 'prod'], $override_form_type = FALSE) {

    // If argument to override the form type:
    if ($override_form_type) {
      $form_type = $override_form_type;
    }
    // Else, use this bundle / form type:
    else {
      $form_type = $this->bundle();
    }

    // Load the Marketo form type data.
    // @TODO: Make a more centalized ways to do this.
    /** @var \Drupal\ul_marketo\Entity\MarketoFormType $marketoFormType */
    $marketoFormType = \Drupal::entityTypeManager()->getStorage('marketo_form_type')->load($form_type);
    // Use the form type to get the form IDs.
    $form_ids = $marketoFormType->get('marketo_form_id');
    // Create an array of clean data to return.
    $forms_return = [];
    if (is_array($form_ids)) {
      foreach ($form_ids as $key => $val) {
        if (isset($form_ids[$key]['filters']['env'])) {
          $forms_return[$form_ids[$key]['filters']['env']] = $form_ids[$key]['value'];
        }
      }
      if (isset($filters['env'])) {
        if (array_key_exists($filters['env'], $forms_return)) {
          return $forms_return[$filters['env']];
        }
        else {
          // Return "prod" form by default.
          return $forms_return['prod'];
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getParentEntity() {
    // Check if there's a parent type/entity.
    if (!isset($this->get('parent_type')->value) || !isset($this->get('parent_id')->value)) {
      return NULL;
    }

    $parent = \Drupal::entityTypeManager()->getStorage($this->get('parent_type')->value)->load($this->get('parent_id')->value);

    // Return current translation of parent entity, if it exists.
    if ($parent != NULL && ($parent instanceof TranslatableInterface) && $parent->hasTranslation($this->language()->getId())) {
      return $parent->getTranslation($this->language()->getId());
    }

    return $parent;
  }

  /**
   * Recursive function for fetching the host node.
   *
   * @return mixed
   *   Either the parent node or FALSE.
   */
  public function getParentNode($entity) {
    // @todo warning! This should be recursive!
    // This fetches the host node and assumes two levels
    // (Node > Paragraph > Marketo Form). It was written this way for the sake
    // of time. However, this needs to be rewritten to be recursive.
    $parent = $entity->getParentEntity();
    if (!empty($parent) && $parent instanceof Node) {
      return $parent;
    }
    elseif (!empty($parent) && $parent instanceof EntityInterface) {
      return $this->getParentNode($parent);
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setParentEntity(ContentEntityInterface $parent, $parent_field_name) {
    $this->set('parent_type', $parent->getEntityTypeId());
    $this->set('parent_id', $parent->id());
    $this->set('parent_field_name', $parent_field_name);
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
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function getMarketoFormType() {
    return $this->type->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published = NULL) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the MarketoForm entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Form Name'))
      ->setDescription(t('The name of this instance of the form.'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Revision user ID'))
      ->setDescription(t('The user ID of the author of the current revision.'))
      ->setSetting('target_type', 'user')
      ->setRevisionable(TRUE);

    $fields['parent_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Parent ID'))
      ->setDescription(t('The ID of the parent entity of which this entity is referenced.'))
      ->setSetting('is_ascii', TRUE);

    $fields['parent_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Parent type'))
      ->setDescription(t('The entity parent type to which this entity is referenced.'))
      ->setSetting('is_ascii', TRUE)
      ->setSetting('max_length', EntityTypeInterface::ID_MAX_LENGTH);

    $fields['parent_field_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Parent field name'))
      ->setDescription(t('The entity parent field name to which this entity is referenced.'))
      ->setSetting('is_ascii', TRUE)
      ->setSetting('max_length', FieldStorageConfig::NAME_MAX_LENGTH);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Published'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
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
   * Set Default Vaules of title, button_text, description.
   *
   * @return $this
   *   This object.
   */
  public function setDefaultValues() {
    $this->set('field_shared_custom_title', $this->get('field_shared_custom_title')->getFieldDefinition()->getDefaultValueLiteral());
    $this->set('field_shared_button_text', $this->get('field_shared_button_text')->getFieldDefinition()->getDefaultValueLiteral());
    $this->set('field_shared_cta_button_text', $this->get('field_shared_cta_button_text')->getFieldDefinition()->getDefaultValueLiteral());
    $this->set('field_shared_form_description', $this->get('field_shared_form_description')->getFieldDefinition()->getDefaultValueLiteral());
    return $this;
  }

}

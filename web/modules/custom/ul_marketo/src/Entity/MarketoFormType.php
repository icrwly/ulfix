<?php

namespace Drupal\ul_marketo\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Marketo Form type entity.
 *
 * @ConfigEntityType(
 *   id = "marketo_form_type",
 *   label = @Translation("Marketo Form type"),
 *   handlers = {
 *     "access" = "Drupal\ul_marketo\MarketoFormTypeAccessControlHandler",
 *     "list_builder" = "Drupal\ul_marketo\MarketoFormTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\ul_marketo\Form\MarketoFormTypeForm",
 *       "add" = "Drupal\ul_marketo\Form\MarketoFormTypeForm",
 *       "edit" = "Drupal\ul_marketo\Form\MarketoFormTypeTypeForm",
 *       "delete" = "Drupal\ul_marketo\Form\MarketoFormTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\ul_marketo\MarketoFormTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "marketo_form_type",
 *   admin_permission = "administer marketo_form_type entities",
 *   bundle_of = "marketo_form",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/marketo-form-type/add",
 *     "edit-form" = "/admin/structure/marketo-form-type/{marketo_form_type}",
 *     "delete-form" = "/admin/structure/marketo-form-type/{marketo_form_type}/delete",
 *     "collection" = "/admin/structure/marketo-form-type"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "marketo_form_id",
 *     "settings",
 *   }
 * )
 */
class MarketoFormType extends ConfigEntityBundleBase implements MarketoFormTypeInterface {

  /**
   * The Marketo form type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Marketo entity type label.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of this Marketo form type.
   *
   * @var string
   */
  protected $description;

  /**
   * Array of entity settings.
   *
   * @var array
   */
  protected $settings;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->description = $description;
  }

  /**
   * {@inheritdoc}
   */
  public function getSetting($key) {
    if (isset($this->settings[$key])) {
      return $this->settings[$key];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setSetting($key, $value) {
    $this->settings[$key] = $value;
  }

}

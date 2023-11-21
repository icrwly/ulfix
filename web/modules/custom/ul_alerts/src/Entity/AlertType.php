<?php

namespace Drupal\ul_alerts\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Alert type entity.
 *
 * @ConfigEntityType(
 *   id = "ul_alert_type",
 *   label = @Translation("Alert type"),
 *   handlers = {
 *     "list_builder" = "Drupal\ul_alerts\AlertTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\ul_alerts\Form\AlertTypeForm",
 *       "edit" = "Drupal\ul_alerts\Form\AlertTypeForm",
 *       "delete" = "Drupal\ul_alerts\Form\AlertTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\ul_alerts\AlertTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "ul_alert_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "ul_alert",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/ul_alert_type/{ul_alert_type}",
 *     "add-form" = "/admin/structure/ul_alert_type/add",
 *     "edit-form" = "/admin/structure/ul_alert_type/{ul_alert_type}",
 *     "delete-form" = "/admin/structure/ul_alert_type/{ul_alert_type}/delete",
 *     "collection" = "/admin/structure/ul_alert_type"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *   }
 * )
 */
class AlertType extends ConfigEntityBundleBase implements AlertTypeInterface {

  /**
   * The Alert type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Alert type label.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of this alert type.
   *
   * @var string
   */
  protected $description;

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

}

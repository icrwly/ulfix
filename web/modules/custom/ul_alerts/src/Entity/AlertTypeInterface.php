<?php

namespace Drupal\ul_alerts\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityDescriptionInterface;

/**
 * Provides an interface for defining Alert type entities.
 */
interface AlertTypeInterface extends ConfigEntityInterface, EntityDescriptionInterface {

  /**
   * Gets the description.
   *
   * @return string
   *   The description of this alert type.
   */
  public function getDescription();

  /**
   * Sets the description.
   *
   * @param string $description
   *   The description of this alert type.
   */
  public function setDescription($description);

}

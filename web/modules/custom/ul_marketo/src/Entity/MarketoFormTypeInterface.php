<?php

namespace Drupal\ul_marketo\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityDescriptionInterface;

/**
 * Provides an interface for defining Marketo type entities.
 */
interface MarketoFormTypeInterface extends ConfigEntityInterface, EntityDescriptionInterface {

  /**
   * Gets the description.
   *
   * @return string
   *   The description of this marketo form type.
   */
  public function getDescription();

  /**
   * Sets the description.
   *
   * @param string $description
   *   The description of this marketo form type.
   */
  public function setDescription($description);

  /**
   * Get individual setting.
   *
   * @param string $key
   *   The setting key to look for.
   */
  public function getSetting($key);

  /**
   * Set an individual setting.
   *
   * @param string $key
   *   The setting key to set.
   * @param mixed $value
   *   The new value to set.
   */
  public function setSetting($key, $value);

}

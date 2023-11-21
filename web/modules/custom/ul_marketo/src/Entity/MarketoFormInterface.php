<?php

namespace Drupal\ul_marketo\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\entity_reference_revisions\EntityNeedsSaveInterface;
use Drupal\Core\Entity\EntityPublishedInterface;

/**
 * Provides an interface for defining Marketo entities.
 *
 * @ingroup ul_marketo
 */
interface MarketoFormInterface extends ContentEntityInterface, EntityOwnerInterface, EntityNeedsSaveInterface, EntityPublishedInterface {

  /**
   * Gets the Marketo entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Marketo entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Marketo entity creation timestamp.
   *
   * @param int $timestamp
   *   The Marketo entity creation timestamp.
   *
   * @return \Drupal\ul_marketo\Entity\MarketoFormInterface
   *   The called Marketo entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Marketo entity published status indicator.
   *
   * Unpublished Marketo entities are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Marketo entity is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Marketo entity.
   *
   * @param bool $published
   *   TRUE to set this Marketo entity to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\ul_marketo\Entity\MarketoFormInterface
   *   The called Marketo entity.
   */
  public function setPublished($published = NULL);

}

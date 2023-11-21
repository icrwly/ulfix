<?php

namespace Drupal\ul_alerts\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Alert entities.
 *
 * @ingroup ul_alerts
 */
interface AlertInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the Alert name.
   *
   * @return string
   *   Name of the Alert.
   */
  public function getName();

  /**
   * Sets the Alert name.
   *
   * @param string $name
   *   The Alert name.
   *
   * @return \Drupal\ul_alerts\Entity\AlertInterface
   *   The called Alert entity.
   */
  public function setName($name);

  /**
   * Gets the Alert creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Alert.
   */
  public function getCreatedTime();

  /**
   * Sets the Alert creation timestamp.
   *
   * @param int $timestamp
   *   The Alert creation timestamp.
   *
   * @return \Drupal\ul_alerts\Entity\AlertInterface
   *   The called Alert entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Alert published status indicator.
   *
   * Unpublished Alert are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Alert is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Alert.
   *
   * @param bool $published
   *   TRUE to set this Alert to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\ul_alerts\Entity\AlertInterface
   *   The called Alert entity.
   */
  public function setPublished($published);

  /**
   * Gets the weight of this alert.
   *
   * @return int
   *   The weight of the alert.
   */
  public function getWeight();

  /**
   * Gets the weight of this alert.
   *
   * @param int $weight
   *   The alert's weight.
   *
   * @return $this
   */
  public function setWeight($weight);

  /**
   * Gets the dismiss timer (in seconds).
   *
   * @return int
   *   The dismiss timer of the alert.
   */
  public function getDismissTimer();

  /**
   * Gets the dismiss timer.
   *
   * @param int $seconds
   *   The amount of time before the alert is dismissed.
   *
   * @return $this
   */
  public function setDismissTimer($seconds);

  /**
   * Gets the Alert revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Alert revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\ul_alerts\Entity\AlertInterface
   *   The called Alert entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Alert revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Alert revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\ul_alerts\Entity\AlertInterface
   *   The called Alert entity.
   */
  public function setRevisionUserId($uid);

}

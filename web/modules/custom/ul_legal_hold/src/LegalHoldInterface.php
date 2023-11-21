<?php

namespace Drupal\ul_legal_hold;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\node\NodeInterface;

/**
 * Provides an interface defining a legal hold entity type.
 */
interface LegalHoldInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Gets the legal hold title.
   *
   * @return string
   *   Title of the legal hold.
   */
  public function getTitle();

  /**
   * Sets the legal hold title.
   *
   * @param string $title
   *   The legal hold title.
   *
   * @return \Drupal\ul_legal_hold\LegalHoldInterface
   *   The called legal hold entity.
   */
  public function setTitle($title);

  /**
   * Gets the legal hold creation timestamp.
   *
   * @return int
   *   Creation timestamp of the legal hold.
   */
  public function getCreatedTime();

  /**
   * Sets the legal hold creation timestamp.
   *
   * @param int $timestamp
   *   The legal hold creation timestamp.
   *
   * @return \Drupal\ul_legal_hold\LegalHoldInterface
   *   The called legal hold entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the legal hold parent content.
   *
   * @return \Drupal\node\Entity\NodeInterface
   *   The legal hold creation held content.
   */
  public function getHeldContent();

  /**
   * Sets the legal hold held content node.
   *
   * @param \Drupal\node\Entity\NodeInterface $heldContent
   *   The legal hold creation held content.
   *
   * @return $this
   */
  public function setHeldContent(NodeInterface $heldContent);

  /**
   * Get the description.
   *
   * @return $this
   */
  public function getDescription();

  /**
   * Gets the held revisions associated with the current Legal Hold.
   *
   * @param array $targets
   *   Array of held revision IDs.
   */
  public function setHeldRevisions(array $targets);

  /**
   * Gets the held revisions associated with the current Legal Hold.
   *
   * @return array
   *   Array of held revision IDs.
   */
  public function getHeldRevisions();

  /**
   * Gets the Legal Hold revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Legal Hold revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\ul_legal_hold\Entity\LegalHoldInterface
   *   The called Legal Hold entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Legal Hold revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Legal Hold revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\ul_legal_hold\Entity\LegalHoldInterface
   *   The called Legal Hold entity.
   */
  public function setRevisionUserId($uid);

}

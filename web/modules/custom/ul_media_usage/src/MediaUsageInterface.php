<?php

namespace Drupal\ul_media_usage;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\media\MediaInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Entity usage interface.
 */
interface MediaUsageInterface extends ContainerInjectionInterface {

  /**
   * Register or update a usage record.
   *
   * If called with $count >= 1, the record matching the other parameters will
   * be updated (or created if it doesn't exist). If called with $count <= 0,
   * the record will be deleted.
   *
   * Note that this method will honor the settings defined on the configuration
   * page, hence potentially ignoring the register if the settings for the
   * called combination are to not track this usage. Also, the hook
   * hook_ul_media_usage_block_tracking() will be invoked, so other modules will
   * have an opportunity to block this record before it is written to DB.
   *
   * @param int|string $target_id
   *   The target entity ID.
   * @param string $target_type
   *   The target entity type.
   * @param int|string $source_id
   *   The source entity ID.
   * @param string $source_type
   *   The source entity type.
   * @param string $source_langcode
   *   The source entity language code.
   * @param string $source_vid
   *   The source entity revision ID.
   * @param string $method
   *   The method used to relate source entity with the target entity. Normally
   *   the plugin id.
   * @param string $field_name
   *   The name of the field in the source entity using the target entity.
   * @param int $count
   *   (optional) The number of references to add to the object. Defaults to 1.
   */
  public function registerUsage($target_id, $target_type, $source_id, $source_type, $source_langcode, $source_vid, $method, $field_name, $count = 1);

  /**
   * Remove all records of a given target entity type.
   *
   * @param string $target_type
   *   The target entity type.
   */
  public function bulkDeleteTargets($target_type);

  /**
   * Remove all records of a given source entity type.
   *
   * @param string $source_type
   *   The source entity type.
   */
  public function bulkDeleteSources($source_type);

  /**
   * Delete all records for a given field_name + source_type.
   *
   * @param string $source_type
   *   The source entity type.
   * @param string $field_name
   *   The name of the field in the source entity using the
   *   target entity.
   */
  public function deleteByField($source_type, $field_name);

  /**
   * Delete all records for a given source entity.
   *
   * @param int|string $source_id
   *   The source entity ID.
   * @param string $source_type
   *   The source entity type.
   * @param string $source_langcode
   *   (optional) The source entity language code. Defaults to NULL.
   * @param string $source_vid
   *   (optional) The source entity revision ID. Defaults to NULL.
   */
  public function deleteBySourceEntity($source_id, $source_type, $source_langcode = NULL, $source_vid = NULL);

  /**
   * Delete all records for a given target entity.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The Media entity.
   */
  public function deleteByTargetEntity(MediaInterface $media);

  /**
   * Delete all records for a given target entity.
   *
   * @param array|mix $media_usage
   *   The array contains target entity data (id, type, etc.).
   */
  public function deleteByTargetEntityUnused(&$media_usage);

  /**
   * Delete all records for a given source entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The EntityInterface entity.
   */
  public function deleteBySourceRevision(EntityInterface $entity);

  /**
   * Select all records of entity_usage table.
   */
  public function listAllEntityUsage();

  /**
   * Save all records (media, entity_usage) for a given target entity.
   *
   * @param string $mid
   *   The Entity Media ID.
   * @param array|mix $entity_usage
   *   The array of date from entity_usage table.
   */
  public function saveMediaUsageData($mid, &$entity_usage);

  /**
   * Get data from the entity_usage table.
   *
   * @param string $target_id
   *   The Entity Media ID.
   */
  public function getEntityUsageData($target_id);

}

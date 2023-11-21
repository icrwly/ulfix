<?php

namespace Drupal\ul_media_usage;

use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\media\MediaInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines the entity usage base class.
 */
class MediaUsage implements MediaUsageInterface {

  /**
   * The database connection used to store entity usage information.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The name of DB table ul_media_usage.
   *
   * @var string
   */
  protected $tableName;

  /**
   * The ModuleHandler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The name of DB table entity_usage.
   *
   * @var string
   */
  protected $entityUsageTable;

  /**
   * The node storage controller.
   *
   * @var \\Drupal\node\NodeInterface
   */
  protected $nodeStorage;

  /**
   * The media storage.
   *
   * @var \Drupal\media\MediaInterface
   */
  protected $mediaStorage;

  /**
   * The storage handler class for files.
   *
   * @var \Drupal\file\FileInterface
   */
  protected $fileStorage;

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The service request_stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The string of host.
   *
   * @var string
   */
  protected $host;

  /**
   * The EntityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Construct the MediaUsage object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection which will be used to store the entity usage
   *   information.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The ModuleHandler service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language_manager service.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The path alias manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request_stack service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param string $table
   *   (optional) The table to store the entity usage info. Defaults to
   *   'ul_media_usage'.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Connection $connection, ModuleHandlerInterface $module_handler, LanguageManagerInterface $language_manager, AliasManagerInterface $alias_manager, RequestStack $request_stack, LoggerChannelFactoryInterface $logger_factory, $table = 'ul_media_usage') {
    $this->entityTypeManager = $entity_type_manager;
    $this->connection = $connection;
    $this->tableName = $table;
    $this->entityUsageTable = 'entity_usage';
    $this->moduleHandler = $module_handler;
    $this->languageManager = $language_manager;
    // EntityStorage of node, media and files.
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->mediaStorage = $entity_type_manager->getStorage('media');
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->aliasManager = $alias_manager;
    $this->requestStack = $request_stack;
    $this->host = $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost();
    $this->loggerFactory = $logger_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('database'),
      $container->get('module_handler'),
      $container->get('language_manager'),
      $container->get('path_alias.manager'),
      $container->get('request_stack'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function registerUsage($target_id, $target_type, $source_id, $source_type, $source_langcode, $source_vid, $method, $field_name, $count = 1) {
    // Entities can have string IDs. We support that by using different columns
    // on each case.
    $target_id_column = $this->isInt($target_id) ? 'target_id' : 'target_id';
    $source_id_column = $this->isInt($source_id) ? 'source_id' : 'source_id';

    // If $count is 0, we want to delete the record.
    if ($count <= 0) {
      $this->connection->delete($this->tableName)
        ->condition($target_id_column, $target_id)
        ->condition('target_type', $target_type)
        ->condition($source_id_column, $source_id)
        ->condition('source_type', $source_type)
        ->condition('source_langcode', $source_langcode)
        ->condition('source_vid', $source_vid)
        ->condition('method', $method)
        ->condition('field_name', $field_name)
        ->execute();
    }
    else {
      $this->connection->merge($this->tableName)
        ->keys([
          $target_id_column => $target_id,
          'target_type' => $target_type,
          $source_id_column => $source_id,
          'source_type' => $source_type,
          'source_langcode' => $source_langcode,
          'source_vid' => $source_vid ?: 0,
          'method' => $method,
          'field_name' => $field_name,
        ])
        ->execute();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function bulkDeleteTargets($target_type) {
    $query = $this->connection->delete($this->tableName)
      ->condition('target_type', $target_type);
    $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function bulkDeleteSources($source_type) {
    $query = $this->connection->delete($this->tableName)
      ->condition('source_type', $source_type);
    $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteByField($source_type, $field_name) {
    $query = $this->connection->delete($this->tableName)
      ->condition('source_type', $source_type)
      ->condition('field_name', $field_name);
    $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteBySourceEntity($source_id, $source_type, $source_langcode = NULL, $source_vid = NULL) {
    // Entities can have string IDs  by using different columns.
    $default_langcode = $this->languageManager->getDefaultLanguage()->getId();

    $query = $this->connection->delete($this->tableName)
      ->condition('source_id', $source_id);
    if ($source_langcode && $default_langcode !== $source_langcode) {
      $query->condition('source_langcode', $source_langcode);
    }
    $result = $query->execute();
    if ($result) {
      $this->loggerFactory->get('ul_media_usage')
        ->info("The Node $source_id and its media_usage data are deleted successfully.");
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteByTargetEntity(MediaInterface $media_entity) {
    $mid = $media_entity->id();
    $langcode = $media_entity->langcode->value;
    $query = $this->connection->delete($this->tableName)
      ->condition('target_id', $mid)
      ->condition('target_langcode', $langcode);
    $result = $query->execute();
    if ($result) {
      $this->loggerFactory->get('ul_media_usage')
        ->info("The Media $mid and its media_usage data are deleted successfully");
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteByTargetEntityUnused(&$media_usage) {
    $query = $this->connection->delete($this->tableName)
      ->condition('target_id', $media_usage['target_id'])
      ->condition('target_type', $media_usage['target_type']);
    $query->isNull('source_id')->isNull('source_type');
    $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteBySourceRevision(EntityInterface $entity) {

    $source_vid = $entity->getRevisionId();
    $source_id = $entity->id();
    $count = 0;
    if ($source_id && $source_vid) {

      $query = $this->connection->delete($this->tableName)
        ->condition('source_id', $source_id);

      // Insert eneity_usage data after delete.
      if ($query->execute()) {
        $query = $this->connection->select($this->entityUsageTable, 'e')
          ->fields('e', [
            'target_id', 'target_type', 'source_id', 'source_langcode', 'source_vid', 'source_type', 'method', 'field_name',
          ])
          ->condition('source_id', $source_id);
        $result = $query->execute();

        while ($entity_usage = $result->fetchAssoc()) {
          $target_id = $entity_usage['target_id'];
          try {
            $flag = $this->saveMediaUsageData($target_id, $entity_usage);
            if ($flag) {
              $count++;
            }
          }
          catch (\Exception $e) {
            watchdog_exception('ul_media_usage.revision', $e);
          }
        }
      }
    }
    return $count;
  }

  /**
   * Get recent updated data from the entity_usage, node and media.
   */
  public function getRecentUpdatedData($flag) {
    $recentData = [];

    switch ($flag) {
      case 1:
        $range = 10000;
        break;

      case 2;
        $range = 10;
        break;

      case 3;
        $range = 5;
        break;

      case 4;
        $range = 1;
        break;

      case 10;
        $range = 0.05;
        break;

      case 20;
        $range = 0.01;
        break;

      default:
        $range = 2;
    }

    // The most recent change of media within a number of days.
    $max_changed = $this->getMaxTimestamp($range, 'changed');
    // The most recent change of node or paragraph within a number of days.
    $max_s_changed = $this->getMaxTimestamp($range, 'source_changed');

    // Get all media IDs changed recently.
    $query_m = $this->connection
      ->query("SELECT distinct mid
        FROM {media_field_data}
        WHERE changed >= :timestamp", [':timestamp' => $max_changed]);
    $mids = $query_m->fetchAllKeyed(0, 0);
    if (!empty($mids)) {
      $recentData['media'] = $mids;
    }

    // Get all node IDs changed recently.
    $query_n = $this->connection
      ->query("SELECT distinct nid
        FROM {node_field_data}
        WHERE changed >= :timestamp", [':timestamp' => $max_s_changed]);
    $nids = $query_n->fetchAllKeyed(0, 0);
    if (!empty($nids)) {
      $recentData['node'] = $nids;
    }

    // Get all paragraph IDs changed recently.
    $query_p = $this->connection
      ->query("SELECT distinct id
        FROM {paragraphs_item_field_data}
        WHERE created >= :timestamp", [':timestamp' => $max_s_changed]);
    $pids = $query_p->fetchAllKeyed(0, 0);
    if (!empty($pids)) {
      $recentData['paragraph'] = $pids;
    }

    return $recentData;
  }

  /**
   * Get the timestamp before a number of days.
   *
   * @param int $range
   *   The number of days.
   * @param string $field
   *   The field of DB table.
   *
   * @return int
   *   The max timestamp.
   */
  protected function getMaxTimestamp($range, $field) {
    $max = $this->connection
      ->query("SELECT MAX($field) FROM {ul_media_usage}")
      ->fetchField();
    $max = $max - 3600 * 24 * $range;
    return max(1, $max);
  }

  /**
   * {@inheritdoc}
   */
  public function listAllEntityUsage($flag = 2) {
    // Get array of data: media, node & paragraph which are changed.
    $recentData = [];
    $result = $this->connection
      ->query("SELECT count(id) FROM {ul_media_usage} WHERE source_id IS NOT NULL");
    $total = ($result) ? $result->fetchField() : 0;

    if ($flag == 1 && $total > 4000) {
      $flag = 3;
    }
    else {
    }
    $recentData = $this->getRecentUpdatedData($flag);

    // $timestamp = $this->getRecentChange();
    $query = $this->connection->select($this->entityUsageTable, 'e')
      ->fields('e', ['target_id'])
      ->condition('target_type', 'media');
    // Debug the query code (do not remove it):
    // $query->condition('target_id', [17206], 'IN');
    // Insert/update data changed recently.
    if (!empty($recentData)) {
      $orGroup = $query->orConditionGroup();

      if (!empty($recentData['media'])) {
        $orGroup->condition('target_id', $recentData['media'], 'IN');
      }
      if (!empty($recentData['node'])) {
        $orGroup->condition('source_id', $recentData['node'], 'IN');
      }
      if (!empty($recentData['paragraph'])) {
        $orGroup->condition('source_id', $recentData['paragraph'], 'IN');
      }
      $query->condition($orGroup);
    }

    $result = $query->distinct()->execute();
    $references = [];
    // Loop all entity_usage data.
    foreach ($result as $usage) {
      $references[] = $usage->target_id;
    }

    return $references;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityUsageData($target_id) {
    // Get media entity data.
    $query = $this->connection->select($this->entityUsageTable, 'e')
      ->fields('e', [
        'target_id',
        'target_type',
        'source_id',
        'source_id_string',
        'source_type',
        'source_langcode',
        'source_vid',
        'method',
        'field_name',
      ])
      ->condition('target_id', $target_id);

    $result = $query->distinct()->execute();
    $references = [];
    // Loop all entity_usage data.
    foreach ($result as $usage) {
      $source_id_value = !empty($usage->source_id) ? (string) $usage->source_id : (string) $usage->source_id_string;
      $references[] = [
        'target_id' => $usage->target_id,
        'target_type' => $usage->target_type,
        'source_id' => $source_id_value,
        'source_type' => $usage->source_type,
        'source_langcode' => $usage->source_langcode,
        'source_vid' => $usage->source_vid,
        'method' => $usage->method,
        'field_name' => $usage->field_name,
      ];
    }
    return $references;
  }

  /**
   * {@inheritdoc}
   */
  public function saveMediaUsageData($mid, &$entity_usage) {
    if (!isset($mid)) {
      return FALSE;
    }

    $media_usage = [];
    $media_usage['source_id'] = NULL;
    $media_usage['source_vid'] = NULL;
    $media_usage['source_type'] = NULL;
    $media_usage['source_langcode'] = NULL;
    $media_usage['source_language'] = NULL;
    $media_usage['used_url'] = NULL;
    $media_usage['source_changed'] = NULL;
    $media_usage['method'] = NULL;
    $media_usage['field_name'] = NULL;
    $media_usage['source_type_name'] = NULL;
    $media_usage['source_paragraph_id'] = NULL;

    $source_id = $nid = $entity_usage['source_id'];
    $type = $entity_usage['source_type'];
    $source_langcode = $entity_usage['source_langcode'];

    if ($type == 'paragraph') {
      $media_usage['source_paragraph_id'] = $source_id;
      $nid = $this->getParentNodeParagraph($source_id, $type);
    }

    if ($nid > 0 && $node = $this->nodeStorage->load($nid)) {
      if ($node->hasTranslation($source_langcode)) {
        $node = $node->getTranslation($source_langcode);
      }
      $this->setSourceEntityData($node, $media_usage, $entity_usage);
    }

    $media_usage['target_id'] = $entity_usage['target_id'];

    $is_update = FALSE;
    if ($media_entity = $this->mediaStorage->load($mid)) {
      $this->setMediaEntityData($media_entity, $media_usage);

      $media_usage['image_attached_url'] = $this->getImageAttached($media_entity->id());

      if ($id = $this->checkDataExisting($media_usage)) {
        $is_update = $this->updateDataInDatabase($media_usage, $id);
      }
      else {
        $is_update = $this->saveDataToDatabase($media_usage);
        if ($media_usage['source_id'] == NULL) {
          $is_update = FALSE;
        }
      }
    }

    return $is_update;
  }

  /**
   * Provite function to set the source node data in media_usage.
   *
   * @param \Drupal\node\NodeInterface $node
   *   A source Node entity.
   * @param array $media_usage
   *   Array of media_usage data passed by reference.
   * @param array $entity_usage
   *   Array of entity_usage data passed by reference.
   */
  protected function setSourceEntityData(NodeInterface $node, array &$media_usage, array &$entity_usage) {
    if ($node instanceof NodeInterface) {
      // Get the File entity from Media.
      $media_usage['source_id'] = $node->id();
      $media_usage['source_vid'] = $node->get('vid')->value;
      $media_usage['source_type'] = $node->bundle();
      $media_usage['source_type_name'] = $this->getSourceBundleName($node);
      $media_usage['source_langcode'] = $node->get('langcode')->value;
      $media_usage['source_language'] = $this->getSourceLanguageName($node);

      $media_usage['used_url'] = $this->getSourcePathAlias($node);
      $media_usage['source_changed'] = $node->get('changed')->value;
      $media_usage['method'] = $entity_usage["method"];
      $media_usage['field_name'] = $entity_usage["field_name"];
    }
  }

  /**
   * Provite function to set the Media entity date in the meida_usage.
   *
   * @param \Drupal\media\MediaInterface $media_entity
   *   A target media entity.
   * @param array $media_usage
   *   Array of media_usage data.
   */
  protected function setMediaEntityData(MediaInterface &$media_entity, array &$media_usage) {
    // Get media entity data.
    $record = $this->getMediaFieldData($media_entity->id());

    // Get the target file fid based on the bundle.
    $target_bundle = $record->bundle;
    $field_name = "field_media_$target_bundle";

    if (isset($media_entity->$field_name->target_id)) {
      $fid = $media_entity->$field_name->target_id;
      $media_usage['filesize'] = $this->getMediaFilesize($fid);
    }
    else {
      $media_usage['filesize'] = 0;
    }

    if ($record) {
      $media_usage['author'] = $record->uid;
      $media_usage['target_type'] = $target_bundle;
      $media_usage['target_langcode'] = $record->langcode;
      $lang = $this->languageManager->getLanguageName($record->langcode);

      $media_usage['target_language'] = $lang;
      $media_usage['status'] = $record->status;
      $media_usage['changed'] = $record->changed;
      $media_usage['media_name'] = $record->name;

      $media_usage['thumbnail_url'] = $this->getThumbnailUri($record->thumbnail__target_id);

    }
    else {
      $media_usage['author'] = 1;
      $media_usage['thumbnail_url'] = "";
      $media_usage['target_type'] = "image";
      $media_usage['target_langcode'] = "en";
      $media_usage['target_language'] = "English";
      $media_usage['status'] = 1;
      $media_usage['changed'] = 1573674071;
      $media_usage['media_name'] = "";
    }
  }

  /**
   * Get the media entity filesize.
   *
   * @param int $fid
   *   The file ID.
   *
   * @return int
   *   The filesize of media.
   */
  public function getMediaFilesize($fid) {
    $filesize = 0;
    if (isset($fid) && $file = $this->fileStorage->load($fid)) {
      $filesize = $file->get('filesize')->value;
    }
    return $filesize;
  }

  /**
   * Get field_images_for_attachements.
   */
  protected function getImageAttached($mid) {
    $query = $this->connection->select('media__field_images_for_attachments', 'm');
    $query->fields('f', ['uri']);
    $query->leftJoin('file_managed', 'f', 'f.fid = m.field_images_for_attachments_target_id');
    $query->condition('m.entity_id', $mid, '=');
    // $query->condition('m.deleted', 0, '=');
    // $query->condition('m.bundle', 'file', '=');
    $query->range(0, 1);
    $result = $query->execute();
    $record = $result->fetchObject();
    return ($record) ? $record->uri : "";
  }

  /**
   * Get biggest value of timestamp in changed column.
   *
   * @param array $media_usage
   *   Array of media_usage data passed by reference.
   */
  protected function checkDataExisting(array &$media_usage) {
    $this->deleteByTargetEntityUnused($media_usage);

    $query = $this->connection->select($this->tableName, 'e')
      ->fields('e', ['id'])
      ->condition('target_id', $media_usage['target_id'])
      ->condition('source_id', $media_usage['source_id'])
      ->condition('source_langcode', $media_usage['source_langcode'])
      ->condition('method', $media_usage['method'])
      ->condition('field_name', $media_usage['field_name'])
      ->orderBy('id');
    $result = $query->execute();

    foreach ($result as $usage) {
      return $usage->id;
    }
    return FALSE;
  }

  /**
   * Update the ul_media_usage table.
   *
   * @param array $media_usage
   *   Array of media_usage data passed by reference.
   * @param int $id
   *   The id of ul_media_usage table.
   */
  protected function updateDataInDatabase(array &$media_usage, $id) {
    $query = $this->connection->update($this->tableName)
      ->fields([
        'source_id' => $media_usage['source_id'],
        'source_vid' => $media_usage['source_vid'],
        'source_paragraph_id' => $media_usage['source_paragraph_id'],
        'used_url' => $media_usage['used_url'],
        'source_changed' => $media_usage['source_changed'],
        'source_type_name' => $media_usage['source_type_name'],
        'source_language' => $media_usage['source_language'],
      ])
      ->condition('id', $id);
    // Add OR group to update a record only if any field value is changed.
    $orGroup = $query->orConditionGroup()
      ->condition('source_id', $media_usage['source_id'], '<>')
      ->condition('source_vid', $media_usage['source_vid'], '<>')
      ->condition('used_url', $media_usage['used_url'], '<>')
      ->condition('source_changed', $media_usage['source_changed'], '<>');

    $query->condition($orGroup);
    $num_updated = $query->execute();

    // Update media data without changing the source data.
    $num_updated += $this->updateUlMediaInDatabase($media_usage);

    return $num_updated;
  }

  /**
   * Update the ul_media_usage table for media data only.
   *
   * @param array $media_usage
   *   Array of media_usage data passed by reference.
   */
  protected function updateUlMediaInDatabase(array &$media_usage) {
    $query = $this->connection->update($this->tableName)
      ->fields([
        'thumbnail_url' => $media_usage['thumbnail_url'],
        'media_name' => $media_usage['media_name'],
        'image_attached_url' => $media_usage['image_attached_url'],
        'filesize' => $media_usage['filesize'],
        'changed' => $media_usage['changed'],
      ])
      ->condition('target_id', $media_usage['target_id']);

    $group = $query->orConditionGroup()
      ->condition('filesize', $media_usage['filesize'], '<>')
      ->condition('media_name', $media_usage['media_name'], '<>');

    $num_updated = $query->condition($group)->execute();

    return ($num_updated > 0) ? 1 : 0;
  }

  /**
   * Save new data into the ul_media_usage table.
   *
   * @param array $media_usage
   *   Array of media_usage data passed by reference.
   */
  protected function saveDataToDatabase(array &$media_usage) {
    $result = $this->connection->insert($this->tableName)
      ->fields([
        'target_id', 'thumbnail_url', 'media_name', 'target_langcode', 'target_language', 'author', 'target_type', 'status', 'filesize',
        'changed', 'image_attached_url', 'source_id', 'source_vid', 'used_url',
        'source_type', 'source_type_name', 'source_langcode', 'source_language', 'method', 'field_name', 'source_changed', 'source_paragraph_id',
      ])
      ->values($media_usage)
      ->execute();

    return $result;
  }

  /**
   * Provite function to get the parent Node ID from the Paragraph ID.
   *
   * @param string $id
   *   A target entity ID.
   * @param string $type
   *   A paragraph entity type.
   *
   * @return mixed
   *   Return the parent_id (node id) for the paragraph.
   */
  public function getParentNodeParagraph($id, $type) {
    if ($type !== 'paragraph') {
      return;
    }
    $query = $this->connection->select('paragraphs_item_field_data', 'p');
    $query->fields('p', ['parent_id', 'parent_type']);
    $query->condition('id', $id, '=');
    $query->range(0, 1);
    $result = $query->execute();
    $record = $result->fetchObject();

    if (!$record) {
      return FALSE;
    }
    else {
      $parent_id = $record->parent_id;
      $parent_type = $record->parent_type;

      if ($parent_type == 'node') {
        return $parent_id;
      }
      elseif ($parent_type == 'paragraph') {
        return $this->getParentNodeParagraph($parent_id, $parent_type);
      }
      else {
        return FALSE;
      }

    }

  }

  /**
   * Get the thumbnail__target_id.
   */
  public function getMediaFieldData($mid) {
    $query = $this->connection->select('media_field_data', 'm');
    $query->fields('m', [
      'mid', 'bundle', 'name', 'uid', 'changed', 'langcode',
      'thumbnail__target_id', 'default_langcode', 'status',
    ]);
    $query->condition('mid', $mid, '=');
    $query->range(0, 1);
    $result = $query->execute();
    return ($result) ? $result->fetchObject() : FALSE;
  }

  /**
   * Check if a value is an integer, or an integer string.
   *
   * Core doesn't support big integers (bigint) for entity reference fields.
   * Therefore we consider integers with more than 10 digits (big integer) to be
   * strings.
   *
   * @param int|string $value
   *   The value to check.
   *
   * @return bool
   *   TRUE if the value is a numeric integer or a string containing an integer,
   *   FALSE otherwise.
   */
  protected function isInt($value) {
    return ((string) (int) $value === (string) $value) && strlen($value) < 11;
  }

  /**
   * Select ul_media_usage table data to associate array.
   *
   * @param string $flag
   *   The flag: ALL or any.
   * @param int $rang
   *   The limit records from database.
   *
   * @return array
   *   The associate arrary of ul_media_usage data.
   */
  public function selectMediaUsageData($flag, $rang = 500) {
    $data[] = ['Media ID', 'Media Name', 'Media language', 'Author',
      'Source', 'Status', 'Updated', 'Filesize', 'Used in',
      'Content Type', 'Content Language', 'Method',
    ];

    $query = $this->connection->select($this->tableName, 'm');
    $query->leftJoin('users_field_data', 'ufd', 'm.author = ufd.uid');
    $query->innerJoin('media_field_data', 'mfd', 'm.target_id = mfd.mid');

    $query->fields('m', ['id', 'target_id', 'target_language',
      'target_type', 'status', 'changed', 'filesize', 'used_url', 'source_id',
      'source_type_name', 'source_language', 'method', 'source_langcode',
    ]);
    $query->addField('ufd', 'name', 'author');
    $query->addField('ufd', 'langcode', 'users_langcode');

    $query->addField('mfd', 'mid', 'mid');
    $query->addField('mfd', 'name', 'media_name');
    $query->addField('mfd', 'changed', 'media_changed');
    $query->addField('mfd', 'langcode', 'media_langcode');

    $query->orderBy('m.target_id', 'ASC');

    if ($flag !== "ALL") {
      $query->range(0, $rang);
    }

    $result = $query->distinct()->execute()->fetchAll();

    foreach ($result as $record) {
      $datetime = date('m/d/Y', $record->changed);
      $bytes = number_format($record->filesize / 1024) . ' KB';
      $used_url = $record->used_url;

      if (!empty($used_url) && $nid = $record->source_id) {
        $langcode = $record->source_langcode;

        $lang_str = ($langcode == 'en') ? "" : "/$langcode";
        if (stristr($this->host, 'shimadzu')) {
          $lang_str = "";
        }

        $used_url = $this->host . $lang_str . $this->aliasManager->getAliasByPath('/node/' . $nid, $record->source_langcode);
      }
      $username = $record->author;

      $data[] = [$record->target_id, $record->media_name, $record->target_language,
        $username, $record->target_type, $record->status, $datetime, $bytes,
        $used_url, $record->source_type_name, $record->source_language,
        $record->method,
      ];
    }
    return $data;
  }

  /**
   * Get the username from the UID.
   */
  public function getUserName($uid) {
    $result = $this->connection->select('users_field_data', 'u')
      ->fields('u', ['name'])
      ->condition('uid', $uid, '=')
      ->execute();
    if ($name = $result->fetchAssoc()) {
      return $name['name'];
    }
    else {
      return $uid;
    }
  }

  /**
   * Save unused Meida data into ul_media_usage table.
   *
   * @param string $table
   *   The table name to select data, media_field_data.
   *
   * @return int
   *   The count of items insert into DB.
   */
  public function saveUnusedMediaData($table = 'media_field_data') {
    $count = 0;
    $media_usage = [];
    $query = $this->connection->select($table, 'm');
    $query->fields('m',
    ['mid', 'langcode', 'bundle', 'status', 'name', 'uid', 'changed']);
    $query->where('m.mid not in (select distinct target_id from ul_media_usage)');
    $result = $query->execute();

    foreach ($result as $record) {
      $mid = $record->mid;
      $media_entity = $this->mediaStorage->load($mid);

      // Get the target file fid based on the bundle.
      $target_bundle = $record->bundle;
      $field_name = "field_media_$target_bundle";

      if (isset($media_entity->$field_name->target_id)) {
        $fid = $media_entity->$field_name->target_id;
        $filesize = $this->getMediaFilesize($fid);
      }
      else {
        $filesize = 0;
      }

      $lang = $this->languageManager->getLanguageName($record->langcode);

      $media_usage['target_id'] = $mid;
      $media_usage['media_name'] = $record->name;
      $media_usage['target_langcode'] = $record->langcode;
      $media_usage['target_language'] = $lang;
      $media_usage['author'] = $record->uid;
      $media_usage['target_type'] = $target_bundle;
      $media_usage['status'] = $record->status;
      $media_usage['filesize'] = $filesize;
      $media_usage['changed'] = $record->changed;

      // Insert the media data into ul_media_usage table.
      $result2 = $this->connection
        ->insert($this->tableName)
        ->fields([
          'target_id', 'media_name', 'target_langcode', 'target_language',
          'author', 'target_type', 'status', 'filesize', 'changed',
        ])
        ->values($media_usage)
        ->execute();

      if ($result2) {
        $count++;
      }

    }

    return $count;
  }

  /**
   * Get the thumbnail url for an image.
   *
   * @param int $id
   *   The Meida thumbnail__target_id.
   *
   * @return string
   *   The URI of thmbnail.
   */
  public function getThumbnailUri($id) {
    if (isset($id) && $thum_file = $this->fileStorage->load($id)) {
      return $thum_file->get('uri')->value;
    }
    return "";
  }

  /**
   * Save unused Meida data into ul_media_usage table.
   *
   * @param \Drupal\media\MediaInterface $entity
   *   The table name to select data, media_field_data.
   *
   * @return int
   *   The count of items insert into DB.
   */
  public function updateMediaDataWhenEntityUpdate(MediaInterface $entity) {

    $mid = $entity->id();
    // Get the target file fid based on the bundle.
    $target_bundle = $entity->bundle();
    $field_name = "field_media_$target_bundle";

    if (isset($entity->$field_name->target_id)) {
      $fid = $entity->$field_name->target_id;
      $filesize = $this->getMediaFilesize($fid);
      // Update query for the new filesize.
      $query = $this->connection->update($this->tableName)
        ->fields([
          'thumbnail_url' => $this->getThumbnailUri($fid),
          'filesize' => $filesize,
          'media_name' => $entity->get('name')->value,
          'changed' => $entity->get('changed')->value,
        ])
        ->condition('target_id', $mid);
      return $query->execute();
    }
    return FALSE;

  }

  /**
   * Get the Path alias instead of /node/nid.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The table name to select data, media_field_data.
   *
   * @return int
   *   The path alias.
   */
  public function getSourcePathAlias(NodeInterface $entity) {
    // Fix the URLs in Used in column on Shimadzu site.
    $alias = $entity->toUrl()->toString();
    return $alias;
  }

  /**
   * Get the language name in English.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The table name to select data, media_field_data.
   *
   * @return string
   *   The language name.
   */
  public function getSourceLanguageName(NodeInterface $entity) {
    // The language label(name): translated label if label is translated.
    $language = $entity->language()->getName();
    $lang = $entity->language()->getId();

    // Get a Standard Language Name in English, but not Translation.
    if (in_array($lang, ['fr-ca', 'pt-br'])) {
      switch ($lang) {
        case "fr-ca":
          $language = "French Canadian";
          break;

        case "pt-br":
          $language = "Portuguese, Brazil";
          break;

        default:
          $language = 'English';
      }
    }
    else {
      $list = $this->languageManager::getStandardLanguageList();
      if (isset($list[$lang])) {
        $language = $list[$lang][0];
      }
    }
    return $language;

  }

  /**
   * Get the bundle name in English.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The table name to select data, media_field_data.
   *
   * @return string
   *   The bundle name.
   */
  public function getSourceBundleName(NodeInterface $entity) {
    $bundle = $entity->bundle();

    switch ($bundle) {
      case "resource":
        $name = "Resources";
        break;

      case "event":
        $name = "Events";
        break;

      case "news":
        $name = "News";
        break;

      case "homepage":
        $name = "Homepages";
        break;

      case "landing_page":
        $name = "Landing Pages";
        break;

      case "location":
        $name = "Locations";
        break;

      case "market_access_profile":
        $name = "Global Market Access";
        break;

      case "campaign_page":
        $name = "Campaign Landing Pages";
        break;

      default:
        $name = $entity->type->entity->label();
    }

    return $name;
  }

}

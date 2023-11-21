<?php

namespace Drupal\ul_media_usage;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\views\Views;
use Drupal\ul_media_usage\MediaUsage;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Manages Entity Usage integration with Batch API.
 */
class EntityUsageBatchManager implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The service MediaUsage.
   *
   * @var \Drupal\ul_media_usage\MediaUsage
   */
  protected $mediaUsage;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Creates a EntityUsageBatchManager object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   * @param \Drupal\ul_media_usage\MediaUsage $media_usage
   *   The service of ul_media_usage.usage.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The entity type messenger.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, TranslationInterface $string_translation, MediaUsage $media_usage, MessengerInterface $messenger, LoggerChannelFactoryInterface $logger_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->stringTranslation = $string_translation;
    $this->mediaUsage = $media_usage;
    $this->messenger = $messenger;
    $this->loggerFactory = $logger_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('string_translation'),
      $container->get('ul_media_usage.usage'),
      $container->get('messenger'),
      $container->get('logger.factory'),
    );
  }

  /**
   * The second method to load entity_usage data and save to ul_media_usgae.
   */
  public function generateDataMediaUsage($rang = 1) {

    $allEntityUsage = $this->mediaUsage->listAllEntityUsage($rang);
    $count_used = 0;
    $count_unused = 0;

    foreach ($allEntityUsage as $target_id) {

      $multple_records = $this->mediaUsage->getEntityUsageData($target_id);

      foreach ($multple_records as $entity_usage) {
        // Only look for entities enabled for tracking on the settings form.
        if (isset($target_id)) {
          /** @var \Drupal\Core\Entity\EntityInterface $entity */
          try {
            $flag = $this->mediaUsage->saveMediaUsageData($target_id, $entity_usage);
            if ($flag) {
              $count_used++;
            }
          }
          catch (\Exception $e) {
            watchdog_exception('ul_media_usage.batch', $e);
          }
        }
      }
    }

    if ($rang == 1) {
      // Save unused Media into ul_media_usage table.
      $count_unused = $this->mediaUsage->saveUnusedMediaData('media_field_data');
    }

    if ($count_used > 0 && $rang < 3) {
      $this->loggerFactory->get('ul_media_usage')->info("Add $count_used USED items into the ul_media_usage table.");
      $this->messenger->addStatus($this->t('Total of %count USED Media are inserted/refreshed successfully.', [
        '%count' => $count_used,
      ]));
    }

    if ($count_unused > 0 && $rang < 3) {
      $this->loggerFactory->get('ul_media_usage')->info("Add $count_unused UNUSED Media items into the ul_media_usage table.");
      $this->messenger->addStatus($this->t('Total of %count UNUSED Media are inserted/refreshed successfully.', [
        '%count' => $count_unused,
      ]));
    }
    // Clear cache of View.
    // drupal_flush_all_caches();
    $view = Views::getView('ul_media_usage');
    $view->storage->invalidateCaches();
  }

  /**
   * Recreate the entity usage statistics.
   *
   * Generate a batch to recreate the statistics for all entities.
   * Note that if we force all statistics to be created, there is no need to
   * separate them between source / target cases. If all entities are
   * going to be re-tracked, tracking all of them as source is enough, because
   * there could never be a target without a source.
   */
  public function recreate($rang) {
    $this->executeMultiple($rang);

    if ($rang == 1) {
      // Save unused Media into ul_media_usage table.
      $count_unused = $this->mediaUsage->saveUnusedMediaData('media_field_data');
      if ($count_unused > 0) {
        $this->loggerFactory->get('ul_media_usage')->info("Add $count_unused UNUSED Media items into the ul_media_usage.");
        $this->messenger->addMessage("Add $count_unused UNUSED Media item(s).");
      }
    }
    $view = Views::getView('ul_media_usage');
    $view->storage->invalidateCaches();
  }

  /**
   * Create a batch to process the entity types in bulk.
   *
   * @return mixed
   *   Array {operations: array<array{callable-string, array}>,
   *   finished: callable-string,
   *   title: \Drupal\Core\StringTranslation\TranslatableMarkup,
   *   progress_message: \Drupal\Core\StringTranslation\TranslatableMarkup,
   *   error_message: \Drupal\Core\StringTranslation\TranslatableMarkup}
   *   The batch array.
   */
  public function executeMultiple($rang) {
    $target_ids = $this->mediaUsage->listAllEntityUsage($rang);
    // $total = count($entities);
    foreach ($target_ids as $target_id) {
      $operations[] = [
        [static::class, 'processBatch'],
        [$target_id],
      ];
    }

    if ($operations) {
      $batch = [
        'operations' => $operations,
        'finished' => [static::class, 'finishBatch'],
      ];
      batch_set($batch);
    }

  }

  /**
   * Processes the batch item.
   *
   * @param string $target_id
   *   Keyed array of data to process.
   * @param array $context
   *   The batch context.
   */
  public static function processBatch($target_id, array &$context) {
    $count_used = 0;
    if (!isset($context['results']['processed'])) {
      $context['results']['processed'] = 0;
    }
    if (!isset($context['results']['saved'])) {
      $context['results']['saved'] = 0;
    }

    if (isset($target_id)) {

      $multple_records = \Drupal::service('ul_media_usage.usage')->getEntityUsageData($target_id);

      foreach ($multple_records as $entity_usage) {
        try {
          $flag = \Drupal::service('ul_media_usage.usage')->saveMediaUsageData($target_id, $entity_usage);
          if ($flag) {
            $count_used++;
            // $context['results']['saved']++;
            $context['results']['processed']++;
          }
        }
        catch (\Exception $e) {
          watchdog_exception('ul_media_usage.batch', $e);
        }

      }
    }

  }

  /**
   * Finish batch.
   *
   * @param bool $success
   *   Indicates whether the batch process was successful.
   * @param array $results
   *   Results information passed from the processing callback.
   */
  public static function finishBatch($success, array $results) {
    if ($results['processed'] > 0) {
      \Drupal::messenger()->addMessage(
        \Drupal::translation()->formatPlural($results['processed'], '1 media item has been updated.', '@count media items have been updated.')
      );
    }
    else {
      \Drupal::messenger()->addMessage('No media items have been updated.');

    }
  }

}

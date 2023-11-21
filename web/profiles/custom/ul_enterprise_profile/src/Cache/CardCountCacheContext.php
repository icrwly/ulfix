<?php

namespace Drupal\ul_enterprise_profile\Cache;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Cache\Context\CacheContextInterface;

/**
 * Defines the ThemeCacheContext service, for "per theme" caching.
 *
 * Cache context ID: 'card_count'.
 */
class CardCountCacheContext implements CacheContextInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The context string, set outside of class.
   *
   * @var string
   */
  protected $context;

  /**
   * Constructs a new CardCountCacheContext.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Card Count Context');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    return 'card_context.' . $this->context;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    return new CacheableMetadata();
  }

  /**
   * Set the context function.
   *
   * @param string $context
   *   A context string.
   */
  public function setContext($context = 'grid_card') {
    return $this->context = $context;
  }

}

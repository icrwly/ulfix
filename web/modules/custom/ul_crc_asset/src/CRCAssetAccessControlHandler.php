<?php

namespace Drupal\ul_crc_asset;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\PageCache\ResponsePolicy\Vary;

/**
 * Access controller for the Alert entity.
 *
 * @see \Drupal\ul_alerts\Entity\Alert.
 */
class CRCAssetAccessControlHandler extends EntityAccessControlHandler implements EntityHandlerInterface {

  /**
   * An alias manager to find the alias for the current system path.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The path matcher.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The Vary Page Cache response policy.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\Vary
   */
  protected $pageCacheVary;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('path_alias.manager'),
      $container->get('path.matcher'),
      $container->get('request_stack'),
      $container->get('page_cache_vary')
    );
  }

  /**
   * Constructs the node access control handler instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The alias manager service.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The path matcher service.
   * @param \Drupal\Core\PageCache\ResponsePolicy\Vary $page_cache_vary
   *   The vary page cache response policy service.
   */
  public function __construct(EntityTypeInterface $entity_type, AliasManagerInterface $alias_manager, PathMatcherInterface $path_matcher, RequestStack $request_stack, Vary $page_cache_vary) {
    parent::__construct($entity_type);
    $this->aliasManager = $alias_manager;
    $this->pathMatcher = $path_matcher;
    $this->requestStack = $request_stack;
    $this->pageCacheVary = $page_cache_vary;
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\ul_alerts\Entity\AlertInterface $entity */
    switch ($operation) {
      case 'view':

        $access = AccessResult::allowedIfHasPermission($account, 'view published crc asset entities');
        return $access;
    }

    return AccessResult::neutral();
  }

}

<?php

namespace Drupal\ul_alerts;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\PageCache\ResponsePolicy\Vary;
use Drupal\path_alias\AliasManagerInterface;

/**
 * Access controller for the Alert entity.
 *
 * @see \Drupal\ul_alerts\Entity\Alert.
 */
class AlertAccessControlHandler extends EntityAccessControlHandler implements EntityHandlerInterface {

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
  public function __construct(
    EntityTypeInterface $entity_type,
    AliasManagerInterface $alias_manager,
    PathMatcherInterface $path_matcher,
    RequestStack $request_stack,
    Vary $page_cache_vary) {
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
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished alert entities');
        }

        $access = AccessResult::allowedIfHasPermission($account, 'view published alert entities');

        // Getting visibility conditions.
        // Page settings.
        // Hide or show any alerts that have been set to show on certain pages.
        if (!empty($entity->hasField('field_shared_pages') && $entity->get('field_shared_pages')->first())) {
          $pages = $entity->get('field_shared_pages')->first()->getValue();
          if (!empty($pages['value'])) {
            $pages = mb_strtolower($pages['value']);

            // @see ul_alerts.alerts.js
            $path = $this->requestStack->getCurrentRequest()->get('path');
            if (!empty($path)) {

              // Do not trim a trailing slash if that is the complete path.
              $path = $path === '/' ? $path : rtrim($path, '/');
              $path_alias = mb_strtolower($this->aliasManager->getAliasByPath($path));

              $pass = $this->pathMatcher->matchPath($path_alias, $pages) || (($path != $path_alias) && $this->pathMatcher->matchPath($path, $pages));

              $access->addCacheTags(['ul_alert_values']);

              if (!$pass) {
                return AccessResult::forbidden();
              }
            }
          }
        }
        // Getting language settings.
        // Hide or show any alerts that have values in the languages field.
        if (!empty($entity->hasField('field_languages') && $entity->get('field_languages')->getValue())) {
          $pass = FALSE;
          $languages = $entity->get('field_languages')->getValue();
          if (!empty($languages)) {
            // Accommodating cache so that it loads the proper alert on each
            // page load.
            $this->pageCacheVary->add('Accept-Language');
            $active_lang = $this->requestStack->getCurrentRequest()->server->get('HTTP_ACCEPT_LANGUAGE');

            // Active language string might have addition characters so get
            // only the langcode.
            // example: HTML_ACCEPT_LANGUAGE could be 'en-US,en;q=0.9'.
            if (strpos($active_lang, ';')) {
              $active_lang = explode(';', $active_lang);
              $active_lang = $active_lang[0];
            }

            // Active language could have multiple languages.
            // example: en-US,en.
            if (strpos($active_lang, ',')) {
              $active_lang = explode(',', $active_lang);
            }
            // Set as an array with single element.
            else {
              $active_lang = [$active_lang];
            }

            foreach ($languages as $language) {
              if (in_array($language['value'], $active_lang)) {
                $pass = TRUE;
              }
            }

            $access->addCacheTags(['ul_alert_values']);

            if (!$pass) {
              return AccessResult::forbidden();
            }
          }
        }

        return $access;

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit alert entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete alert entities');
    }

    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add alert entities');
  }

}

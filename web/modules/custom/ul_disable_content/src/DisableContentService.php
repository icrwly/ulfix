<?php

namespace Drupal\ul_disable_content;

use Drupal\Core\Database\Connection;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * UL Disable Content Script Service.
 */
class DisableContentService implements DisableContentServiceInterface {

  /**
   * ConfigFactory service.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * List of content types.
   *
   * @var array
   */
  protected $contentTypes;

  /**
   * List of saved `hidden` content types.
   *
   * @var array
   */
  protected $hiddenContentTypes;

  /**
   * Route Matcher.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new UlMarketoService object.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   Config factory object.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route matcher.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(ConfigFactory $config, Connection $connection, EntityTypeManagerInterface $entityTypeManager, RouteMatchInterface $route_match, LanguageManagerInterface $language_manager) {
    $this->config = $config;
    $this->connection = $connection;
    $this->entityTypeManager = $entityTypeManager;
    $this->routeMatch = $route_match;
    $this->languageManager = $language_manager;
    $this->contentTypes = $this->getContentTypes();
    $this->hiddenContentTypes = $this->getDisabledContentTypes();
  }

  /**
   * Function to return a list of all content types on the site.
   */
  public function getContentTypes() {
    $types = [];
    // $entityTypeManager = \Drupal::service('entity_type.manager');
    $contentTypes = $this->entityTypeManager->getStorage('node_type')->loadMultiple();
    foreach ($contentTypes as $contentType) {
      $types[$contentType->id()] = $contentType->label();
    }

    return $types;
  }

  /**
   * Function to return a list of currently disabled content types.
   */
  public function getDisabledContentTypes() {
    $disabledContentTypes = [];
    // Get current saved `hidden` content types:
    $hiddenSettings = $this->config->get('ul_disable_content.metadata.settings');
    $data = $hiddenSettings->get('options');

    if ($data && is_array($data['options']) && count($data['options']) > 0) {
      $disabledContentTypes = $data['options'];
      // Remove false positives:
      foreach ($disabledContentTypes as $key => $val) {
        if ($val == 0) {
          unset($disabledContentTypes[$key]);
        }
      }
    }

    return $disabledContentTypes;
  }

}

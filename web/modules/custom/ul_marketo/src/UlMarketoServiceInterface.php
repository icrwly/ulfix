<?php

namespace Drupal\ul_marketo;

use Drupal\Core\Entity\EntityInterface;

/**
 * Interface CRCServiceInterface.
 */
interface UlMarketoServiceInterface {

  /**
   * Return array of Instance Settings.
   *
   * @return array
   *   Array of instances.
   */
  public function getInstanceSettings();

  /**
   * Fetch marketo settings for a specific entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity type id.
   *
   * @return bool|mixed
   *   Return the entity settings or false on empty.
   */
  public function getEntitySettings(EntityInterface $entity);

  /**
   * Overrides entity bundle/entity type marketo settings for a single entity.
   *
   * @param string $entity_type_id
   *   The entity type id.
   * @param int $id
   *   The entity id.
   * @param array $settings
   *   The settings array.
   *
   * @return $this
   */
  public function setEntitySettings($entity_type_id, $id, array $settings);

  /**
   * Removes marketo settings for an entity that overrides the marketo settings.
   *
   * @param string $entity_type_id
   *   The entity type id.
   * @param string|null $entity_ids
   *   The entity ids.
   *
   * @return $this
   */
  public function removeEntitySettings($entity_type_id, $entity_ids = NULL);

  /**
   * Get the default config.
   *
   * @return mixed
   *   The returned config.
   */
  public function getConfig();

  /**
   * Gets shared theme items that can be altered within methods.
   *
   * @return array
   *   The theme array.
   */
  public function getThemeSettings($marketo_settings);

  /**
   * Get success page url.
   *
   * @return string
   *   URL to marketo success page.
   */
  public function getSuccessUrl($marketo_settings);

  /**
   * Method to get current route.
   *
   * @return string|null
   *   Current route or nothing.
   */
  public function getCurrentRoute();

  /**
   * Method to get form by route.
   *
   * @return string
   *   Current form id.
   */
  public function getFormByRoute();

  /**
   * Method to get language.
   *
   * @return string
   *   Current language.
   */
  public function getCurrentLanguage();

  /**
   * Method to get a language manager.
   *
   * @return \Drupal\Core\Language\LanguageManagerInterface
   *   Language manager.
   */
  public function getLanguageManager();

}

<?php

namespace Drupal\ul_crc_asset;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Config\Config;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Theme\Registry;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;

/**
 * View builder handler for CRC Assets.
 */
class CRCAssetViewBuilder extends EntityViewBuilder {

  /**
   * Constructs a new FeedViewBuilder.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Theme\Registry $theme_registry
   *   The theme registry.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   * @param \Drupal\Core\Config\Config $config
   *   The 'aggregator.settings' config.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityRepositoryInterface $entity_manager, LanguageManagerInterface $language_manager, Registry $theme_registry, EntityDisplayRepositoryInterface $entity_display_repository, Config $config) {
    parent::__construct($entity_type, $entity_manager, $language_manager, $theme_registry, $entity_display_repository);
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.repository'),
      $container->get('language_manager'),
      $container->get('theme.registry'),
      $container->get('entity_display.repository'),
      $container->get('config.factory')->get('ul_crc_asset.settings')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {
    $render = [];
    $langcode = isset($langcode) ? $langcode : $entity->getCrcLanguage();
    // Fetching data from CRC or Cache.
    $crc = \Drupal::service('ul_crc')->getAsset($entity->getCrcId(), $langcode);

    // Note: We store the ID and filename as entity data but we view it from
    // the cached service data to ensure we have the most up to date
    // information.
    if (!empty($crc['data'])) {
      $data = $crc['data'];
      $langcode = $data['available_languages'][0];
      // Small thumbnail.
      $img_placehoder_sm = $this->getImgPlaceholder($data['sm_thumbnail_url']);
      // Medium thumbnail.
      $img_placehoder_med = $this->getImgPlaceholder($data['med_thumbnail_url']);
      $render = [
        '#theme' => 'ul_crc_asset',
        '#id' => $data['id'],
        '#content' => [
          'id' => ['#markup' => $data['id']],
          'name' => ['#markup' => $data['name']],
          'description' => ['#markup' => $data['description']],
          'file_extension' => ['#markup' => $data['file_extension']],
          'created_at' => ['#markup' => $data['created_at']],
          'updated_at' => ['#markup' => $data['updated_at']],
          'file_type' => ['#markup' => $data['file_type']],
          'sm_thumbnail_url' => ['#markup' => $img_placehoder_sm],
          'med_thumbnail_url' => ['#markup' => $img_placehoder_med],
          'original_url' => ['#markup' => $data['original_url']],
          'language' => ['#markup' => $langcode],
        ],
      ];
    }

    return $render;
  }

  /**
   * Get a placeholder image if the file doesn't exit or not an image.
   *
   * @param string $file
   *   The thumbail image URL.
   *
   * @return string
   *   The default placehoder image URL.
   */
  protected function getImgPlaceholder($file) {
    // The file exists and the file is an image.
    if (isset($file) && $size = getimagesize($file)) {
      // Valid image types.
      $types = [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP];
      if (in_array($size[2], $types)) {
        return $file;
      }
    }
    return "https://crc.ul.com/file_placeholder-01.png";
  }

}

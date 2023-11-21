<?php

namespace Drupal\ul_crc_asset\Plugin\media\Source;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\media\MediaSourceBase;
use Drupal\media\MediaTypeInterface;
use Drupal\media\MediaInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\ul_crc_asset\Entity\CRCAsset;

/**
 * Media source wrapping around a CRC Asset.
 *
 * @todo A fatal error is triggered when first creating the media bundle.
 *
 * @MediaSource(
 *   id = "crc_asset",
 *   label = @Translation("CRC Asset"),
 *   description = @Translation("Use crc assets for reusable media."),
 *   allowed_field_types = {"crc_asset_item"},
 *   default_thumbnail_filename = "generic.png"
 * )
 */
class CRCAssetMediaSource extends MediaSourceBase {

  /**
   * {@inheritdoc}
   */
  public function getMetadataAttributes() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function createSourceField(MediaTypeInterface $type) {
    return parent::createSourceField($type)
      ->set('settings', ['file_extensions' => 'jpg png']);
  }

  /**
   * {@inheritdoc}
   */
  public function prepareViewDisplay(MediaTypeInterface $type, EntityViewDisplayInterface $display) {
    $display->setComponent($this->getSourceFieldDefinition($type)->getName(), [
      'type' => 'crc_asset',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(MediaInterface $media, $name) {
    // Get the file and image data.
    $entity = $media->get($this->configuration['source_field'])->entity;

    switch ($name) {

      case 'default_name':
        return $entity->getCrcData('name');

      case 'thumbnail_uri':
        return $this->getThumbnail($entity);

      default:
        return parent::getMetadata($media, $name);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getThumbnail(CRCAsset $entity) {

    // When used in media we need to download the thumbnail because
    // media doesn't work without it and the amazon cached image
    // url is too long.
    $thumbnail = $entity->getCrcData('med_thumbnail_url');

    // Parse the file name of the original, we're going to use that name for
    // the thumbnail.
    $file = $entity->getCrcData('original_url');
    $url = UrlHelper::parse($file);
    $explode = explode('/', $url['path']);
    $filename = array_pop($explode);

    // Fetch the thumbnail and save it to the filesystem.
    // do not track when downloaded, it will be tracked later.
    $uri = system_retrieve_file($thumbnail, 'public://' . $filename, FALSE);

    return $uri;
  }

}

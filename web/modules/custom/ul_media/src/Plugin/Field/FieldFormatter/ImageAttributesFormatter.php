<?php

namespace Drupal\ul_media\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;

/**
 * Plugin implementation of the 'image_attributes' formatter.
 *
 * @FieldFormatter(
 *   id = "image_attributes",
 *   label = @Translation("Image Attributes"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class ImageAttributesFormatter extends ImageFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'image_style' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    unset($element['image_link']);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    return [$summary[0]];
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    /** @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $items */
    if (empty($images = $this->getEntitiesToView($items, $langcode))) {
      // Early opt-out if the field is empty.
      return $elements;
    }

    /** @var \Drupal\image\ImageStyleInterface $image_style */
    $image_style = $this->imageStyleStorage->load($this->getSetting('image_style'));
    /** @var \Drupal\file\FileInterface[] $images */
    foreach ($images as $delta => $image) {
      $image_uri = $image->getFileUri();
      $url = $image_style ? $image_style->buildUrl($image_uri) : \Drupal::service('file_url_generator')->generateString($image_uri);
      // Add cacheability metadata from the image and image style.
      $cacheability = CacheableMetadata::createFromObject($image);
      if ($image_style) {
        $cacheability->addCacheableDependency(CacheableMetadata::createFromObject($image_style));
      }

      // Load full image data from item.
      $field_data = $items->getValue();
      foreach ($field_data as $attrib) {
        if ($attrib['target_id'] == $image->id()) {
          $elements[$delta] = [
            'src' => ['#markup' => $url],
            'alt' => ['#markup' => $attrib['alt']],
            'title' => ['#markup' => $attrib['title']],
          ];
        }
      }

      $cacheability->applyTo($elements[$delta]);
    }
    return $elements;
  }

}

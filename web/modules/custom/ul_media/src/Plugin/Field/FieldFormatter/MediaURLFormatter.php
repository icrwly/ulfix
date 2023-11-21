<?php

namespace Drupal\ul_media\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\media\Plugin\Field\FieldFormatter\MediaThumbnailFormatter;
use Drupal\image\Entity\ImageStyle;

/**
 * Plugin implementation of the 'media_url' formatter.
 *
 * @FieldFormatter(
 *   id = "media_url",
 *   label = @Translation("URL"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class MediaURLFormatter extends MediaThumbnailFormatter {

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('current_user'),
      $container->get('entity_type.manager')->getStorage('image_style'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();
    $settings['alt_tag'] = TRUE;
    $settings['title'] = FALSE;
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    unset($element['image_link']);

    $element['alt_tag'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Return Alt Tag'),
      '#default_value' => $this->getSetting('alt_tag'),
    ];
    $element['title'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Return Title'),
      '#default_value' => $this->getSetting('title'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $media = $this->getEntitiesToView($items, $langcode);

    // Early opt-out if the field is empty.
    if (empty($media)) {
      return $elements;
    }

    // Get the image style.
    $image_style_setting = $this->getSetting('image_style');

    // Loop through the field items.
    /** @var \Drupal\media\MediaInterface $media_item */
    foreach ($media as $delta => $media_item) {
      // Check if this is a media image entity (which has a required alt tag).
      if ($media_item->hasField('field_media_image')) {
        $image = $media_item->get('field_media_image');
      }
      else {
        $image = $media_item->get('thumbnail');
      }

      // Get the file.
      if ($image && $image->entity) {

        $uri = $image->entity->getFileUri();

        // Get the url for the image style.
        if (!empty($image_style_setting)) {
          $url = ImageStyle::load($image_style_setting)->buildUrl($uri);
        }
        else {
          $wrapper = \Drupal::service('stream_wrapper_manager')
            ->getViaUri($uri);
          $url = $wrapper->getExternalUrl();
        }
        // The values will be used directly in templates,
        // so just return them as pure markup.
        $elements[$delta] = [
          'url' => ['#markup' => $url],
        ];

        if ($this->getSetting('alt_tag')) {
          // Get the alt tag.
          $alt = '';
          if (!empty($image->get(0)->getValue()['alt'])) {
            $alt = $image->get(0)->getValue()['alt'];
          }
          $elements[$delta]['alt'] = ['#markup' => $alt];
        }

        if ($this->getSetting('title')) {
          // Get the title.
          $title = '';
          if (!empty($image->get(0)->getValue()['title'])) {
            $title = $image->get(0)->getValue()['title'];
          }
          $elements[$delta]['title'] = ['#markup' => $title];
        }

        // @todo This formatter does not accommodate for non-image entities.
        // Collect cache tags to be added for each item in the field.
        $this->renderer->addCacheableDependency($elements[$delta], $media_item);
      }
    }

    // Collect cache tags related to the image style setting.
    $image_style = $this->imageStyleStorage->load($image_style_setting);
    $this->renderer->addCacheableDependency($elements, $image_style);

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    // This formatter is only available for entity types that reference
    // image media items. However, in some cases the target bundle is not
    // passed (such as with views) so we can bypass image restriction.
    if (parent::isApplicable($field_definition)) {
      $settings = $field_definition->getSetting('handler_settings');

      if (empty($settings) || (!empty($settings) && in_array('image', $settings['target_bundles']))) {
        return TRUE;
      }
    }
    return FALSE;
  }

}

<?php

namespace Drupal\ul_media\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\Core\StreamWrapper\StreamWrapperManager;

/**
 * Plugin implementation of the 'media_url' formatter.
 *
 * @FieldFormatter(
 *   id = "media_file_url",
 *   label = @Translation("URL"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class MediaFileUrlFormatter extends EntityReferenceFormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The Stream Wrapper Manager service.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface
   */
  protected $streamWrapperManager;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, StreamWrapperManager $stream_wrapper_manager) {
    parent:: __construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->streamWrapperManager = $stream_wrapper_manager;

  }

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
      $container->get('stream_wrapper_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();
    $settings['properties'] = [
      'label' => FALSE,
      'changed' => FALSE,
      'filesize' => FALSE,
    ];
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['properties'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Render additional properties'),
      '#default_value' => $this->getSetting('properties'),
      '#options' => [
        'label' => $this->t('Label'),
        'changed' => $this->t('Changed'),
        'filesize' => $this->t('Filesize'),
      ],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $properties = $this->getSetting('properties');
    $properties = array_filter($properties);
    if (!empty($properties)) {
      $summary[] = $this->t('Properties:') . ' ' . implode(', ', $properties);
    }
    return $summary;
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

    // Loop through the field items.
    /** @var \Drupal\media\MediaInterface $media_item */
    foreach ($media as $delta => $media_item) {
      $file = $media_item->get('field_media_file');

      // Get the file.
      $uri = $file->entity->getFileUri();

      $wrapper = $this->streamWrapperManager->getViaUri($uri);
      // $wrapper = \Drupal::service('stream_wrapper_manager')->getViaUri($uri);
      $url = $wrapper->getExternalUrl();

      // The values will be used directly in templates,
      // so just return them as pure markup.
      $elements[$delta] = [
        'url' => ['#markup' => $url],
      ];

      // Additional properties.
      $properties = $this->getSetting('properties');
      foreach ($properties as $key => $property) {
        if ($property) {
          switch ($key) {
            case 'label':
              $elements[$delta]['label'] = $media_item->label();
              break;

            case 'filesize':
              $elements[$delta]['filesize'] = format_size($file->entity->getSize());
              break;

            case 'changed':
              $elements[$delta]['changed'] = $file->entity->getChangedTime();
              break;
          }
        }
      }
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    // This formatter is only available for entity types that reference
    // file media items.
    if (parent::isApplicable($field_definition)) {
      $settings = $field_definition->getSetting('handler_settings');
      if (isset($settings['target_bundles']) && in_array('file', $settings['target_bundles'])) {
        return TRUE;
      }
    }
    return FALSE;
  }

}

<?php

namespace Drupal\ul_crc_asset\Plugin\views\field;

use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * A field that displays data from UL CRC Service.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("ul_crc_asset")
 */
class CRCAssetField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['render_image'] = [
      'default' => NULL,
    ];
    $options['width'] = [
      'default' => NULL,
    ];
    $options['height'] = [
      'default' => NULL,
    ];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['render_image'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Render as Image'),
      '#default_value' => $this->options['render_image'],
      '#description' => $this->t('Render the thumbnail as an image.'),
    ];
    $form['width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Width'),
      '#default_value' => $this->options['width'],
      '#description' => $this->t('Set the width of this image. Leave blank to use native width.'),
      '#states' => [
        'visible' => [
          ':input[name="options[render_image]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Height'),
      '#default_value' => $this->options['height'],
      '#description' => $this->t('Set the height of this image. Leave blank to use the native height.'),
      '#states' => [
        'visible' => [
          ':input[name="options[render_image]"]' => ['checked' => TRUE],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $value = $this->getValue($values);
    // @todo Make this a custom theme?
    // Drupal only supports image_style theme which does not work with remote
    // images. Just passing the URL to a simple image tag.
    if ($this->options['render_image']) {
      return [
        '#markup' => '<img src="' . $value . '" width="' . $this->options['width'] . '" height="' . $this->options['height'] . '" alt="Thumbnail">',
      ];
    }

    return $value;
  }

}

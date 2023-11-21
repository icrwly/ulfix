<?php

namespace Drupal\ul_guidelines_zendesk\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a block for the Zendesk widget.
 *
 * @Block(
 *   id = "ul_guidelines_zendesk_block",
 *   admin_label = @Translation("Zendesk Widget"),
 * )
 */
class ULGuidelinesZendeskFormBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $config = $this->getConfiguration();

    if (empty($config['zendesk_widget_url'])) {
      return [];
    }

    return [
      'zendesk_url' => $config['zendesk_widget_url'],
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['zendesk_widget_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Zendesk URL'),
      '#description' => $this->t('The Zendesk property you would like to use.'),
      '#default_value' => isset($config['zendesk_widget_url']) ? $config['zendesk_widget_url'] : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['zendesk_widget_url'] = $values['zendesk_widget_url'];
  }

}

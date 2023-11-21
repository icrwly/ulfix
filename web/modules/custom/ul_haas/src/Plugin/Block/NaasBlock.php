<?php

namespace Drupal\ul_haas\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Generates a Navigation as a Service block.
 *
 * @Block(
 *   id = "ul_haas_naas",
 *   admin_label = @Translation("Navigation as a service"),
 *   category = @Translation("UL header services"),
 * )
 */
class NaasBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['naas'] = [
      '#type' => 'details',
      '#title' => $this->t('Navigation Settings'),
      '#open' => TRUE,
      '#description' => $this->t('Settings for the Navigation service.'),
    ];

    $form['naas']['naas_careers'] = [
      '#title' => $this->t('Careers Link URL'),
      '#type' => 'textfield',
      '#default_value' => $config['naas_careers'] ?? NULL,
      '#description' => $this->t('You can enter an external URL such as %url.', [
        '%url' => 'http://example.com',
      ]),
    ];

    $form['naas']['naas_contact'] = [
      '#title' => $this->t('Contact Link URL'),
      '#type' => 'textfield',
      '#default_value' => $config['naas_contact'] ?? NULL,
      '#description' => $this->t('You can enter an external URL such as %url.', [
        '%url' => 'http://example.com',
      ]),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    $values = $form_state->getValues();

    $this->configuration['naas_careers'] = $values['naas']['naas_careers'];
    $this->configuration['naas_contact'] = $values['naas']['naas_contact'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $default_links = [
      'default_ul' => [
        [
          '#type' => 'link',
          '#title' => $this->t('Explore UL'),
          '#url' => 'https://www.ul.com/',
        ],
      ],
      'default_login' => [
        [
          '#type' => 'link',
          '#title' => $this->t('Login'),
          '#url' => 'https://www.ultesttools.com/login',
        ],
      ],
    ];

    $build_careers_link = [];
    $build_contact_link = [];

    if (!empty($this->configuration['naas_careers'])) {
      $build_careers_link = [
        '#type' => 'link',
        '#title' => $this->t('Careers'),
        '#url' => Url::fromUri($this->configuration['naas_careers']),
      ];
    }

    if (!empty($this->configuration['naas_contact'])) {
      $build_contact_link = [
        '#type' => 'link',
        '#title' => $this->t('Contact'),
        '#url' => Url::fromUri($this->configuration['naas_contact']),
      ];
    }

    return [
      '#theme' => 'haas_naas',
      '#ul_home' => $default_links['default_ul'],
      '#careers' => $build_careers_link,
      '#contact' => $build_contact_link,
      '#ul_login' => $default_links['default_login'],
    ];
  }

}

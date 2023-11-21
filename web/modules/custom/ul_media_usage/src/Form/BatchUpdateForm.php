<?php

namespace Drupal\ul_media_usage\Form;

use Drupal\ul_media_usage\EntityUsageBatchManager;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to launch batch tracking of existing entities.
 */
class BatchUpdateForm extends FormBase {

  /**
   * The EntityUsageBatchManager service.
   *
   * @var \Drupal\ul_media_usage\EntityUsageBatchManager
   */
  protected $batchManager;

  /**
   * BatchUpdateForm constructor.
   *
   * @param \Drupal\ul_media_usage\EntityUsageBatchManager $batch_manager
   *   The entity usage batch manager.
   */
  public function __construct(EntityUsageBatchManager $batch_manager) {
    $this->batchManager = $batch_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ul_media_usage.batch_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_update_batch_update_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description'] = [
      '#markup' => $this->t("<h3>This page allows you to Generate/Refesh Media entity usage statistics.</h3><br>"),
    ];
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Refresh Media entity usage data'),
    ];

    $form['button'] = [
      '#markup' => $this->t('<br><a href="/admin/content/ul-media-usage" class="button">&larr; Back to UL Media Usage</a>'),
      '#weight' => 1000,
      '#class' => 'button-secondary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->batchManager->recreate(1);
  }

}

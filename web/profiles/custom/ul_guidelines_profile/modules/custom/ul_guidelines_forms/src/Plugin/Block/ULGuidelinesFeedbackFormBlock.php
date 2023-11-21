<?php

namespace Drupal\ul_guidelines_forms\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block with the Feedback form (contact form).
 *
 * @Block(
 *   id = "ul_guidelines_feedback_form_block",
 *   admin_label = @Translation("Feedback Form Block"),
 * )
 */
class ULGuidelinesFeedbackFormBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $message = \Drupal::entityTypeManager()
      ->getStorage('contact_message')
      ->create(array(
        'contact_form' => 'feedback',
      ));
    $form = \Drupal::service('entity.form_builder')->getForm($message);

    return $form;
  }
}

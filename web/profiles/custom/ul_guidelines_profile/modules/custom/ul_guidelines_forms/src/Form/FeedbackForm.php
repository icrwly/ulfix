<?php

namespace Drupal\ul_guidelines_forms\Form;

use Drupal\Core\Form\FormStateInterface;

class FeedbackForm {

  /**
   * Alter the feedback form.
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function FormAlter(array &$form, FormStateInterface $form_state) {
    // This form is coming from the Contact module.
    // We need to do some adjustments to fit it with our need.

    // Unset preview button.
    unset($form['actions']['preview']);

    // Change submit button's label.
    $form['actions']['submit']['#value'] = t('Submit');
  }
}

<?php

namespace Drupal\ul_marketo\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Helper class to autosave the form.
 *
 * @package Drupal\ul_marketo\Form
 */
class FormAlter {

  /**
   * Function to autosave the form to get an ID.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public static function autoSave(array &$form, FormStateInterface $form_state) {
    $node = $form_state->getFormObject()->getEntity();
    $triggering_element = $form_state->getTriggeringElement();

    $node->set($triggering_element, $triggering_element['#value']);
    $node->save();

  }

}

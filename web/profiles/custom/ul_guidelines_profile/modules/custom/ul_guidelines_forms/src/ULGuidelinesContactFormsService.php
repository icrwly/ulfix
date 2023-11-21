<?php

namespace Drupal\ul_guidelines_forms;

use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\file\Entity\File;
use stdClass;

class ULGuidelinesContactFormsService {

  /**
   * FormAlter factory method.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param $form_id
   */
  public function formAlter(array &$form, FormStateInterface $form_state, $form_id) {
    // TODO: Drupal has a better way to do this. Perhaps class_resolver?
    $form_name =  __NAMESPACE__ . '\\Form\\' . $this->getFormClassName($form_id);
    $alter_method = 'formAlter';
    // check if form class exists.
    if (class_exists($form_name)) {
      $form_obj = new $form_name();
      if (method_exists($form_obj, $alter_method)) {
        $form_obj->$alter_method($form, $form_state);
      }
    }
  }

  /**
   * MailAlter factory method.
   *
   * @param array $message
   * @param $form_id
   */
  public function mailAlter(array &$message, $form_id) {
    $form_name =  __NAMESPACE__ . '\\Form\\' . $this->getFormClassName($form_id);
    $alter_method = 'mailAlter';
    // check if form class exists.
    if (class_exists($form_name)) {
      $form_obj = new $form_name();
      if (method_exists($form_obj, $alter_method)) {
        $form_obj->$alter_method($message);
      }
    }
  }

  /**
   * Generate a CamelCase version for a contact form name.
   *
   * @param string $form_id
   *   Original form id.
   *
   * @return string
   *   formatted camelcase form id.
   */
  private function getFormClassName($form_id) {
    // Remove 'contact_message_';
    $form_name = str_replace('contact_message_', '', $form_id);

    // Upper case first letter of all words.
    $form_name = explode('_', $form_name);
    for($w = 0; $w < count($form_name); $w++) {
      $form_name[$w] = ucwords($form_name[$w]);
    }

    // Make sure that 'Form' is in the name.
    if (!in_array('Form', $form_name)) {
      $form_name[] = 'Form';
    }
    // Implode
    $form_name = implode('', $form_name);

    return $form_name;
  }
}
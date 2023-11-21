<?php

namespace Drupal\ul_salesforce\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;

/**
 * @file
 * Contains \Drupal\ul_salesforce\Plugin\Block\ContactusBlock.
 */

/**
 * Provides a 'contactus form' block.
 *
 * @Block(
 *   id = "contactus_form_block",
 *   admin_label = @Translation("Contact Us form block"),
 *   category = @Translation("Custom")
 * )
 */
class ContactusBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $form = \Drupal::formBuilder()->getForm('Drupal\ul_salesforce\Form\ContactUsForm');

    return $form;

  }

}

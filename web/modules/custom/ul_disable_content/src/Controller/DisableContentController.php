<?php

namespace Drupal\ul_disable_content\Controller;

/**
 * Provide Disabled Content Type error message.
 */
class DisableContentController {

  /**
   * {@inheritdoc}
   */
  public function disabled() {

    $mssg = '<h2>Sorry!</h2>';
    $mssg .= '<p>This content type has been disabled. It is no longer possible to add new pages using this content type.</p>';
    $mssg .= '<p><a href="/node/add">Please go back and select another one</a>.</p>';

    return [
      '#markup' => $mssg,
    ];
  }

}

<?php

namespace Drupal\ul_base_profile\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted source does not have a trailing slash.
 *
 * @Constraint(
 *   id = "RedirectSource",
 *   label = @Translation("Redirect Source", context = "Validation"),
 * )
 */
class RedirectSourceConstraint extends Constraint {

  /**
   * The message that will be shown if the format is incorrect.
   *
   * @var string
   */
  public $incorrectSourceFormat = 'The source URL must not have a trailing slash.';

}

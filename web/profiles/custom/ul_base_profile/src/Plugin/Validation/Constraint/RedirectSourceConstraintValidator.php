<?php

namespace Drupal\ul_base_profile\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the "no trailing slash" constraint.
 */
class RedirectSourceConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    $item = $items->first();
    if ($item && substr($item->get('path')->getValue(), -1) === '/') {
      $this->context->addViolation($constraint->incorrectSourceFormat);
    }
  }

}

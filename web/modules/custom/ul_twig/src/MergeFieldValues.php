<?php

namespace Drupal\ul_twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * A class that merges values from multiple fields into a single array.
 *
 * @see https://github.com/fabpot/Twig-extensions
 */
class MergeFieldValues extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'merge_field_terms';
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('merge_field_values',
          [$this, 'mergeFieldValueArray']
      ),
    ];
  }

  /**
   * Merge field values.
   *
   * This function fetches all values in the fields and consolidates them
   * into a single array.
   *
   * @param array $field_array
   *   Array of fields that you want to combine.
   *
   * @return array
   *   Returns an array of values.
   */
  public function mergeFieldValueArray(array $field_array = []) {
    $items = [];
    if (!empty($field_array)) {
      foreach ($field_array as $field) {
        if (is_array($field)) {
          foreach ($field as $index => $value) {
            if (is_numeric($index)) {
              $items[] = $value;
            }
          }
        }
      }
    }
    return $items;
  }

}

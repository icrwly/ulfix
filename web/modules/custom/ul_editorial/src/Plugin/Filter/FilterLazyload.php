<?php

namespace Drupal\ul_editorial\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a filter to lazy-load inline images.
 *
 * @Filter(
 *   id = "filter_lazyload",
 *   title = @Translation("Lazyload images"),
 *   description = @Translation("Lazy load inline images"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE,
 *   weight = 20
 * )
 *
 * The filter plugin should be FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * type to avoid making changes to node content. That way in CKEditor the
 * images could be maintained same as before. But when they are viewed in
 * context of the page, they will be lazy-loaded.
 */
class FilterLazyload extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {

    $result = new FilterProcessResult($text);
    $html_dom = Html::load($text);
    $images = $html_dom->getElementsByTagName('img');

    foreach ($images as $image) {
      $src = $image->getAttribute('src');
      $image->removeAttribute('src');
      $image->setAttribute('data-src', $src);
      $classes = $image->getAttribute('class');
      $classes = (strlen($classes) > 0) ? explode(' ', $classes) : [];
      $classes[] = 'lazy';
      $image->setAttribute('class', implode(' ', $classes));
    }

    $result->setProcessedText(Html::serialize($html_dom));

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    return $this->t('All inline-images will be lazy-loaded.');
  }

}

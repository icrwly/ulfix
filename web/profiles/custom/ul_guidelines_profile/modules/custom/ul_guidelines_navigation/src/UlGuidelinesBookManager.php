<?php

namespace Drupal\ul_guidelines_navigation;

use Drupal\book\BookManager;

/**
 * Defines a book manager.
 */
class UlGuidelinesBookManager extends BookManager {

  /**
   * {@inheritdoc}
   */
  public function bookTreeCheckAccess(&$tree, $node_links = []) {
    // Overriding the bookTreeAccess method so that we can see the book
    // as an anonymous user while logged out during share.
    if ($node_links) {
      // @todo Extract that into its own method.
      $nids = array_keys($node_links);

      // @todo This should be actually filtering on the desired node status
      //   field language and just fall back to the default language.
      $nids = \Drupal::entityQuery('node')
        ->condition('nid', $nids, 'IN')
        ->condition('status', 1)
        ->accessCheck(FALSE)
        ->execute();

      foreach ($nids as $nid) {
        foreach ($node_links[$nid] as $mlid => $link) {
          $node_links[$nid][$mlid]['access'] = TRUE;
        }
      }
    }
    $this->doBookTreeCheckAccess($tree);
  }

}

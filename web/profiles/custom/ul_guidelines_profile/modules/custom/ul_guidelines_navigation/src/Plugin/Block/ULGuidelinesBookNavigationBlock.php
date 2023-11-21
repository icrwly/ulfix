<?php

namespace Drupal\ul_guidelines_navigation\Plugin\Block;

use Drupal\book\Plugin\Block\BookNavigationBlock;
use Drupal\node\NodeInterface;
use Drupal\Core\Cache\Cache;

/**
 * Provides a 'Book navigation' block.
 *
 * @Block(
 *   id = "ul_guidelines_book_navigation",
 *   admin_label = @Translation("UL Guidelines Book navigation"),
 *   category = @Translation("Menus")
 * )
 */
class ULGuidelinesBookNavigationBlock extends BookNavigationBlock {

  /**
   * {@inheritdoc}
   *
   * Note: This function is a direct copy of BookNavigationBlock::build() with
   * some minor changes in order to render the first element in the book.
   */
  public function build() {
    $current_bid = 0;

    $node = $this->routeMatch->getParameter('node');
    if ($node instanceof NodeInterface && !empty($node->book['bid'])) {
      $current_bid = $node->book['bid'];
    }
    if ($this->configuration['block_mode'] == 'all pages') {
      $book_menus = [];
      $pseudo_tree = [0 => ['below' => FALSE]];
      foreach ($this->bookManager->getAllBooks() as $book_id => $book) {
        if ($book['bid'] == $current_bid) {
          // If the current page is a node associated with a book, the menu
          // needs to be retrieved.
          $data = $this->bookManager->bookTreeAllData($node->book['bid'], $node->book);
          $book_menus[$book_id] = $this->bookManager->bookTreeOutput($data);
        }
        else {
          // Since we know we will only display a link to the top node, there
          // is no reason to run an additional menu tree query for each book.
          $book['in_active_trail'] = FALSE;
          // Check whether user can access the book link.
          $book_node = $this->nodeStorage->load($book['nid']);
          $book['access'] = $book_node->access('view');
          $pseudo_tree[0]['link'] = $book;
          $book_menus[$book_id] = $this->bookManager->bookTreeOutput($pseudo_tree);
        }
        $book_menus[$book_id] += [
          '#book_title' => $book['title'],
        ];
      }
      if ($book_menus) {
        return [
          '#theme' => 'book_all_books_block',
        ] + $book_menus;
      }
    }
    elseif ($current_bid) {
      // Only display this block when the user is browsing a book and do
      // not show unpublished books.
      $nid = \Drupal::entityQuery('node')
        ->condition('nid', $node->book['bid'], '=')
        ->condition('status', NodeInterface::PUBLISHED)
        ->accessCheck(FALSE)
        ->execute();

      // Only show the block if the user has view access for the top-level node.
      if ($nid && $node->access('view')) {
        // Note: This conditional is the only change that we've made to this
        // function. See BookNavigationBlock::build().
        // Grab the active trail tree.
        $active_tree = $this->bookManager->bookTreeAllData($node->book['bid'], $node->book);

        // Next grab the full tree and check against the active tree.
        // If it is active then pass a custom 'active' and 'current' flags.
        // We pass this flag because books does some default behavior with
        // in_active_trail that we don't want.
        $tree = $this->bookManager->bookTreeAllData($node->book['bid']);

        foreach ($active_tree as $key => $active_book) {
          $this->flagActiveMenuItems($tree[$key], $active_book, $node->id());
        }

        // Update access for all tree items.
        foreach ($tree as &$tree_item) {
          $this->checkItemAccess($tree_item);
        }

        return $this->bookManager->bookTreeOutput($tree);
      }
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['url', 'url.query_args']);
  }

  /**
   * Flag active menu items.
   *
   * @param array $tree_item
   *   The tree item.
   * @param array $active_item
   *   The active tree item.
   * @param int $nid
   *   The current active node.
   */
  private function flagActiveMenuItems(array &$tree_item, array $active_item, $nid) {
    $node = $this->nodeStorage->load($tree_item['link']['nid']);
    // Check to see if user has access to view this node.
    if ($node->access('view')) {
      // Flag this as in the active trail.
      if ($active_item['link']['in_active_trail']) {
        $tree_item['link']['active'] = TRUE;
      }

      // Flag this as the currently viewed item.
      if ($active_item['link']['in_active_trail'] && $tree_item['link']['nid'] == $nid) {
        $tree_item['link']['current'] = TRUE;
      }

      if (!empty($active_item['below'])) {
        foreach ($active_item['below'] as $bid => $book) {
          $this->flagActiveMenuItems($tree_item['below'][$bid], $book, $nid);
        }
      }
    }
    // No access, so remove it.
    else {
      $tree_item['link']['access'] = FALSE;
    }
  }

  /**
   * Checks and updates the tree item access for a user.
   *
   * @param array $tree_item
   *   Book tree item.
   */
  private function checkItemAccess(array &$tree_item) {
    $node = $this->nodeStorage->load($tree_item['link']['nid']);
    // Check to see if user has access to view this node.
    if ($node->access('view')) {
      if (!empty($tree_item['below'])) {
        foreach ($tree_item['below'] as $bid => $book) {
          $this->checkItemAccess($tree_item['below'][$bid]);
        }
      }
    }
    else {
      $tree_item['link']['access'] = FALSE;
    }
  }

}

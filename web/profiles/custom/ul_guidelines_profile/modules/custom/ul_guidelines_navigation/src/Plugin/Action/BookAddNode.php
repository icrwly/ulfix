<?php

namespace Drupal\ul_guidelines_navigation\Plugin\Action;

use Drupal\book\BookManagerInterface;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add node to book.
 *
 * @Action(
 *   id = "book_add_node_action",
 *   label = @Translation("Add content to a book"),
 *   type = "node"
 * )
 */
class BookAddNode extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new BookAddNode object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user, BookManagerInterface $bookManager) {

    $this->currentUser = $current_user;
    $this->bookManager = $bookManager;

    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('book.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {

    $bid = $this->configuration['bid'];

    $book = [
      'nid' => $entity->id(),
      'has_children' => 0,
      'original_bid' => 0,
      'parent_depth_limit' => 8,
      'pid' => $bid,
      'weight' => '0',
      'bid' => $bid,
    ];

    // If this content doesn't have a book then create one.
    if (empty($entity->book)) {
      $entity->book = $book;
      $this->bookManager->updateOutline($entity);
    }
    // Otherwise update the existing book with a new parent.
    // Do not update it if it's the same parent.
    elseif ($entity->book['bid'] != $bid && $entity->book['pid'] != $bid) {
      $book['original_bid'] = $entity->book['bid'];
      $entity->book = $book;
      $this->bookManager->updateOutline($entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $key = $object->getEntityType()->getKey('published');

    /** @var \Drupal\Core\Entity\EntityInterface $object */
    $result = $object->access('update', $account, TRUE)
      ->andIf($object->$key->access('edit', $account, TRUE));

    return $return_as_object ? $result : $result->isAllowed();
  }

}

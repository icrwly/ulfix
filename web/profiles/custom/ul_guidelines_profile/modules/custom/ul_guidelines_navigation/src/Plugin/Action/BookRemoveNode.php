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
 *   id = "book_remove_node_action",
 *   label = @Translation("Remove content from a book"),
 *   type = "node"
 * )
 */
class BookRemoveNode extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * CurrentUser object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * BookManager object.
   *
   * @var \Drupal\book\BookManagerInterface;
   */
  protected $bookManager;

  /**
   * Constructs a new BookRemoveNode object.
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

    // If this content is in a book then remove it.
    // Note this will not delete any books that have children.
    if ($this->bookManager->checkNodeIsRemovable($entity)) {
      $this->bookManager->deleteFromBook($entity->id());
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

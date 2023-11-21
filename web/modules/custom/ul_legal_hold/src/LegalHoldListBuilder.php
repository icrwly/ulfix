<?php

namespace Drupal\ul_legal_hold;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Entity\Node;
use Drupal\ul_legal_hold\Entity\LegalHold;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a list controller for the legal hold entity type.
 */
class LegalHoldListBuilder extends EntityListBuilder {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The redirect destination service.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  protected $redirectDestination;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new LegalHoldListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $redirect_destination
   *   The redirect destination service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, DateFormatterInterface $date_formatter, RedirectDestinationInterface $redirect_destination, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($entity_type, $storage);
    $this->dateFormatter = $date_formatter;
    $this->redirectDestination = $redirect_destination;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('date.formatter'),
      $container->get('redirect.destination'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build['table'] = parent::render();

    $total = \Drupal::database()
      ->query('SELECT COUNT(*) FROM {ul_legal_hold}')
      ->fetchField();

    $build['summary']['#markup'] = $this->t('Total legal holds: @total', ['@total' => $total]);
    $build['#attributes'] = ['class' => 'legal-hold-table'];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['title'] = $this->t('Title/Description');
    $header['type'] = $this->t('Content Type');
    $header['held'] = $this->t('Held Revisions');
    $header['uid'] = $this->t('Author');
    $header['created'] = $this->t('Created');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $parent = $entity->getHeldContent();
    $title = $entity->getTitle();
    $type = $parent->getType();
    /** @var \Drupal\ul_legal_hold\Entity\LegalHold $entity */
    $row['id'] = $entity->id();
    $row['title'] = $title;
    $row['type'] = ucfirst($type);
    $row['held'] = $this->buildHeld($entity, $parent);
    $row['uid']['data'] = [
      '#theme' => 'username',
      '#account' => $entity->getOwner(),
    ];
    $row['created'] = $this->dateFormatter->format($entity->getCreatedTime(), 'short');
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    $destination = $this->redirectDestination->getAsArray();
    foreach ($operations as $key => $operation) {
      $operations[$key]['query'] = $destination;
    }
    return $operations;
  }

  /**
   * Undocumented function.
   *
   * @param \Drupal\ul_legal_hold\Entity\LegalHold $entity
   *   The hold.
   * @param \Drupal\node\Entity\Node $parent
   *   The hold.
   */
  protected function buildHeld(LegalHold $entity, Node $parent) {
    $vids = $entity->getHeldRevisions();
    $items = [];
    $revs = $this->entityTypeManager->getStorage('node')->loadMultipleRevisions($vids);
    // $revs = array_values($revs);
    foreach ($revs as $k => $rev) {
      $timestamp = $this->dateFormatter->format($rev->getRevisionCreationTime());
      $url = Url::fromRoute('entity.node.revision', [
        'node' => $parent->id(),
        'node_revision' => $k,
      ]);
      $log = $rev->get('revision_log')->getString() ?: 'No log message set.';
      $title = $timestamp . ' - ' . $log;
      $link = Link::fromTextAndUrl($title, $url);
      $link = $link->toRenderable();
      $items[] = $link;
    }
    $list = [
      'data' => [
        '#theme' => 'item_list',
        '#list_type' => 'ul',
        '#prefix' => '<div>' . $parent->getTitle() . '</div>',
        '#items' => $items,
        '#attributes' => ['class' => 'mylist'],
      ],
    ];
    return $list;
  }

}

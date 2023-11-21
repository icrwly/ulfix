<?php

namespace Drupal\ul_legal_hold\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\node\Entity\Node;
use Drupal\ul_legal_hold\Entity\LegalHold;
use Drupal\Component\Utility\Unicode;

/**
 * Returns responses for UL Legal Hold routes.
 */
class LegalHoldController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * Drupal\Core\Entity\EntityStorageInterface definition.
   *
   * @var Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityStorage;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructor function.
   *
   * @param Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   The renderer.
   */
  public function __construct(DateFormatter $date_formatter, RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager, Renderer $renderer) {
    $this->dateFormatter = $date_formatter;
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
    $this->entityStorage = $this->entityTypeManager->getStorage('ul_legal_hold');

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
      $container->get('renderer')
    );
  }

  /**
   * Builds the response.
   */
  public function build() {
    // Get all holds attached to this node.
    $holds = $this->holdsByNode();
    $node = $this->getNode();

    $build['#title'] = $this->t('Legal Holds for %title', [
      '%title' => $node->label(),
    ]);
    $build['table'] = [
      '#type' => 'table',
      '#header' => $this->buildHeader(),
      '#title' => $this->t('Legal Holds Attached to This Content'),
      '#rows' => [],
      '#empty' => $this->t('There are no Legal Holds yet.'),
    ];
    $build['table']['#attributes'] = ['class' => ['ul-hold-table']];
    $build['table']['#attached']['library'][] = 'ul_legal_hold/ul_legal_hold';
    foreach ($holds as $entity) {
      if ($row = $this->buildRow($entity)) {
        $build['table']['#rows'][$entity->id()] = $row;
      }
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['title'] = $this->t('Title');
    $header['held'] = $this->t('Held Revisions');
    $header['description'] = $this->t('Description');
    $header['uid'] = $this->t('Author');
    $header['created'] = $this->t('Created');
    $header['operations'] = $this->t('Operations');
    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\ul_legal_hold\Entity\LegalHold $entity */
    $parent = $entity->getHeldContent();
    $title = $entity->getTitle();
    $description = $entity->getDescription();
    $description['#text'] = '<p>' . Unicode::truncate(strip_tags($description['#text']), 200) . '...</p>';
    $row['id'] = $entity->id();
    $row['title'] = $title;
    $row['held'] = $this->buildHeld($entity, $parent);
    $row['description'] = $this->renderer->render($description);
    $row['uid']['data'] = [
      '#theme' => 'username',
      '#account' => $entity->getOwner(),
    ];
    $row['created'] = $this->dateFormatter->format($entity->getCreatedTime(), 'short') . ' ' . date_default_timezone_get();
    $row['operations']['data'] = $this->buildOperations($entity);
    return $row;
  }

  /**
   * Builds a renderable list of operation links for the entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity on which the linked operations will be performed.
   *
   * @return array
   *   A renderable array of operation links.
   *
   * @see \Drupal\Core\Entity\EntityListBuilder::buildRow()
   */
  public function buildOperations(EntityInterface $entity) {
    $build = [
      '#type' => 'operations',
      '#links' => $this->getDefaultOperations($entity),
    ];
    return $build;
  }

  /**
   * Gets this list's default operations.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity the operations are for.
   *
   * @return array
   *   The array structure is identical to the return value of
   *   self::getOperations().
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = [];
    if ($entity->access('update') && $entity->hasLinkTemplate('edit-form')) {
      $operations['edit'] = [
        'title' => $this->t('Edit'),
        'weight' => 10,
        'url' => $entity->toUrl('edit-form', []),
      ];
    }
    if ($entity->access('delete') && $entity->hasLinkTemplate('delete-form')) {
      $operations['delete'] = [
        'title' => $this->t('Delete'),
        'weight' => 100,
        'url' => $entity->toUrl('delete-form', []),
      ];
    }
    return $operations;
  }

  /**
   * Builds the query and returns the holds.
   */
  public function holdsByNode() {
    $current_node = $this->getNode();
    $nid = $current_node->id();
    $query = $this->entityStorage->getQuery();
    $query->condition('held_content', $nid);
    $entity_ids = $query->execute();

    $holds = $this->entityTypeManager->getStorage('ul_legal_hold')->loadMultiple($entity_ids);

    return $holds;

  }

  /**
   * Returns current route node.
   *
   * @return \Drupal\node\Entity\NodeInterface
   *   The node.
   */
  protected function getNode() {
    $crm = $this->routeMatch;
    $node = $crm->getParameter('node');
    if ($node instanceof NodeInterface) {
      return $node;
    }
    else {
      $node = $this->entityTypeManager->getStorage('node')->load($node);
      return $node;
    }
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
        '#items' => $items,
        '#attributes' => ['class' => 'ul-hold-list'],
      ],
    ];
    return $list;
  }

}

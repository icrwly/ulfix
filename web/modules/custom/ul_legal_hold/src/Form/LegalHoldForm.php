<?php

namespace Drupal\ul_legal_hold\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\NodeStorageInterface;
use Drupal\node\NodeInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Link;

/**
 * Form controller for the legal hold entity edit forms.
 */
class LegalHoldForm extends ContentEntityForm {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;


  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The node storage controller.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * Legal hold form constructor.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match service.
   * @param \Drupal\node\NodeStorageInterface $node_storage
   *   The entity manager.
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter service.
   */
  public function __construct(EntityRepositoryInterface $entity_repository, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL, RouteMatchInterface $route_match, NodeStorageInterface $node_storage, DateFormatter $date_formatter) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
    $this->routeMatch = $route_match;
    $this->nodeStorage = $node_storage;
    $this->dateFormatter = $date_formatter;

    $this->node = $this->getNode();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('current_route_match'),
      $container->get('entity_type.manager')->getStorage('node'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $held_revisions = $entity->getHeldRevisions();
    $title = $this->t('Revisions to be placed on Legal Hold');
    $vids = $this->getVids($this->node);
    $options = [];
    $current = [];

    foreach ($vids as $vid) {
      // Reset the default flag.
      $default = FALSE;
      // Load this revision and pull some info from it.
      $rev = $this->nodeStorage->loadRevision($vid);
      if (!isset($rev) || $rev->getRevisionUser() == NULL) {
        continue;
      }
      $user = $rev->getRevisionUser()->getAccountName();
      $timestamp = $this->dateFormatter->format($rev->getRevisionCreationTime(), 'long');
      $timestamp_link = Link::fromTextAndUrl($timestamp, new Url('entity.node.revision', [
        'node' => $rev->id(),
        'node_revision' => $vid,
      ]));
      $log = $rev->get('revision_log')->getString();
      // If this is the default revision, mark it as such.
      if ($rev->isDefaultRevision()) {
        $current[$vid] = $vid;
        $default = TRUE;
      }
      $check_array['data'] = [
        '#theme' => 'image',
        '#width' => 18,
        '#height' => 18,
        '#uri' => 'core/misc/icons/73b355/check.svg',
        '#alt' => $this->t('Success'),
      ];
      // Build the table of available revisions.
      $options[$vid] = [
        'date' => $timestamp_link,
        'user' => $this->t('@user', ['@user' => $user]),
        'log' => $this->t('@log', ['@log' => $log]),
        'default' => $default ? $check_array : ' ',
      ];
    }

    $table_header = [
      'date' => $this->t('Revision Timestamp'),
      'user' => $this->t('Revision Creator'),
      'default' => $this->t('Current Default Revision'),
      'log' => $this->t('Log Message'),
    ];

    $form = parent::buildForm($form, $form_state);

    // Build this field as a tableselect on the form instead of a widget to keep
    // from having to fight against formatting a multi-value field format.
    $form['held_revisions'] = [
      '#type' => 'tableselect',
      '#prefix' => '<h2 class="js-form-required form-required">' . $title . '</h2>',
      '#header' => $table_header,
      '#options' => $options,
      '#default_value' => count($held_revisions) > 0 ? $held_revisions : $current,
      '#weight' => 10,
      '#needs_validation' => FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $entity = $this->entity;
    $massaged_values = [];

    // Make sure we're attaching to a node.
    if ($this->node instanceof NodeInterface) {
      $entity->setHeldContent($this->node);
    }

    // Then extract the values of fields that are not rendered through widgets,
    // by simply copying from top-level form values. This leaves the fields
    // that are not being edited within this form untouched.
    foreach ($form_state->getValues() as $name => $values) {
      if ($name == 'held_revisions') {
        $massaged_values = $this->manipulateRevisions($values);
      }
    }

    // Save as a new revision if requested to do so.
    if (!$form_state->isValueEmpty('revision') && $form_state->getValue('revision') != FALSE) {
      $entity->setNewRevision();

      // If a new revision is created, save the current user as revision author.
      $request_time = \Drupal::time()->getRequestTime();
      $entity->setRevisionCreationTime($request_time);
      $entity->setRevisionUserId($this->currentUser()->id());
    }
    else {
      $entity->setNewRevision(FALSE);
    }

    // Flag the current default if needed.
    $default = FALSE;
    $parent = $this->node;
    $loaded_rev = $parent->getLoadedRevisionId();
    $revs = $this->entity->getHeldRevisions();

    foreach ($revs as $rev) {
      if ($rev === $loaded_rev) {
        $default = TRUE;
      }
    }

    if ($default === TRUE) {
      $parent->setNewRevision(TRUE);
      $parent->revision_log = 'Placed content on Legal Hold.';
      $request_time = \Drupal::time()->getRequestTime();
      $parent->setRevisionCreationTime($request_time);
      $parent->setRevisionTranslationAffected(TRUE);
      $parent->setRevisionUserId($this->currentUser()->id());
      $parent->save();
    }

    if ($massaged_values <> NULL) {
      $entity->setHeldRevisions($massaged_values);
    }

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        \Drupal::messenger()->addMessage($this->t('Created the %label Legal Hold.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        \Drupal::messenger()->addMessage($this->t('Saved the %label Legal Hold.', [
          '%label' => $entity->label(),
        ]));
    }

    $form_state->setRedirect(
      'entity.ul_legal_hold.content', ['node' => $this->node->id()]);
  }

  /**
   * {@inheritdoc}
   */
  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {
    // First, extract values from the form.
    $extracted = $this->getFormDisplay($form_state)->extractFormValues($entity, $form, $form_state);

    // Then extract the values of fields that are not rendered through widgets,
    // by simply copying from top-level form values. This leaves the fields
    // that are not being edited within this form untouched.
    foreach ($form_state->getValues() as $name => $values) {
      if ($name == 'held_revisions') {
        $massaged_values = $this->manipulateRevisions($values);
        $entity->set($name, $massaged_values);
      }
      elseif ($entity->hasField($name) && !isset($extracted[$name])) {
        $entity->set($name, $values);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, FormStateInterface $form_state) {
    $entity = parent::buildEntity($form, $form_state);

    // Invoke all specified builders for copying form values to entity
    // properties.
    if (isset($form['#entity_builders'])) {
      foreach ($form['#entity_builders'] as $function) {
        call_user_func_array(
          $form_state->prepareCallback($function),
          [$entity->getEntityTypeId(), $entity, &$form, &$form_state]
        );
      }
    }

    return $entity;
  }

  /**
   * Get the revision IDs of the current node.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node object.
   *
   * @return array
   *   Array of Revision IDs.
   */
  protected function getVids(Node $node) {
    $vids = $this->nodeStorage->revisionIds($node);
    return $vids;
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
      $node = $this->nodeStorage->load($node);
      return $node;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    // Update the changed timestamp of the entity.
    $this->updateChangedTime($this->entity);
  }

  /**
   * Helper method to clean up field.
   *
   * @param array $values
   *   The current form state.
   *
   * @return array
   *   An array of cleaned values.
   */
  private function manipulateRevisions(array $values) {
    $parent_id = $this->node->id();
    foreach ($values as $v) {
      if ($v !== 0) {
        $massaged_values[] = [
          'target_id' => $parent_id,
          'target_revision_id' => $v,
        ];
      }
    }
    return $massaged_values;
  }

}

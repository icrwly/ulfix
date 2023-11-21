<?php

namespace Drupal\ul_alerts\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Alert revision.
 *
 * @ingroup ul_alerts
 */
class AlertRevisionDeleteForm extends ConfirmFormBase {


  /**
   * The Alert revision.
   *
   * @var \Drupal\ul_alerts\Entity\AlertInterface
   */
  protected $revision;

  /**
   * The Alert storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $AlertStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a new AlertRevisionDeleteForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The entity storage.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(EntityStorageInterface $entity_storage, Connection $connection) {
    $this->AlertStorage = $entity_storage;
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $entity_manager = $container->get('entity_type.manager');
    return new static(
      $entity_manager->getStorage('ul_alert'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ul_alert_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $format_date = \Drupal::service('date.formatter')
      ->format($this->revision->getRevisionCreationTime());
    return $this->t(
      'Are you sure you want to delete the revision from %revision-date?',
      ['%revision-date' => $format_date]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.ul_alert.version_history', ['ul_alert' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $ul_alert_revision = NULL) {
    $this->revision = $this->AlertStorage->loadRevision($ul_alert_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->AlertStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Alert: deleted %title revision %revision.', [
      '%title' => $this->revision->label(),
      '%revision' => $this->revision->getRevisionId(),
    ]);

    $format_date = \Drupal::service('date.formatter')
      ->format($this->revision->getRevisionCreationTime());
    \Drupal::messenger()->addMessage($this->t(
      'Revision from %revision-date of Alert %title has been deleted.', [
        '%revision-date' => $format_date,
        '%title' => $this->revision->label(),
      ]));
    $form_state->setRedirect(
      'entity.ul_alert.canonical',
       ['ul_alert' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {ul_alert_field_revision} WHERE id = :id', [':id' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.ul_alert.version_history',
         ['ul_alert' => $this->revision->id()]
      );
    }
  }

}

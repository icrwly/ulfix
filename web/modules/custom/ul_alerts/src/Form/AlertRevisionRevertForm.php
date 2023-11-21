<?php

namespace Drupal\ul_alerts\Form;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\ul_alerts\Entity\AlertInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for reverting a Alert revision.
 *
 * @ingroup ul_alerts
 */
class AlertRevisionRevertForm extends ConfirmFormBase {


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
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a new AlertRevisionRevertForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The Alert storage.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(EntityStorageInterface $entity_storage, DateFormatterInterface $date_formatter) {
    $this->AlertStorage = $entity_storage;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('ul_alert'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ul_alert_revision_revert_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $date = $this->dateFormatter->format($this->revision->getRevisionCreationTime());
    return $this->t('Are you sure you want to revert to the revision from %revision-date?',
      ['%revision-date' => $date]);
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
    return $this->t('Revert');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return '';
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
    // The revision timestamp will be updated when the revision is saved. Keep
    // the original one for the confirmation message.
    $original_revision_timestamp = $this->revision->getRevisionCreationTime();

    $this->revision = $this->prepareRevertedRevision($this->revision, $form_state);
    $this->revision->revision_log = $this->t(
      'Copy of the revision from %date.',
      ['%date' => $this->dateFormatter->format($original_revision_timestamp)],
    );
    $this->revision->save();

    $this->logger('content')->notice('Alert: reverted %title revision %revision.', [
      '%title' => $this->revision->label(),
      '%revision' => $this->revision->getRevisionId(),
    ]);
    \Drupal::messenger()->addMessage($this->t(
      'Alert %title has been reverted to the revision from %revision-date.', [
        '%title' => $this->revision->label(),
        '%revision-date' => $this->dateFormatter->format($original_revision_timestamp),
      ]
    ));
    $form_state->setRedirect(
      'entity.ul_alert.version_history',
      ['ul_alert' => $this->revision->id()]
    );
  }

  /**
   * Prepares a revision to be reverted.
   *
   * @param \Drupal\ul_alerts\Entity\AlertInterface $revision
   *   The revision to be reverted.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\ul_alerts\Entity\AlertInterface
   *   The prepared revision ready to be stored.
   */
  protected function prepareRevertedRevision(AlertInterface $revision, FormStateInterface $form_state) {
    $revision->setNewRevision();
    $revision->isDefaultRevision(TRUE);
    $request_time = \Drupal::time()->getRequestTime();
    $revision->setRevisionCreationTime($request_time);

    return $revision;
  }

}

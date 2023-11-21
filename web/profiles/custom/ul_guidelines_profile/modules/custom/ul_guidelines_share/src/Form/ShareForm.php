<?php

namespace Drupal\ul_guidelines_share\Form;

/**
 * @file
 * Contains \Drupal\ul_guidelines_share\Form\ShareForm.
 */

use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class for UL Guidelines Share Form.
 */
class ShareForm extends FormBase {

  /**
   * Language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Mail manager service.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * Constructs a new \Drupal\contact\MailHandler object.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager service.
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   Mail manager service.
   */
  public function __construct(LanguageManagerInterface $language_manager, MailManagerInterface $mail_manager) {
    $this->languageManager = $language_manager;
    $this->mailManager = $mail_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('language_manager'),
      $container->get('plugin.manager.mail')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ul_guidelines_share_share_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Enter Email'),
      '#required' => TRUE,
    ];
    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#required' => TRUE,
    ];
    $form['shared_url'] = [
      '#type' => 'hidden',
      '#value' => $this->createShareUrl(),
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Share'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Email setup.
    $params = [];
    $module = 'ul_guidelines_share';
    $key = 'share_form_email';
    $to = $form_state->getValue('email');
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $params['message'] = $form_state->getValue('message');
    $params['shared_url'] = $form_state->getValue('shared_url');

    // Email sending, this will find the right one declared in hook_mail()
    $mailManager = $this->mailManager;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, $reply = NULL, $send = TRUE);

    // If the email was successfully sent or not.
    if ($result['result'] !== TRUE) {
      $this->messenger()->addMessage($this->t('There was a problem sending your message and it was not sent.'), 'error');
    }
    else {
      $this->messenger()->addMessage($this->t('Your message has been sent.'));
    }
  }

  /**
   * Creating the shared URL when something is shared.
   */
  private function createShareUrl() {
    return Url::fromRoute('<current>', [], ['absolute' => 'true', 'query' => ['referrer' => 'share']])->toString();
  }

}

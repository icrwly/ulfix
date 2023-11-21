<?php

namespace Drupal\ul_salesforce\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Salesforce module settings form.
 */
class ContactUsForm extends WebToCaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ul_salesforce_contactus_form';
  }

  /**
   * {@inheritdoc}
   */
  protected $title = 'Contact Us';

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // Web Inquiry Type.
    $form['web_inquiry'] = [
      '#type' => 'select',
      '#attributes' => [
        'name' => $this->custom_fields['web_inquiry_type'][$this->env],
        'class' => ['fbtf_select'],
      ],
      '#title' => $this->t('Inquiry type'),
      '#options' => [
        'Changes to existing UL File' => $this->t('Changes to existing UL File'),
        'General Feedback (Customer Advocacy)' => $this->t('Customer Feedback'),
        'Invoicing' => $this->t('Invoice Questions'),
        'myUL Portal Support' => $this->t('myUL Portal Support'),
        'Product iQ (PiQ) Question' => $this->t('Product iQ Support'),
        'Sales' => $this->t('Sales Inquiries'),
        'Customer Service' => $this->t('Other Customer Service Inquiries'),
      ],
      '#empty_option' => '...',
      '#required' => TRUE,
    ];

    // NOTE: Fields (below) are disabled by JS until user selects Inquiry Type!
    // Subject.
    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#size' => 20,
      '#maxlength' => 80,
      '#required' => TRUE,
      '#attributes' => ['class' => ['sfrce_field']],
      '#disabled' => TRUE,
    ];

    // Description.
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('How can we help you?'),
      '#required' => TRUE,
      '#attributes' => ['class' => ['sfrce_field']],
      '#disabled' => TRUE,
    ];

    // First Name.
    $form['first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First name'),
      '#size' => 40,
      '#maxlength' => 40,
      '#required' => TRUE,
      '#attributes' => ['class' => ['sfrce_field']],
      '#disabled' => TRUE,
    ];

    // Last Name.
    $form['last_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last name'),
      '#size' => 40,
      '#maxlength' => 40,
      '#required' => TRUE,
      '#attributes' => ['class' => ['sfrce_field']],
      '#disabled' => TRUE,
    ];

    // Hidden Contact Name.
    $form['name'] = [
      '#type' => 'hidden',
    ];

    // Email.
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email address'),
      '#size' => 20,
      '#maxlength' => 80,
      '#required' => TRUE,
      '#attributes' => ['class' => ['sfrce_field']],
      '#disabled' => TRUE,
    ];

    // Phone.
    $form['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone number'),
      '#size' => 20,
      '#maxlength' => 40,
      '#required' => FALSE,
      '#attributes' => [
        'type' => 'tel',
        'class' => ['sfrce_field'],
      ],
      '#disabled' => TRUE,
    ];

    // Company Name*.
    $form['company'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Company name'),
      '#size' => 20,
      '#maxlength' => 80,
      '#required' => FALSE,
      '#attributes' => ['class' => ['sfrce_field']],
      '#disabled' => TRUE,
    ];

    // Where Do You Live.
    $form['web_country'] = [
      '#type' => 'select',
      '#attributes' => [
        'name' => $this->custom_fields['web_country'][$this->env],
        'class' => ['sfrce_field'],
      ],
      '#title' => $this->t('Where do you live?'),
      '#options' => $this->country_options,
      '#empty_option' => '...',
      '#required' => TRUE,
      '#disabled' => TRUE,
    ];

    // Language.
    $form['language'] = [
      '#type' => 'select',
      '#attributes' => [
        'name' => $this->custom_fields['language'][$this->env],
        'class' => ['sfrce_field'],
      ],
      '#title' => $this->t('What is your preferred language?'),
      '#options' => $this->language_options,
      '#empty_option' => '...',
      '#required' => TRUE,
      '#disabled' => TRUE,
    ];

    // GDPR/Opt-in message target div:
    $form['gdprTarget'] = [
      '#prefix' => '<div class="sfrce_field_wrap gdpr_target">',
      '#suffix' => '</div>',
      '#value' => $this->t('Loading...'),
    ];

    return $form;
  }

}

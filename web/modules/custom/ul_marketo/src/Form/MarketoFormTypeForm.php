<?php

namespace Drupal\ul_marketo\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\ul_marketo\UlMarketoServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MarketoFormTypeForm.
 */
class MarketoFormTypeForm extends EntityForm {

  /**
   * The Marketo service.
   *
   * @var Drupal\ul_marketo\UlMarketoServiceInterface
   */
  protected $marketoService;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Class constructor.
   *
   * @param \Drupal\ul_marketo\UlMarketoServiceInterface $marketo_service
   *   The marketo service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(UlMarketoServiceInterface $marketo_service, MessengerInterface $messenger) {
    $this->marketoService = $marketo_service;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      // Load the service required to construct this class.
      $container->get('ul_marketo'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'marketo_form_add_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Need $i to be 0 for future code.
    $i = 0;
    $entity = $this->entity;
    // If it's a new form or first load of edit, form_state won't be populated.
    $form_id_count = $form_state->get('num_ids') ?? NULL;
    // If there's an entity ID and there's no form_id_count then we're in a
    // first load of an edit form. Count those items.
    if ($entity->id() !== NULL && $form_id_count === NULL) {
      $array = $entity->marketo_form_id;
      // Since there's an extra item in the array we only want numeric stuff.
      $form_id_count = count(array_filter(array_keys($array), function ($key) {
        return is_int($key);
      }));
      // Put it in the form_state for easier access.
      $form_state->set('num_ids', $form_id_count);
    }
    // Makes it easier to reload the things.
    $form['#tree'] = TRUE;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $entity->label(),
      '#description' => $this->t("The human-readable name of this Marketo Form type. This name must be unique."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\ul_marketo\Entity\MarketoFormType::load',
      ],
      '#disabled' => !$entity->isNew(),
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#maxlength' => 255,
      '#default_value' => $entity->getDescription(),
    ];

    $form['marketo_form_id'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Form IDs'),
      '#prefix' => '<div id="form-id-container">',
      '#suffix' => '</div>',
    ];

    // If there isn't a count, we assume there's just the one empty form.
    if (empty($form_id_count)) {
      $form_id_count = $form_state->set('num_ids', 1);
    }
    // If we've set form_state num_ids then let's use that.
    if ($form_state->get('num_ids') > 0) {
      $value = $form_state->get('num_ids');
    }
    // Fall back to 1 form.
    else {
      $value = 1;
    }

    // Loop through and grab all of the settings for each form.
    for ($i = 0; $i < $value; $i++) {

      $marketo_form_id_array = $entity->get('marketo_form_id');
      // Let's increase the prettiness.
      $form['marketo_form_id'][$i] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Form ID'),
        '#attributes' => ['class' => ['form--inline']],
      ];
      // Let's put all the fields together.
      $form['marketo_form_id'][$i]['value'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Form ID'),
        '#default_value' => $marketo_form_id_array[$i]['value'] ?? '',
      ];
      $form['marketo_form_id'][$i]['filters'] = [
        '#type' => 'container',
      ];
      $form['marketo_form_id'][$i]['filters']['instance'] = [
        '#type' => 'hidden',
        '#default_value' => $marketo_form_id_array[$i]['filters']['instance'] ?? 'Enterprise',
      ];
      $form['marketo_form_id'][$i]['filters']['env'] = [
        '#type' => 'select',
        '#options' => $this->marketoService->getAllEnvironments(),
        '#title' => $this->t('Environment'),
        '#default_value' => $marketo_form_id_array[$i]['filters']['env'] ?? 'prod',
      ];

      // NOTE: We only have EN at present!
      // =====================================:
      // @todo Move those out of this method.
      // getLanguages loads the translate tab, that should be removed.
      // $language_manager = $this->marketoService->getLanguageManager();
      // $languages = $language_manager->getLanguages();
      // $options = [];
      // foreach ($languages as $iey => $language) {
      // $options[$iey] = $language->getName();
      // }.
      // $form['marketo_form_id'][$i]['filters']['langcode'] = [
      // '#type' => 'select',
      // '#options' => $options,
      // '#title' => $this->t('Language'),
      // '#default_value' => $marketo_form_id_array[$i]['filters']['langcode'],
      // ];
      $form['marketo_form_id'][$i]['filters']['langcode'] = [
        '#type' => 'hidden',
        '#default_value' => 'en',
      ];
    }

    $form_state->setCached(FALSE);
    $form['marketo_form_id']['actions']['add_form_id'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Form ID'),
      '#submit' => ['::addFormId'],
      '#ajax' => [
        'callback' => '::addFormIdCallback',
        'wrapper' => 'form-id-container',
      ],
    ];
    // Add an option to remove the last form from the settings.
    if ($value > 1) {
      $form['marketo_form_id']['actions']['remove_form_id'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove ID'),
        '#submit' => ['::removeFormId'],
        '#ajax' => [
          'callback' => '::addFormIdCallback',
          'wrapper' => 'form-id-container',
        ],
      ];
    }
    // The bottom of page fields for extra settings.
    $form['settings'] = [
      '#type' => 'vertical_tabs',
    ];
    $form['page'] = [
      '#type' => 'details',
      '#group' => 'settings',
      '#title' => $this->t('Page settings'),
    ];
    $form['page']['page_enable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Page'),
      '#default_value' => $entity->getSetting('page_enable'),
      '#description' => $this->t("Enable separate page for this form."),
    ];
    $form['page']['path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path'),
      '#maxlength' => 255,
      '#default_value' => $entity->getSetting('path'),
      '#description' => $this->t("The path to the form. Lead with forward slash."),
      '#states' => [
        'visible' => [
          ':input[name="page_enable"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['block'] = [
      '#type' => 'details',
      '#group' => 'settings',
      '#title' => $this->t('Block settings'),
    ];
    $form['block']['block_enable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Block'),
      '#default_value' => $entity->getSetting('block_enable'),
      '#description' => $this->t("Enable block for this form."),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Function to massage data for adding form ID.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function addFormId(array &$form, FormStateInterface $form_state) {
    $form_id_count = $form_state->get('num_ids');
    $add_button = $form_id_count + 1;
    $form_state->set('num_ids', $add_button);
    $form_state->setRebuild();
  }

  /**
   * Callback for adding form ID.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return mixed
   *   The DOM object to be rebuilt.
   */
  public function addFormIdCallback(array &$form, FormStateInterface $form_state) {
    // @todo if we don't need the commented line, remove it.
    // $form_id_count = $form_state->get('num_ids');
    return $form['marketo_form_id'];
  }

  /**
   * Callback for removing form ID.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function removeCallback(array &$form, FormStateInterface $form_state) {
    $form_id_count = $form_state->get('num_ids');
    if ($form_id_count > 1) {
      $remove_button = $form_id_count - 1;
      $form_state->set('num_ids', $remove_button);
    }
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $page_settings = $form_state->getValue('page');
    $block_settings = $form_state->getValue('block');

    // @todo Make this more dynamic.
    $entity->setSetting('page_enable', $page_settings['page_enable']);
    $entity->setSetting('path', $page_settings['path']);
    $entity->setSetting('block_enable', $block_settings['block_enable']);

    $status = $entity->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger->addMessage($this->t('Created the %label Marketo Form type.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger->addMessage($this->t('Saved the %label Marketo Form type.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirectUrl($entity->toUrl('collection'));
  }

}

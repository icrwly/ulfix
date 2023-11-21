<?php

namespace Drupal\ul_marketo\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\SortArray;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\ul_marketo\UlMarketoServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'dice' widget.
 *
 * @FieldWidget (
 *   id = "marketo_meta",
 *   label = @Translation("Marketo Metadata Widget"),
 *   field_types = {
 *     "marketo_meta"
 *   }
 * )
 */
class MarketoMetaWidget extends WidgetBase {

  /**
   * The Marketo service.
   *
   * @var Drupal\ul_marketo\UlMarketoServiceInterface
   */
  protected $marketo;

  /**
   * Constructs a MarketoFormWidget object.
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param Drupal\ul_marketo\UlMarketoServiceInterface $marketo_service
   *   The marketo form service manager.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, UlMarketoServiceInterface $marketo_service) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->marketo = $marketo_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('ul_marketo')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // Populate with form state if we have them because they should be the
    // most current.
    $field = $this->fieldDefinition->getName();

    // Marketo Instance:
    $element['instance'] = [
      '#type' => 'hidden',
      '#default_value' => 'Enterprise',
    ];

    // Marketo Campaign:
    $element['mkto_campaign'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Marketo Campaign'),
      '#maxlength' => 255,
      '#description' => $this->t('Enter the Marketo Campaign ID (for conversion attribution).'),
      '#default_value' => $items[$delta]->mkto_campaign ?? NULL,
    ];

    // If the Default Lang !== Curr Lang
    // make Mkto Campaign field "readonly":
    $node = $form_state->getFormObject()->getEntity();
    if ($node) {
      $curr_lang = $node->get('langcode')->value;
      $dflt_lang = \Drupal::languageManager()->getDefaultLanguage()->getId();
      if ($curr_lang !== $dflt_lang) {
        $element['mkto_campaign']['#attributes'] = ['readonly' => 'readonly'];
      }
    }

    /* New L2O fields (start): */
    // Sub-COU: This is now a HIDDEN field, because we do nothing with
    // this value. The sub-cou value is hard-coded to 'last_interests'
    // which aligns with the new/only Sub-COU that is in this file:
    // `config/ul_enterprise_profile/default/ul_marketo.last_interest_by_sub_cou.yml`.
    $sub_cou = 'last_interests';
    $element['sub_cou'] = [
      '#type' => 'hidden',
      '#default_value' => $sub_cou,
      '#ajax' => [
        'callback' => [$this, 'nodeFormRebuild'],
        'event' => 'change',
        'wrapper' => $field . 'marketo-meta-wrapper',
      ],
    ];

    // Get all the "Last Interests" options:
    $last_interests = [];
    if (!empty($sub_cou)) {
      $last_interests = $this->marketo->getSubCouOptions($sub_cou);
    }

    $element['last_interest'] = [
      '#type' => 'select',
      '#title' => $this->t('Last Interest'),
      '#options' => $last_interests,
      '#description' => $this->t('Choose the Last Interest'),
      '#empty_option' => $this->t('- None -'),
      '#default_value' => $items[$delta]->last_interest ?? NULL,
      '#chosen' => TRUE,
      '#limit_validation_errors' => [],
      '#prefix' => '<div id="' . $field . 'marketo-meta-wrapper">',
      '#suffix' => '</div>',
    ];
    /* New L2O fields (end): */

    if ($this->fieldDefinition->getFieldStorageDefinition()->getCardinality() == 1) {
      $element += [
        '#type' => 'details',
        '#group' => isset($form['additional_settings']) ? 'additional_settings' : 'advanced',
        '#title' => $this->t('Marketo Forms'),
      ];
    }

    return $element;
  }

  /**
   * AJAX callback to rebuild the form.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   Either the render array of the new form section or empty array.
   */
  public static function nodeFormRebuild(array $form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $parents = array_slice($trigger['#array_parents'], 0, -1);
    $parents[] = 'last_interest';
    $parents = NestedArray::getValue($form, $parents);

    return $parents;
  }

}

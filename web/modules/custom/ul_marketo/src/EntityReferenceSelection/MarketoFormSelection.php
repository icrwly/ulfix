<?php

namespace Drupal\ul_marketo\Plugin\EntityReferenceSelection;

use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;

/**
 * Default plugin implementation of the Entity Reference Selection plugin.
 *
 * @EntityReferenceSelection(
 *   id = "default:marketo_form",
 *   label = @Translation("Marketo Forms"),
 *   group = "default",
 *   entity_types = {"marketo_form"},
 *   weight = 0
 * )
 */
class MarketoFormSelection extends DefaultSelection {

  /**
   * Entity type bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  public $entityTypeBundleInfo;

  /**
   * ParagraphSelection constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   Entity type bundle info service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_manager, ModuleHandlerInterface $module_handler, AccountInterface $current_user, EntityTypeBundleInfoInterface $entity_type_bundle_info) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_manager, $module_handler, $current_user);

    $this->entityTypeBundleInfo = $entity_type_bundle_info;
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
      $container->get('module_handler'),
      $container->get('current_user'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $entity_type_id = $this->configuration['target_type'];
    $selection_handler_settings = $this->configuration['handler_settings'] ?: [];
    $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type_id);

    // Merge-in default values.
    $selection_handler_settings += [
      'target_bundles' => [],
      'negate' => 0,
      'target_bundles_drag_drop' => [],
    ];

    $bundle_options = [];
    $bundle_options_simple = [];

    // Default weight for new items.
    $weight = count($bundles) + 1;

    foreach ($bundles as $bundle_name => $bundle_info) {
      $bundle_options_simple[$bundle_name] = $bundle_info['label'];
      $bundle_options[$bundle_name] = [
        'label' => $bundle_info['label'],
        'enabled' => isset($selection_handler_settings['target_bundles_drag_drop'][$bundle_name]['enabled']) ? $selection_handler_settings['target_bundles_drag_drop'][$bundle_name]['enabled'] : FALSE,
        'weight' => isset($selection_handler_settings['target_bundles_drag_drop'][$bundle_name]['weight']) ? $selection_handler_settings['target_bundles_drag_drop'][$bundle_name]['weight'] : $weight,
      ];
      $weight++;
    }

    // Do negate the selection.
    $form['negate'] = [
      '#type' => 'radios',
      '#options' => [
        1 => $this->t('Exclude the selected below'),
        0 => $this->t('Include the selected below'),
      ],
      '#title' => $this->t('Which Marketo Form types should be allowed?'),
      '#default_value' => isset($selection_handler_settings['negate']) ? $selection_handler_settings['negate'] : 0,
    ];

    // Kept for compatibility with other entity reference widgets.
    $form['target_bundles'] = [
      '#type' => 'checkboxes',
      '#options' => $bundle_options_simple,
      '#default_value' => isset($selection_handler_settings['target_bundles']) ? $selection_handler_settings['target_bundles'] : [],
      '#access' => FALSE,
    ];

    if ($bundle_options) {
      $form['target_bundles_drag_drop'] = [
        '#element_validate' => [[__CLASS__, 'targetTypeValidate']],
        '#type' => 'table',
        '#header' => [
          $this->t('Type'),
          $this->t('Weight'),
        ],
        '#attributes' => [
          'id' => 'bundles',
        ],
        '#prefix' => '<h5>' . $this->t('Marketo Form types') . '</h5>',
        '#suffix' => '<div class="description">' . $this->t('Selection of Marketo form types for this field. Select none to allow all Marketo Form types.') . '</div>',
      ];

      $form['target_bundles_drag_drop']['#tabledrag'][] = [
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => 'bundle-weight',
      ];
    }

    uasort($bundle_options, 'Drupal\Component\Utility\SortArray::sortByWeightElement');

    $weight_delta = $weight;

    // Default weight for new items.
    $weight = count($bundles) + 1;
    foreach ($bundle_options as $bundle_name => $bundle_info) {
      $form['target_bundles_drag_drop'][$bundle_name] = [
        '#attributes' => [
          'class' => ['draggable'],
        ],
      ];

      $form['target_bundles_drag_drop'][$bundle_name]['enabled'] = [
        '#type' => 'checkbox',
        '#title' => $bundle_info['label'],
        '#title_display' => 'after',
        '#default_value' => $bundle_info['enabled'],
      ];

      $form['target_bundles_drag_drop'][$bundle_name]['weight'] = [
        '#type' => 'weight',
        '#default_value' => (int) $bundle_info['weight'],
        '#delta' => $weight_delta,
        '#title' => $this->t('Weight for type @type', ['@type' => $bundle_info['label']]),
        '#title_display' => 'invisible',
        '#attributes' => [
          'class' => ['bundle-weight', 'bundle-weight-' . $bundle_name],
        ],
      ];
      $weight++;
    }

    if (!count($bundle_options)) {
      $form['allowed_bundles_explain'] = [
        '#type' => 'markup',
        '#markup' => $this->t('You did not add any Marketo form types yet, click <a href=":here">here</a> to add one.', [':here' => Url::fromRoute('marketo_form.type_add')->toString()]),
      ];
    }

    return $form;
  }

  /**
   * Validate helper to have support for other entity reference widgets.
   *
   * @param array $element
   *   Render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param array $form
   *   The form.
   */
  public static function targetTypeValidate(array $element, FormStateInterface $form_state, array $form) {
    $values = &$form_state->getValues();
    $element_values = NestedArray::getValue($values, $element['#parents']);
    $bundle_options = [];

    if ($element_values) {
      $enabled = 0;
      foreach ($element_values as $machine_name => $bundle_info) {
        if (isset($bundle_info['enabled']) && $bundle_info['enabled']) {
          $bundle_options[$machine_name] = $machine_name;
          $enabled++;
        }
      }

      // All disabled = all enabled.
      if ($enabled === 0) {
        $bundle_options = NULL;
      }
    }

    // New value parents.
    $parents = array_merge(array_slice($element['#parents'], 0, -1), ['target_bundles']);
    NestedArray::setValue($values, $parents, $bundle_options);
  }

  /**
   * Returns the sorted allowed types for the field.
   *
   * @return array
   *   A list of arrays keyed by the paragraph type machine name
   *   with the following properties.
   *     - label: The label of the paragraph type.
   *     - weight: The weight of the paragraph type.
   */
  public function getSortedAllowedTypes() {
    $return_bundles = [];

    $bundles = $this->entityTypeBundleInfo->getBundleInfo('marketo_form');
    if (!empty($this->configuration['handler_settings']['target_bundles'])) {
      if (isset($this->configuration['handler_settings']['negate']) && $this->configuration['handler_settings']['negate'] == '1') {
        $bundles = array_diff_key($bundles, $this->configuration['handler_settings']['target_bundles']);
      }
      else {
        $bundles = array_intersect_key($bundles, $this->configuration['handler_settings']['target_bundles']);
      }
    }

    // Support for the paragraphs reference type.
    if (!empty($this->configuration['handler_settings']['target_bundles_drag_drop'])) {
      $drag_drop_settings = $this->configuration['handler_settings']['target_bundles_drag_drop'];
      $max_weight = count($bundles);

      foreach ($drag_drop_settings as $bundle_info) {
        if (isset($bundle_info['weight']) && $bundle_info['weight'] && $bundle_info['weight'] > $max_weight) {
          $max_weight = $bundle_info['weight'];
        }
      }

      // Default weight for new items.
      $weight = $max_weight + 1;
      foreach ($bundles as $machine_name => $bundle) {
        $return_bundles[$machine_name] = [
          'label' => $bundle['label'],
          'weight' => isset($drag_drop_settings[$machine_name]['weight']) ? $drag_drop_settings[$machine_name]['weight'] : $weight,
        ];
        $weight++;
      }
    }
    else {
      $weight = 0;

      foreach ($bundles as $machine_name => $bundle) {
        $return_bundles[$machine_name] = [
          'label' => $bundle['label'],
          'weight' => $weight,
        ];

        $weight++;
      }
    }
    uasort($return_bundles, 'Drupal\Component\Utility\SortArray::sortByWeightElement');

    return $return_bundles;
  }

  /**
   * {@inheritdoc}
   */
  public function validateReferenceableNewEntities(array $entities) {
    // This is triggering phpcs because of the return statement's structure.
    // phpcs:disable
    $bundles = array_keys($this->getSortedAllowedTypes());
    // phpcs:enable
    return array_filter($entities, function ($entity) {
      if (isset($bundles)) {
        return in_array($entity->bundle(), $bundles);
      }
      return TRUE;
    });
  }

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $target_type = $this->configuration['target_type'];
    $handler_settings = $this->configuration['handler_settings'];
    $entity_type = $this->entityManager->getDefinition($target_type);

    $query = $this->entityManager->getStorage($target_type)->getQuery();

    // If 'target_bundles' is NULL, all bundles are referenceable, no further
    // conditions are needed.
    if (isset($handler_settings['target_bundles']) && is_array($handler_settings['target_bundles'])) {
      $target_bundles = array_keys($this->getSortedAllowedTypes());

      // If 'target_bundles' is an empty array, no bundle is referenceable,
      // force the query to never return anything and bail out early.
      if ($target_bundles === []) {
        $query->condition($entity_type->getKey('id'), NULL, '=');
        return $query;
      }
      else {
        $query->condition($entity_type->getKey('bundle'), $target_bundles, 'IN');
      }
    }

    if (isset($match) && $label_key = $entity_type->getKey('label')) {
      $query->condition($label_key, $match, $match_operator);
    }

    // Add entity-access tag.
    $query->addTag($target_type . '_access');

    // Add the Selection handler for system_query_entity_reference_alter().
    $query->addTag('entity_reference');
    $query->addMetaData('entity_reference_selection_handler', $this);

    // Add the sort option.
    if (!empty($handler_settings['sort'])) {
      $sort_settings = $handler_settings['sort'];
      if ($sort_settings['field'] != '_none') {
        $query->sort($sort_settings['field'], $sort_settings['direction']);
      }
    }

    return $query;
  }

}

<?php

namespace Drupal\ul_crc_asset\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\ul_crc\CRCServiceInterface;
use Drupal\Component\Utility\SortArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\entity_browser\FieldWidgetDisplayManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\entity_browser\Plugin\Field\FieldWidget\EntityReferenceBrowserWidget;

/**
 * Entity browser widget.
 *
 * @FieldWidget(
 *   id = "crc_asset_widget",
 *   label = @Translation("Entity browser"),
 *   provider = "entity_browser",
 *   multiple_values = TRUE,
 *   field_types = {
 *     "crc_asset_item"
 *   }
 * )
 */
class CRCAssetWidget extends EntityReferenceBrowserWidget {

  /**
   * Due to the table structure, this widget has a different depth.
   *
   * @var int
   */
  protected static $deleteDepth = 3;

  /**
   * A list of currently edited items. Used to determine alt/title values.
   *
   * @var \Drupal\Core\Field\FieldItemListInterface
   */
  protected $items;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The display repository service.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $displayRepository;

  /**
   * The UL CRC Service.
   *
   * @var \Drupal\ul_crc\CRCServiceInterface
   */
  protected $crc;

  /**
   * Constructs widget plugin.
   *
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   * @param \Drupal\entity_browser\FieldWidgetDisplayManager $field_display_manager
   *   Field widget display plugin manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\ul_crc\CRCServiceInterface $ul_crc
   *   The CRC Service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager, FieldWidgetDisplayManager $field_display_manager, ModuleHandlerInterface $module_handler, AccountInterface $current_user, MessengerInterface $messenger, CRCServiceInterface $ul_crc) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings, $entity_type_manager, $field_display_manager, $module_handler, $current_user, $messenger);
    $this->entityTypeManager = $entity_type_manager;
    $this->fieldDisplayManager = $field_display_manager;
    $this->crc = $ul_crc;
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
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.entity_browser.field_widget_display'),
      $container->get('module_handler'),
      $container->get('current_user'),
      $container->get('messenger'),
      $container->get('ul_crc')

    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();

    // These settings are hidden.
    unset($settings['field_widget_display']);
    unset($settings['field_widget_display_settings']);
    $settings['entity_browser'] = 'crc_asset';

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    // Remove form elements that aren't relevant.
    unset($element['field_widget_edit']);
    unset($element['field_widget_display']);
    unset($element['field_widget_display_settings']);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = $this->summaryBase();
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    if (!$this->crc->isConnected()) {
      $details_id = Html::getUniqueId('edit-' . $this->fieldDefinition->getName());
      $element += [
        '#id' => $details_id,
        '#type' => 'details',
        '#open' => $this->getSetting('open'),
        '#required' => $this->fieldDefinition->isRequired(),
        '#markup' => $this->t('Could not connect to the CRC Service.'),
      ];
      return $element;
    }
    else {
      $this->items = $items;
      return parent::formElement($items, $delta, $element, $form, $form_state);
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function displayCurrentSelection($details_id, $field_parents, $entities) {

    // Note: Most of this code was grabbed from FileBrowserWidget.php and
    // modified.
    $field_machine_name = $this->fieldDefinition->getName();

    $widget_settings = $this->getSettings();

    $delta = 0;

    $order_class = $field_machine_name . '-delta-order';

    $current = [
      '#type' => 'table',
      '#empty' => $this->t('No assets yet'),
      '#attributes' => ['class' => ['entities-list']],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => $order_class,
        ],
      ],
    ];

    // Add the remaining columns.
    $current['#header'][] = ['data' => $this->t('Name'), 'colspan' => 2];
    $current['#header'][] = ['data' => $this->t('Language')];
    $current['#header'][] = ['data' => $this->t('Operations')];
    $current['#header'][] = $this->t('Order', [], ['context' => 'Sort order']);

    // Store duplicated CRC data.
    $tmp_ids = [];
    $duplicated_msg = "";

    foreach ($entities as $entity) {
      // CRCAsset Entity.
      $entity_id = $entity->id();
      $type_id = $entity->getEntityTypeId();
      $name = $entity->getName();
      $crc_id = $entity->getCrcId();
      $langcode = $entity->getCrcLanguage();

      // Add warning message if a same CRC file is added more than once.
      if (in_array($entity_id, $tmp_ids)) {
        $duplicated_msg = "Duplicated File, $name (CRC ID: $crc_id)! * Please click the [Remove] button to delete one!";
      }

      // Finding the weight.
      $weight = $delta;
      foreach ($this->items as $item) {
        $weight = $item->_weight ?: $delta;
      }

      $current[$entity_id] = [
        '#attributes' => [
          'class' => ['draggable'],
          'data-entity-id' => $type_id . ':' . $entity_id,
          'data-row-id' => $delta,
        ],
      ];

      $current[$entity_id]['thumbnail'] = [
        '#theme' => 'image',
        '#width' => 50,
        '#uri' => $entity->getCrcData('sm_thumbnail_url'),
      ];
      $current[$entity_id]['name'] = ['#markup' => $name];
      $current[$entity_id]['language'] = ['#markup' => $langcode];

      $limit_validation_errors = array_merge($field_parents, [
        $field_machine_name, 'target_id',
      ]);
      $current[$entity_id]['remove_button'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove'),
        '#ajax' => [
          'callback' => [get_class($this), 'updateWidgetCallback'],
          'wrapper' => $details_id,
        ],
        '#submit' => [[get_class($this), 'removeItemSubmit']],
        '#name' => $field_machine_name . '_remove_' . $entity_id . '_' . md5(json_encode($field_parents)),
        '#limit_validation_errors' => [$limit_validation_errors],
        '#attributes' => [
          'data-entity-id' => $type_id . ':' . $entity_id,
          'data-row-id' => $delta,
        ],
        '#access' => (bool) $widget_settings['field_widget_remove'],
      ];

      $current[$entity_id]['_weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for row @number', ['@number' => $delta + 1]),
        '#title_display' => 'invisible',
        // Note: this 'delta' is the FAPI #type 'weight' element's property.
        '#delta' => count($entities),
        '#default_value' => $weight,
        '#weight' => 100,
        '#attributes' => ['class' => [$order_class]],
      ];

      $delta++;
      $tmp_ids[] = $entity_id;
    }

    // Display warning message in the Table Footer.
    if (!empty($duplicated_msg)) {
      $current['#footer'] = [
        [
          '#type' => [
            'data' => '*',
            'class' => 'crc-select-warning-1',
          ],
          '#markup' => [
            'data' => $duplicated_msg,
            'colspan' => 3,
            'class' => 'crc-select-warning-2',
          ],
        ],
      ];
    }

    return $current;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $ids = empty($values['target_id']) ? [] : explode(' ', trim($values['target_id']));
    $return = [];
    foreach ($ids as $id) {
      $id = explode(':', $id)[1];
      if (is_array($values['current']) && isset($values['current'][$id])) {
        $item_values = [
          'target_id' => $id,
          '_weight' => $values['current'][$id]['_weight'],
        ];
        $return[] = $item_values;
      }
    }

    // Return ourself as the structure doesn't match the default.
    usort($return, function ($a, $b) {
      return SortArray::sortByKeyInt($a, $b, '_weight');
    });

    return array_values($return);
  }

  /**
   * {@inheritdoc}
   */
  protected function getPersistentData() {
    return [
      'validators' => [
        'entity_type' => ['type' => 'crc_asset'],
      ],
      'widget_context' => [],
    ];
  }

}

<?php

namespace Drupal\ul_legal_hold\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'Revision Field' formatter.
 *
 * @FieldFormatter(
 *   id = "ul_legal_hold_revision_field_formatter",
 *   label = @Translation("Revision Field"),
 *   field_types = {
 *     "string",
 *     "entity_reference_revisions"
 *   }
 * )
 */
class RevisionFieldFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * EntityTypeManager object.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {

    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager'),
      $container->get('date.formatter')
    );
  }

  /**
   * Constructs a FormatterBase object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The entity manager.
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityTypeManager $entityTypeManager, DateFormatter $date_formatter) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->entityTypeManager = $entityTypeManager;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $val = $item->getValue();
      $rev_id = $val['target_revision_id'];
      $pid = $val['target_id'];

      $rev = $this->entityTypeManager->getStorage('node')->loadRevision($val['target_revision_id']);
      $timestamp = $this->dateFormatter->format($rev->getRevisionCreationTime(), 'long');
      $log = $rev->get('revision_log')->getString();
      $log_message = strlen($log) > 0 ? $log : $this->t('No Log Message Submitted');
      $route_params = ['node' => $pid, 'node_revision' => $rev_id];
      $route_text = $this->t('@timestamp: @log', [
        '@timestamp' => $timestamp,
        '@log' => $log_message,
      ]);

      $link = Link::fromTextAndUrl($route_text, Url::fromRoute('entity.node.revision', $route_params));
      $rendered_link = $link->toRenderable();

      $element[$delta] = $rendered_link + [
        '#prefix' => '<div class="legal_hold_item">',
        '#suffix' => '</div>',
      ];
    }

    return $element;
  }

}

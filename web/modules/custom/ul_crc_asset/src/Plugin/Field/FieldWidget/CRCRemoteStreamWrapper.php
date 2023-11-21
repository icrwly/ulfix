<?php

namespace Drupal\ul_crc_asset\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\ul_crc\CRCServiceInterface;
use Drupal\Component\Utility\UrlHelper;

/**
 * Plugin implementation of the 'file_generic' widget.
 *
 * @FieldWidget(
 *   id = "ul_crc_remote_stream_wrapper",
 *   label = @Translation("UL CRC Remote stream wrapper"),
 *   field_types = {
 *     "crc_asset_item",
 *   }
 * )
 */
class CRCRemoteStreamWrapper extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * EntityTypeManager object.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  private $currentUser;

  /**
   * The CRC Service.
   *
   * @var \Drupal\ul_crc\CRCServiceInterface
   */
  private $ulcrc;

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
      $container->get('current_user'),
      $container->get('ul_crc')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityTypeManagerInterface $entityTypeManager, AccountProxyInterface $currentUser, CRCServiceInterface $ul_crc) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
    $this->ulcrc = $ul_crc;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // @todo Need to add validation.
    $element['url'] = [
      '#type' => 'textfield',
      '#maxlength' => 2048,
      '#required' => $this->fieldDefinition->isRequired(),
    ];

    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();
    if ($cardinality == 1) {
      $element['url'] += [
        '#title' => $this->fieldDefinition->getLabel(),
        '#description' => $this->getFilteredDescription(),
      ];
    }
    $id = $items->get($delta)->target_id;

    if (!empty($id)) {
      // Load the asset.
      $asset = $this->entityTypeManager->getStorage('crc_asset')->load($id);
      $uri = $asset->getCrcData('original_url');
      if (!empty($asset)) {
        $element['url']['#default_value'] = $uri;
        $element['url']['#disabled'] = TRUE;
      }
    }

    return $element;
  }

  /**
   * Parses the submitted url and returns the relevant parts.
   *
   * @param string $url
   *   Full URL provided from UL's CRC service.
   *
   * @return array
   *   The parsed array including the asset_file_id.
   */
  private function parseSubmittedUrl($url) {

    $url = UrlHelper::parse($url);

    // Extract the asset file id.
    preg_match('/uploads\/asset_file\/attachment\/([0-9]+)/', $url['path'], $matches);
    if (!empty($matches)) {
      $asset_file_id = $matches[1];
      $url['asset_file_id'] = $asset_file_id;
      return $url;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {

    $ul_crc = $this->ulcrc;
    $new_values = [];

    foreach ($values as $value) {
      // Run an API call to fetch asset information by asset file ID.
      $url = $this->parseSubmittedUrl($value['url']);
      if (!empty($url)) {
        $search = [
          'asset_file_id' => $url['asset_file_id'],
        ];
        $asset = $ul_crc->search($search, 1);
        $results = $asset['data'];
      }

      if (!empty($results)) {
        foreach ($results as $data) {
          // Load any existing assets.
          $asset_entity = $this->entityTypeManager
            ->getStorage('crc_asset')
            ->loadByProperties(['name' => $data['name']]);

          // If any entity exist then use those.
          if (!empty($asset_entity)) {
            foreach ($asset_entity as $asset) {
              $new_values[] = ['target_id' => $asset->getCrcId()];
            }
          }
          // If asset doesn't already exist then create a new one.
          else {
            // Add CRC asset into DB table.
            $asset_entity = $ul_crc->saveNewCrcAsset($data, $this->currentUser->id(), $asset_entity->getLangcode);

            $new_values[] = ['target_id' => $asset_entity->getCrcId()];
          }
        }
      }
      return $new_values;
    }
  }

}

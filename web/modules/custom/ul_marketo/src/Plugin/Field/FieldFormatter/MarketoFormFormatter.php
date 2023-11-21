<?php

namespace Drupal\ul_marketo\Plugin\Field\FieldFormatter;

use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\node\NodeInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\ul_marketo\Entity\MarketoForm;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\ul_marketo\UlMarketoServiceInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\ul_marketo\Plugin\Field\FieldType\MarketoForm as MarketoFormField;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Plugin implementation of the 'marketo_form_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "marketo_form_formatter",
 *   label = @Translation("Marketo Form"),
 *   field_types = {
 *     "marketo_form"
 *   }
 * )
 */
class MarketoFormFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * CurrentRouteMatch object.
   *
   * @var \Drupal\Core\Routing\ResettableStackedRouteMatchInterface
   */
  private $routeMatch;

  /**
   * The marketo service.
   *
   * @var \Drupal\ul_marketo\UlMarketoServiceInterface
   */
  private $marketoService;

  /**
   * Entity type manager interface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
      $container->get('current_route_match'),
      $container->get('ul_marketo'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, CurrentRouteMatch $routeMatch, UlMarketoServiceInterface $marketo_service, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->routeMatch = $routeMatch;
    $this->marketoService = $marketo_service;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      // Implement default settings.
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [
      // Implement settings form.
    ] + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    // Implement settings summary.
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      // Create render array.
      $elements[$delta] = $this->viewValue($item);
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string
   *   The textual output generated.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function viewValue(FieldItemInterface $item) {
    // @todo Should not have to rely on route match. Consider updating this
    // to use a better method of grabbing entitiy.
    $entity = $this->routeMatch->getParameter('node');
    if (is_object($entity)) {
      // Build the form that is needed for the CTA banner paragraph and other
      // places.
      $block = $this->buildBlock($item, $entity);
    }
    else {
      $block = [];
    }

    return $block;
  }

  /**
   * Builds the display of the form within a paragraph and/or block context.
   *
   * @param Drupal\ul_marketo\Plugin\Field\FieldType\MarketoForm $item
   *   An instance of the marketo form field.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity where the field lives.
   *
   * @return array
   *   The render array of the block.
   */
  protected function buildBlock(MarketoFormField $item, EntityInterface $entity) {
    $langcode = $this->marketoService->getCurrentLanguage();
    $settings = [];
    $type = $item->getValue() ?? '';
    $parent = $item->getEntity();
    // We need to check if the parent has settings. If not, we go with the
    // current entity.
    $settings = $this->marketoService->getEntitySettings($parent);
    if (!$settings && $entity instanceof NodeInterface && $entity->hasField('field_shared_marketo_custom')) {
      $settings = $this->marketoService->getEntitySettings($entity);
    }
    // Load the default(fallback) MarketoForm entity if there is no any
    // MarkdetoForm added in the field_shared_marketo_custom.
    if (!$settings) {
      return [];
      /*
      $defaultForm = $this->getDefaultMarketoForm($item->getString());
      $settings = $this->marketoService->getFormDefaultSettings($defaultForm);
       */
    }

    // @todo Find a better way to handle this.
    // If there's no form type or this isn't an industry instance, go back.
    if (empty($type) && empty($this->marketoSettings['by_industry'])) {
      return [];
    }
    $page_url = '';
    // Nested if to check if key exists and if it has value.  Also need to make
    // sure we're getting correct translation.
    if ($form = $settings['form_entity']) {
      if ($form instanceof MarketoForm && $form->getPageUrl()) {
        // Check if a translation exists on this form.
        if ($form->hasTranslation($langcode)) {
          $form = $form->getTranslation($langcode);
        }
        $page_url = Link::fromTextAndUrl(
          $settings['field_shared_button_text'],
          Url::fromUserInput($form->getPageUrl())
        );
      }
    }

    $theme = [
      '#theme' => 'marketo_block',
      '#id' => $settings['id'],
      '#title' => $settings['label'],
      '#page_url' => $page_url,
      '#success_url' => $this->marketoService->getSuccessUrl($settings),
      '#settings' => $settings,
      '#marketo' => $this->marketoService->getThemeSettings($settings),
      '#cache' => [
        'contexts' => [
          'url.path',
        ],
      ],
    ];

    // Pass entity values to theme including caching.
    if (!empty($this->entity)) {
      $theme['#entity'] = $this->entity;
      $theme['#url'] = $this->entity->toUrl();
      $theme['#cache']['tags'] = $this->entity->getCacheTags();
      $theme['#cache']['max-age'] = $this->entity->getCacheMaxAge();
    }

    // Force cache disable if not set.
    if (!$this->marketoService->getConfig()->get('cache_enable')) {
      $theme['#cache']['max-age'] = 0;
    }

    return $theme;
  }

  /**
   * Get the default(fallback) MarketoForm entity.
   *
   * @param string $formType
   *   The MarketForm bundle name.
   *
   * @return \Drupal\ul_marketo\Entity\MarketoFormInterface
   *   The MarketForm enitity object.
   */
  public function getDefaultMarketoForm($formType) {
    // Call function of UlMarketoServiceInterface to get default MarketoForm.
    return $this->marketoService->getOrCreateDefaultMarketoForm($formType);
  }

}

<?php

namespace Drupal\ul_editorial;

use Drupal\filter\FilterUninstallValidator;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Extension\ModuleUninstallValidatorInterface;

/**
 * Uninstalls UL Editorial filters when module is uninstalled.
 */
class UlEditorialUninstallValidator extends FilterUninstallValidator {

  use StringTranslationTrait;

  /**
   * The filter plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $filterManager;

  /**
   * The filter entity storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface
   */
  protected $filterStorage;

  /**
   * The module uninstall validator.
   *
   * @var \Drupal\Core\Extension\ModuleUninstallValidatorInterface
   */
  protected $innerService;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new UlEditorialUninstallValidator.
   *
   * @param \Drupal\Core\Extension\ModuleUninstallValidatorInterface $inner_service
   *   The FilterUninstallValidator service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $filter_manager
   *   The filter plugin manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(ModuleUninstallValidatorInterface $inner_service, ConfigFactoryInterface $config_factory, PluginManagerInterface $filter_manager, EntityTypeManagerInterface $entity_type_manager, TranslationInterface $string_translation) {
    $this->innerService = $inner_service;
    $this->configFactory = $config_factory;
    parent::__construct($filter_manager, $entity_type_manager, $string_translation);
  }

  /**
   * {@inheritdoc}
   */
  public function validate($module) {
    $reasons = [];

    if ($module !== 'ul_editorial') {
      parent::validate($module);
    }
    else {
      // Get filter plugins supplied by this module.
      if ($filter_plugins = $this->getFilterDefinitionsByProvider($module)) {
        $used_in = [];
        // Find out if any filter formats have the plugin enabled.
        foreach ($this->getEnabledFilterFormats() as $filter_format) {
          $filters = $filter_format->filters();
          foreach ($filter_plugins as $filter_plugin) {
            if ($filters->has($filter_plugin['id']) && $filters->get($filter_plugin['id'])->status) {
              $used_in[] = $filter_format->get('format');
            }
          }
        }
        foreach ($used_in as $format) {
          $config = $this->configFactory->getEditable("filter.format.{$format}");
          $existing_filters = $config->get('filters');
          unset($existing_filters['filter_lazyload']);
          $config->set('filters', $existing_filters);
          try {
            $config->save();
          }
          catch (\Exception $e) {
            $reasons[] = $this->t('There was an issue uninstalling the UL Editorial Module: %error', ['%error' => $e->getMessage()]);
          }
        }
      }
    }
    return $reasons;
  }

}

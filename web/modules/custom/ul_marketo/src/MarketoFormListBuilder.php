<?php

namespace Drupal\ul_marketo;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManager;

/**
 * Defines a class to build a listing of Marketo Form entities.
 *
 * @ingroup marketo
 */
class MarketoFormListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [
      'id' => [
        'data' => $this->t('Form ID'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
      'name' => [
        'data' => $this->t('Name - (Click to edit)'),
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      'type' => [
        'data' => $this->t('Type'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
    ];
    if (\Drupal::languageManager()->isMultilingual()) {
      $header['language_name'] = [
        'data' => $this->t('Language'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ];
    }
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $name = $entity->getName();
    $row = [];

    $row['id'] = Link::createFromRoute(
      $entity->id(),
      'entity.marketo_form.edit_form',
      ['marketo_form' => $entity->id()]
    );

    $row['name'] = Link::createFromRoute(
      $name,
      'entity.marketo_form.edit_form',
      ['marketo_form' => $entity->id()]
    );

    $row['type'] = $entity->bundle();

    $langcode = $entity->language()->getId();
    $uri = $entity->toUrl();
    $options = $uri->getOptions();
    $options += ($langcode != LanguageInterface::LANGCODE_NOT_SPECIFIED && isset($languages[$langcode]) ? ['language' => $languages[$langcode]] : []);
    $uri->setOptions($options);

    $language_manager = \Drupal::languageManager();

    if ($language_manager->isMultilingual()) {
      list($langcode, $langname) = $this->getCurrentLangNameAndId($language_manager);

      $row['language_name'] = $langname;
      $row['operations']['data'] = $this->buildOperations($entity);
    }
    return $row + parent::buildRow($entity);
  }

  /**
   * Loads entity IDs using a pager sorted by the entity id.
   *
   * @param \Drupal\Core\Language\LanguageManager $language_manager
   *   An instance of LanguageManager.
   *
   * @return array
   *   An array of language id and name.
   */
  protected function getCurrentLangNameAndId(LanguageManager &$language_manager) {
    $current_language = $language_manager->getCurrentLanguage();
    return [$current_language->getId(), $current_language->getName()];
  }

  /**
   * Loads entity IDs using a pager sorted by the entity id.
   *
   * @return array
   *   An array of entity IDs.
   */
  protected function getEntityIds() {
    $language_manager = \Drupal::languageManager();
    list($langcode,) = $this->getCurrentLangNameAndId($language_manager);
    $query = $this->getStorage()
      ->getQuery()
      ->condition('name', 'Default', 'CONTAINS')
      ->condition('langcode', $langcode, '=')
      ->accessCheck(FALSE)
      ->sort($this->entityType->getKey('id'));

    // Only add the pager if a limit is specified.
    if ($this->limit) {
      $query->pager($this->limit);
    }
    return $query->execute();
  }

  /**
   * {@inheritdoc}
   *
   * Builds the entity listing as renderable array for table.html.twig.
   */
  public function render() {
    // Title of the languages.
    $build['page_header'] = [
      '#prefix' => '<h3 class="marketo-select-lang">',
      '#suffix' => '</h3>',
      '#markup' => $this->t('Choose a language to show the list'),
      '#attributes' => ['class' => ['title-list-marketo']],
      '#weight' => -11,
    ];

    // Build form render item.
    $build['table'] = [
      '#type' => 'table',
      '#header' => $this->buildHeader(),
      '#title' => $this->getTitle(),
      '#rows' => [],
      '#weight' => 0,
      '#empty' => $this->t('There are no @label yet.', ['@label' => $this->entityType->getPluralLabel()]),
      '#cache' => [
        'contexts' => $this->entityType->getListCacheContexts(),
        'tags' => $this->entityType->getListCacheTags(),
      ],
    ];

    // Load the MarketoForm entity ids.
    foreach ($this->load() as $entity) {
      if ($row = $this->buildRow($entity)) {
        $build['table']['#rows'][$entity->id()] = $row;
      }
    }

    // List of languages.
    $build['languages'] = [
      '#theme' => 'item_list',
      '#type' => 'ol',
      '#items' => $this->getLinksForLanguages(),
      '#weight' => -10,
      '#attributes' => ['class' => ['marketo-lang-ul']],
      '#attached' => [
        'library' => ['ul_marketo/ul_marketo.admin'],
      ],
    ];

    // Only add the pager if a limit is specified.
    if ($this->limit) {
      $build['pager'] = [
        '#type' => 'pager',
      ];
    }
    return $build;
  }

  /**
   * Loads entity langcodes sorted by the langcode.
   *
   * @return array
   *   An array of languages (langcodes).
   */
  protected function getMarketoFormLanguages() {
    $connection = \Drupal::database();
    $results = $connection->select('marketo_form_field_data', 'm')
      ->condition('name', '%Default %', 'LIKE')
      ->fields('m', ['langcode'])
      ->distinct()
      ->orderBy('langcode')
      ->execute()
      ->fetchAll();
    $languages = [];
    foreach ($results as $row) {
      $languages[] = $row->langcode;
    }
    return $languages;
  }

  /**
   * Generate array of Links of languages.
   *
   * @return array
   *   An array of Links of languages.
   */
  protected function getLinksForLanguages() {
    $links = [];
    $languages = $this->getMarketoFormLanguages();
    $host = \Drupal::request()->getHost();

    foreach ($languages as $langcode) {
      $langname = \Drupal::languageManager()->getLanguageName($langcode);

      $str = ($langcode == 'en') ? "/" : "/" . $langcode . "/";
      $str2 = "https://$host" . $str . "admin/content/marketo_form";
      $url = Url::fromUri($str2);
      // Throws a phpcs error about variable passed to t().
      // However, we do want to translate all possible language combinations.
      $link = Link::fromTextAndUrl($this->t("($langcode) $langname"), $url);
      $links[] = $link;
    }

    return $links;
  }

}

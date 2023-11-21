<?php

namespace Drupal\ul_crc_asset\Plugin\views\query;

use Drupal\ul_crc\CRCService;
use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\views\ResultRow;
use Drupal\Core\Cache\Cache;

/**
 * UL CRC views query plugin to expose results to views from CRC.
 *
 * @ViewsQuery(
 *   id = "ul_crc_query",
 *   title = @Translation("UL CRC Asset service"),
 *   help = @Translation("Query against the UL CRC API.")
 * )
 */
class CRCAssetQuery extends QueryPluginBase {

  /**
   * CRCAssetQuery constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\ul_crc\CRCService $ul_crc
   *   UL CRC service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CRCService $ul_crc) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->ul_crc = $ul_crc;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('ul_crc')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(ViewExecutable $view) {
    $this->view = $view;

    // Initialize the pager and let it modify the query to add limits. This has
    // to be done even for aborted queries since it might otherwise lead to a
    // fatal error when Views tries to access $view->pager.
    $view->initPager();
    $view->pager->query();
    $current_page = $view->pager->getCurrentPage();
    $this->view->setCurrentPage($current_page);

    // Need to invalidate the views cache for this display because it
    // assumes we are passing normal view query arguments and thus caching
    // on data that doesn't exist.
    $cache = $view->display_handler->getPlugin('cache');

    $tags = $cache->getCacheTags();
    Cache::invalidateTags($tags);
  }

  /**
   * {@inheritdoc}
   */
  public function ensureTable($table, $relationship = NULL) {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function addField($table, $field, $alias = '', $params = []) {
    return $field;
  }

  /**
   * {@inheritdoc}
   */
  public function loadEntities(&$results) {

    // Query results get cached but not the entities we need to make sure
    // they get loaded.
    if (!empty($results)) {
      foreach ($results as $index => $row) {
        $entity = \Drupal::entityTypeManager()->getStorage('crc_asset')->load($row->id);
        if (!empty($entity)) {
          $results[$index]->_entity = $entity;
        }
        // If the entity doesn't exist then remove this result otherwise it
        // will trigger an error.
        else {
          unset($results[$index]);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function execute(ViewExecutable $view) {
    // Default CRC API query parameter /search?keyword=&language=en.
    $langcode = 'en';
    $params = ['keyword' => "", 'language' => $langcode];
    $results = [];
    $page = $view->getCurrentPage();

    // Parse the submitted exposed filter values and pass it as a search to the
    // crc service.
    // Right now only the filename field is searchable. Maybe later we'll add
    // more.
    // @todo Implement fields mapping.
    $input = $view->getExposedInput();
    foreach ($input as $field => $value) {
      if (isset($view->field[$field])) {
        if ($field == 'filename') {
          $params['keyword'] = $value;
        }
        else {
          $params[$field] = $value;
        }
      }
      if ($field == 'language') {
        // Force to english if empty/null?
        if ($value !== 'All') {
          if (isset($value)) {
            $langcode = $value;
          }
          $params['language'] = $langcode;
        }
      }
    }

    $filters = $view->display_handler->getOption('filters');
    foreach ($filters as $filter) {
      // Do not process exposed filters. That happens earlier.
      if ($filter['exposed'] == FALSE) {

        // Add logic for handling boolean plugins.
        // These fields should be set to 'true' or 'false'.
        if ($filter['plugin_id'] == 'boolean') {
          switch ($filter['operator']) {
            case '=':
              $params[$filter['id']] = ($filter['value'] == 1) ? 'true' : 'false';
              break;

            case '!=':
              $params[$filter['id']] = ($filter['value'] != 1) ? 'false' : 'true';
              break;

          }
        }
        // Default to string value comparison plugins.
        else {
          $params[$filter['id']] = $filter['value'];
        }
      }
    }

    // Update the view build array, this is to help with view caching.
    $view->build_info['query_args'] = $params['keyword'];
    // Which page of results are we on?
    // By default Views interprets pages starting with 0.
    // CRC starts at 1 so we need to increment Views page number.
    $page += 1;
    // Search all CRC asset files via RESTful API request when there is no
    // keyword provided instead of showing a list of Drupal DB records.
    $results = $this->ul_crc->search($params, $page);

    // Update pager information to match the data that came from UL's CRC
    // service.
    $pager = $results['pagination_info'];
    if (!empty($params['keyword'])) {
      // Decrement page number by 1, only if it's a CRC search.
      $page -= 1;
    }
    $view->setCurrentPage($page);
    $view->setItemsPerPage($pager['per_page']);
    $view->pager->total_items = $view->total_rows = $pager['total_count'];

    if (!empty($results['data'])) {

      // $index is required in order for the rows to render correctly.
      $index = 0;
      // Get current user ID or default to 0.
      $user_id = \Drupal::currentUser()->id();
      if (!$user_id) {
        $user_id = 0;
      }

      foreach ($results['data'] as $data) {
        // Create an new asset entity during run time. Entity Browser expects
        // entities to be in the database to we need to generate these entities
        // and perform regular cleanup for any that aren't used.
        // @see ul_crc_asset_cron().
        // Make sure entity doesn't already exist.
        // Add CRC asset into DB table.
        if (!isset($langcode) && isset($data['available_languages'][0])) {
          $langcode = $data['available_languages'][0];
        }

        $asset_entity = $this->ul_crc->loadCrcAssetDb($data['id'], $langcode);

        if (!$asset_entity || empty($asset_entity)) {
          $asset_entity = $this->ul_crc->saveNewCrcAsset($data, $user_id, $langcode);
          if (!$asset_entity) {
            continue;
          }
        }

        $sm_thumbnail = $this->getImgPlaceholder($data['sm_thumbnail_url']);
        $med_thumbnail = $this->getImgPlaceholder($data['med_thumbnail_url']);
        $row['id'] = $asset_entity->getCrcId();
        $row['filename'] = $data['name'];
        $row['file_extension'] = $data['file_extension'];
        $row['created_at'] = strtotime($data['created_at']);
        $row['updated_at'] = strtotime($data['updated_at']);
        $row['sm_thumbnail_url'] = $sm_thumbnail;
        $row['file_type'] = $data['file_type'];
        $row['med_thumbnail_url'] = $med_thumbnail;
        $row['original_url'] = $data['original_url'];
        $row['_entity'] = $asset_entity;
        $row['index'] = $index++;
        $row['language'] = $langcode;
        $resultRow = new ResultRow($row);
        $view->result[] = $resultRow;
      }

      // Make sure that the pager is up to date.
      $view->pager->postExecute($view->result);
      $view->pager->updatePageInfo();

    }
  }

  /**
   * {@inheritdoc}
   */
  public function addWhere($group, $field, $value = NULL, $operator = NULL) {
    // Overriding this so that we don't do any queries against a table.
  }

  /**
   * Get default placeholder image if the file doesn't exit or not an image.
   *
   * @param string $file
   *   The thumbail image URL.
   *
   * @return string
   *   The default placehoder image URL.
   */
  protected function getImgPlaceholder($file) {
    if (isset($file)) {
      return $file;
    }
    else {
      return "https://crc.ul.com/file_placeholder-01.png";
    }
  }

}

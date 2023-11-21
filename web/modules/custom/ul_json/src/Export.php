<?php

namespace Drupal\ul_json;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Class Export service.
 */
class Export {

  /**
   * The logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The Drupal cache service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The default langcode of the site.
   *
   * @var string
   */
  public $defaultLang;

  /**
   * The cached TRUE of FALSE.
   *
   * @var bolleen
   */
  protected $cached;

  /**
   * The storage handler class for files.
   *
   * @var \Drupal\file\FileStorage
   */
  protected $fileStorage;

  /**
   * The storyage handler class for paragraphs.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $paragraphStorage;

  /**
   * The target of usage: PoerBI or Email.
   *
   * @var int
   */
  protected $target;

  /**
   * Constructs an Export object.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger channel factory.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The Lanuage Manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The Request stack.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The Drupal cache.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The file ulr generator.
   */
  public function __construct(LoggerChannelFactoryInterface $logger, RendererInterface $renderer, FileSystemInterface $file_system, Connection $connection, ConfigFactoryInterface $config_factory, LanguageManagerInterface $language_manager, RequestStack $request_stack, CacheBackendInterface $cache, EntityTypeManagerInterface $entity_manager) {
    $this->logger = $logger;
    $this->renderer = $renderer;
    $this->fileSystem = $file_system;
    $this->connection = $connection;
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
    $this->requestStack = $request_stack;
    $this->cache = $cache;

    $this->fileStorage = $entity_manager->getStorage('file');
    $this->paragraphStorage = $entity_manager->getStorage('paragraph');
    $this->defaultLang = $this->languageManager->getDefaultLanguage()->getId();
    $this->cached = TRUE;
    // The value: 1=>PowerBI; 2=>Email.
    $this->target = 0;
  }

  /**
   * Export the JSON data.
   *
   * @param string $token
   *   The access_token string.
   * @param int $target
   *   The target of usage: PowerBI(1), Email(2), Chinese Feed(3).
   *
   * @return mix
   *   The JSON data.
   */
  public function exportJson($token, $target = 1) {
    $this->target = $target;
    $request = $this->requestStack->getCurrentRequest();
    $page = $request->get('page');

    $this->logger->get('php')->log(RfcLogLevel::INFO, 'Memory usage of ul_json before export: ' . $this->getMemoryUsage());

    $config = $this->configFactory->get('ul_json.settings');
    if ($config->get('access_token') !== md5($token)) {
      return FALSE;
    }

    $data = $this->getJsonDataCache($token, $page);
    $this->logger->get('php')->log(RfcLogLevel::INFO, 'Memory usage of ul_json after export: ' . $this->getMemoryUsage());

    return json_encode($data);
  }

  /**
   * Get or set the Cache data for the JSON data.
   *
   * @param string $url
   *   Takes the remote object's URL.
   * @param int $page
   *   The parameter 'page' in the URL.
   *
   * @return array
   *   Returns an big array of nodes.
   */
  protected function getJsonDataCache($url, $page = 1) {
    // Test the loading perforamce with 128M (DEV, STAGE).
    if ($this->cached == FALSE) {
      ini_set('memory_limit', '128M');
    }

    $tag = 'ul_json';
    $url .= $url . "-[" . $this->target . "]-[page:" . $page . "]";
    $cid = $this->generateCacheId('ul_json', $url);

    $data = NULL;
    if ($cache = $this->cache->get($cid)) {
      $data = $cache->data;
    }
    // Return cached data if it exists.
    if (!empty($data) && $this->cached == TRUE) {
      return $data;
    }
    else {
      // Run SQL query to get data from DB (1=>published).
      if ($this->target == 1) {
        $new = $this->selectData(1);

      }
      elseif ($this->target == 2) {
        $new = $this->selectData2(1, $page);
      }
      elseif ($this->target == 3) {

        $new = $this->selectData3(1, $page);
      }
      else {
        $new = [];
      }
      // Set the cache for the data, which expires after 8 hours.
      $this->cache->set($cid, $new, time() + 8 * 60 * 60, [$tag]);

      return $new;
    }

  }

  /**
   * Query DB for the data of all published nodes(AB#345508).
   *
   * @param int $arg
   *   Number of 1 or 0 (published or unpublished).
   *
   * @return mixed
   *   Array of nodes containing all data.
   */
  public function selectData($arg = 1) {
    $nodes = [];
    $default_lang = $this->defaultLang;

    if ($this->connection->schema()->tableExists('marketo_report')) {
      $sql = "SELECT  DISTINCT
      node_field_data.nid AS url,
      node_field_data.nid,
      node.uuid,
      node_field_data.created AS created_date,
      node_field_data.changed AS updated_date,
      node_field_data.title AS title,
      node_field_data.type AS content_type,
      node_field_data.langcode AS langcode,
      node_field_data.langcode AS language,

      marketo_report.last_interest AS last_interest,
      marketo_report.header_cta AS header_cta,
      marketo_report.rr_cta AS right_rail_cta,
      marketo_report.marketo_paragraphs AS body_cta,
      marketo_report.marketo_customizations
      FROM
        node_field_data
      INNER JOIN
        node on node.nid = node_field_data.nid
      LEFT JOIN
        marketo_report marketo_report
        ON node_field_data.nid = marketo_report.nid AND node_field_data.langcode = marketo_report.langcode
      WHERE node_field_data.status = 1
        -- AND node_field_data.nid IN (6,2761,4316,4251,3936,4016,4061,196726,194181,194176,197016,175921)
        -- AND node_field_data.nid IN (31, 6)
      ORDER BY updated_date DESC
      LIMIT 20000 OFFSET 0";

      $query = $this->connection->query($sql);
      $count = 0;

      while (($row = $query->fetchAssoc()) !== FALSE) {
        $count++;

        if ($this->target == 1) {
          $this->handleData4Powerbi($row, $default_lang);
          $nodes[] = $row;
        }
      }
    }
    return $nodes;

  }

  /**
   * Query DB for the data of all published nodes(AB#345508).
   *
   * @param int $arg
   *   Number of 1 or 0 (published or unpublished).
   * @param int $page
   *   Number of page in URL (?page=1).
   *
   * @return mixed
   *   Array of nodes containing all data.
   */
  public function selectData2($arg = 1, $page = 1) {
    $config = $this->configFactory->get('ul_json.settings');
    $nodes = [];
    // Fix the fatal error: Allowed memory size of 134217728 bytes
    // exhausted (tried to allocate 42521849 bytes, by pagination.
    $page = ($page < 1) ? 1 : $page;
    $start = ($page - 1) * 3000;
    $end = $page * 3000 - 1;

    // Handle the data for the target of newsletter/email.
    $condition_ct = "";
    if ($this->target == 2) {
      $types = $config->get('content_types');
      if (empty($types)) {
        $types = 'campaign_page,event,market_access_profile,knowledge,landing_page,news,offering,resource,tool';
      }
      $arr = array_map('trim', explode(',', $types));
      $types_str = "'" . implode("','", $arr) . "'";
      $condition_ct = ' AND node_field_data.type IN (' . $types_str . ')';
    }

    if ($this->connection->schema()->tableExists('marketo_report')) {
      $sql = "SELECT  DISTINCT
        node_field_data.nid,
        node.uuid,
        node_field_data.created AS created_date,
        node_field_data.changed AS updated_date,
        node_field_data.title AS title,
        node_field_data.type AS content_type,
        node_field_data.langcode AS langcode
      FROM
        node_field_data
      INNER JOIN
        node on node.nid = node_field_data.nid
      WHERE node_field_data.status = $arg
        $condition_ct
        -- AND node_field_data.nid IN (204486,197371)
      ORDER BY updated_date DESC
      LIMIT 20000 OFFSET 0";

      $query = $this->connection->query($sql);
      $count = 0;

      while (($row = $query->fetchAssoc()) !== FALSE) {
        if ($this->target == 2) {
          // Split the data into pagination (page=1, page=2, etc.)
          if ($count >= $start && $count <= $end) {
            $nodes[] = $this->handleData4Email($row);
          }
        }
        $count++;
      }
    }
    return $nodes;

  }

  /**
   * Query published nodes(AB#355901) for Chinese translation Feed.
   *
   * @param int $arg
   *   Number of 1 or 0 (published or unpublished).
   * @param int $page
   *   Number of page in URL (?page=1).
   *
   * @return mixed
   *   Array of nodes containing all data.
   */
  public function selectData3($arg = 1, $page = 1) {
    $config = $this->configFactory->get('ul_json.settings');
    $nodes = [];
    // Fix the fatal error: Allowed memory size of 134217728 bytes
    // exhausted (tried to allocate 42521849 bytes, by pagination.
    $batch_size = 100;
    $page = ($page < 1) ? 1 : $page;
    $start = ($page - 1) * $batch_size;
    $end = $page * $batch_size - 1;

    // Handle the data for the target of China team.
    $condition_ct = "";
    if ($this->target == 3) {
      $types = $config->get('content_types_china');
      if (empty($types)) {
        $types = 'campaign_page,event,knowledge,landing_page,news,offering,resource';
      }
      $arr = array_map('trim', explode(',', $types));
      $types_str = "'" . implode("','", $arr) . "'";
      $condition_ct = ' AND node_field_data.type IN (' . $types_str . ')';
    }

    if ($this->connection->schema()->tableExists('marketo_report')) {
      $sql = "SELECT  DISTINCT
        node_field_data.nid,
        node.uuid,
        node_field_data.created AS created_date,
        node_field_data.changed AS updated_date,
        node_field_data.title AS title,
        node_field_data.type AS content_type,
        node_field_data.langcode AS langcode,
        marketo_report.marketo_customizations
      FROM
        node_field_data
      INNER JOIN
        node on node.nid = node_field_data.nid
      LEFT JOIN
        marketo_report marketo_report
        ON node_field_data.nid = marketo_report.nid

      WHERE node_field_data.status = $arg
        AND node_field_data.langcode = 'zh-hans'
        $condition_ct
        -- AND node_field_data.nid IN (179301)
      ORDER BY updated_date DESC
      LIMIT 20000 OFFSET 0";

      $query = $this->connection->query($sql);
      $count = 0;

      while (($row = $query->fetchAssoc()) !== FALSE) {
        if ($this->target == 3) {
          // Split the data into pagination (page=1, page=2, etc.)
          if ($count >= $start && $count <= $end) {
            $nodes[] = $this->handleData4China($row);
          }
        }
        $count++;
      }
    }
    return $nodes;

  }

  /**
   * Handle the data for PowerPI JSON.
   *
   * @param array $row
   *   A row of data from DB.
   * @param string $default_lang
   *   The default langcode of a  node.
   */
  protected function handleData4Powerbi(array &$row, $default_lang) {
    // Get language name.
    $langcode = $row['langcode'];
    $language_name = $this->languageManager->getLanguage($langcode)->getName();
    if ($language_name) {
      $row['language'] = $language_name;
    }
    // Format date to 2023-06-16 10:06:19 PM.
    // Option: $date = date('Y-m-d H:i:s', $row['created_date']);.
    $row['created_date'] = date('r', $row['created_date']);
    $row['updated_date'] = date('r', $row['updated_date']);

    // Content type: offering, page, => field_of_service_type.
    $taxonomes = [
      'service_type', 'industry', 'products_components',
      'solutions', 'cou',
    ];
    foreach ($taxonomes as $vac) {
      // Handle terms including some speical cases.
      $row[$vac] = $this->selectTaxonomyTerm($vac, $row['nid'], $langcode, $row['content_type']);
    }

    $row['marketo_customizations'] = $this->selectMarketoCustom($row['nid'], $langcode);

    $row['header_cta'] = $this->getMarketFormName($row['header_cta']);
    $row['right_rail_cta'] = $this->getMarketFormName($row['right_rail_cta']);

    $row['url'] = $this->getPath($row['nid'], $langcode);

    if (empty($row['last_interest'])) {
      $row['last_interest'] = $this->getLastInterest($row['nid'], 'last_interest', $default_lang);
    }

  }

  /**
   * Handle the data for Newsletter/Email JSON.
   *
   * @param array $row
   *   A row of data from DB.
   *
   * @return array
   *   An array of node data.
   */
  protected function handleData4Email(array &$row) {
    // Get language name.
    $node = [];
    $langcode = $row['langcode'];
    $nid = $row['nid'];
    $language_name = $this->languageManager->getLanguage($langcode)->getName();
    if ($language_name) {
      $node['language'] = $language_name;
    }
    $node['title'] = $row['title'];
    // $node['langcode'] = $langcode;
    $node['nid'] = $nid;
    // $node['content_type'] = $row['content_type'];
    $node['url'] = $this->getPath($row['nid'], $langcode);
    $node['uuid'] = $row['uuid'];
    $node['description'] = $this->getDescription($nid, $langcode, 'header_description');
    $node['header_media'] = $this->getMedia($nid, $langcode);
    $node['summary'] = $this->getDescription($nid, $langcode, 'ref_description');
    $node['basic_content'] = $this->getBasicParagraph($nid, $langcode, $row['content_type']);
    $node['text_and_form'] = $this->getHalfParagraph($nid, $langcode, $row['content_type']);

    return $node;
  }

  /**
   * Handle the data for Newsletter/Email JSON.
   *
   * @param array $row
   *   A row of data from DB.
   *
   * @return array
   *   An array of node data.
   */
  protected function handleData4China(array &$row) {
    $node = [];
    $default_lang = $this->defaultLang;
    // Get language name.
    $langcode = $row['langcode'];
    $nid = $row['nid'];
    $language_name = $this->languageManager->getLanguage($langcode)->getName();
    if ($language_name) {
      $node['language'] = $language_name;
    }
    $node['title'] = $row['title'];
    // $node['langcode'] = $langcode;
    $node['nid'] = $nid;
    $node['updated_date'] = date('Y-m-d H:i:s', $row['updated_date']);
    // $node['content_type'] = $row['content_type'];
    $content_type = $row['content_type'];
    $node['type'] = $content_type;
    $node['url'] = $this->getPath($row['nid'], $langcode);
    $node['uuid'] = $row['uuid'];

    if ($content_type == "campaign_page") {
      $node['subtitle'] = $this->getSubtitle($nid, $langcode, 'subtitle');
    }
    else {
      $node['description'] = $this->getDescription($nid, $langcode, 'header_description');
    }

    if ($header_link = $this->getHeaderLink($nid, $langcode, 'header_link')) {
      $node['header_link'] = $header_link;
    }

    if ($header_media = $this->getMedia($nid, $langcode)) {
      $node['header_media'] = $header_media;
    }

    if ($issue_number = $this->getIssueNumber($nid, $langcode, 'issue_number')) {
      $node['issue_number'] = $issue_number;
    }

    if ($marketo_forms = $this->getMarketoForms($nid, $langcode, 'marketo_forms')) {
      $node['marketo_forms'] = $marketo_forms;
    }

    $node['summary'] = $this->getDescription($nid, $langcode, 'ref_description');

    // Content type: offering, page, => field_of_service_type.
    $taxonomes = [
      // All taxonomy terms in the Taxonomy Tab.
      'domain', 'content_owner', 'country', 'cou', 'market', 'products_components',
      'news_type', 'event_type', 'solutions', 'topic', 'service_type', 'industry',
    ];
    foreach ($taxonomes as $vac) {
      // Handle terms including some speical cases.
      if ($term = $this->selectTaxonomyTerm($vac, $row['nid'], $langcode, $content_type)) {
        $node[$vac] = $term;
      }
    }

    if ($news_date = $this->getNewsDate($nid, $langcode, 'news_date')) {
      $node['news_date'] = $news_date;
    }

    $node['marketo_customizations'] = $this->selectMarketoCustom($row['nid'], $langcode);

    $node['header_cta'] = $this->getCta($nid, 'marketo_link', $langcode);
    $node['right_rail_cta'] = $this->getCta($nid, 'rr_marketo_cta', $langcode);

    $node['url'] = $this->getPath($row['nid'], $langcode);

    if (empty($row['last_interest'])) {
      $node['last_interest'] = $this->getLastInterest($row['nid'], 'last_interest', $default_lang);
    }

    $node['paragraph_contents'] = $this->getContentParagraphs($nid, $langcode, $content_type);

    $rr_checked = $this->getRrCheckbox($nid, $langcode, 'rr_checkbox');
    $node['right_rail_display'] = $rr_checked;

    if ($rr_checked && $rr_related = $this->getRrRelated($nid, $langcode, 'rr_related')) {
      $node['sidebar_curated_related'] = $rr_related;
    }

    if ($content_type == 'campaign_page') {
      $node['closing_argument'] = $this->getCampaignPageButom($nid, $langcode, 'page_bottom');
    }

    if ($review_comments = $this->getReviewComments($nid, $langcode, 'review_comments')) {
      $node['review_comments'] = $review_comments;
    }

    if ($body = $this->getBody($nid, $langcode, 'body')) {
      $node['body'] = $body;
    }

    if ($metaTags = $this->getMetaTags($nid, $langcode, 'metatags')) {
      $node['metatags'] = $metaTags;
    }

    if ($view_mode = $this->getViewModeHero($nid, $langcode, 'view_mode')) {
      $node['view_mode'] = $view_mode;
    }

    if ($content_type == "event") {
      $node['event'] = $this->getEventData($nid, $langcode);
    }

    return $node;
  }

  /**
   * Get the data of all paragraphs.
   *
   * @param int $nid
   *   The node id.
   * @param string $langcode
   *   The language code.
   * @param string $type
   *   The node content_type.
   *
   * @return array
   *   Return a bit assoicate array storing all content of parageraph types.
   */
  protected function getContentParagraphs($nid, $langcode, $type) {
    $paragraphs = [];

    if ($type == 'market_access_profile') {
      return $paragraphs;
    }
    elseif ($type == 'offering') {
      // Possible values:  "of_content" or "offering_types".
      $type_field = $this->queryOfferingPara($nid, $langcode, $type);
      $table_node = "node__field_" . $type_field;
      $table_node_field = "field_" . $type_field . "_target_id";
    }
    elseif ($type == 'knowledge') {
      $table_node = "node__field_know_content";
      $table_node_field = "field_know_content_target_id";
    }
    else {
      $table_node = "node__field_" . $type . "_content";
      $table_node_field = "field_" . $type . "_content_target_id";
    }

    $sql = "SELECT $table_node_field AS pid
      FROM $table_node
      WHERE entity_id=$nid";

    $query = $this->connection->query($sql);
    while (($row = $query->fetchAssoc()) !== FALSE) {
      $pid = $row['pid'];

      $paragraphs[] = $this->getParagrahEntity($pid, $langcode);
    }

    return $paragraphs;
  }

  /**
   * Load the paragraph and return an array data.
   *
   * @param int $pid
   *   The paragraph id.
   * @param string $langcode
   *   The language code.
   *
   * @return array
   *   The array data of paragraph.
   */
  protected function getParagrahEntity($pid, $langcode) {
    $paragraph = [];
    $simplifiedParagraph = [];

    // Not output these fields.
    $ignore = [
      'status', 'created', 'behavior_settings', 'default_langcode', 'revision_default', 'revision_translation_affected',
      'content_translation_source', 'content_translation_outdated', 'content_translation_changed', 'field_hash_target', 'langcode',
    ];
    $entity = $this->paragraphStorage->load($pid);
    if ($entity instanceof Paragraph) {

      if ($entity->hasTranslation($langcode)) {
        $paragraph = $entity->getTranslation($langcode)->toArray();
      }
    }
    foreach ($paragraph as $key => $value) {
      if (in_array($key, $ignore)) {
        continue;
      }
      if (!empty($value)) {
        if (isset($value[0]['value'])) {
          $simplifiedParagraph[$key] = $value[0]['value'];
        }
        if (isset($value[0]['uri'])) {
          $simplifiedParagraph[$key] = $value[0];
        }
        if (isset($value[0]['target_id'])) {
          for ($i = 0; $i < count($value); $i++) {
            $simplifiedParagraph[$key][] = $value[$i]['target_id'];
          }
        }
      }
    }
    return $simplifiedParagraph;
  }

  /**
   * Get the Taxonomy data with SQL.
   *
   * @param string $table
   *   The partial string of the field table.
   * @param int $nid
   *   The Node id.
   * @param string $langcode
   *   The language code.
   * @param string $type
   *   The content type.
   */
  protected function selectTaxonomyTerm($table, $nid, $langcode, $type) {
    if (empty($nid) || empty($table) || empty($langcode)) {
      return "";
    }
    $default_lang = $this->defaultLang;
    // Content type: ['page', 'offering'] => field_of_service_type.
    if (in_array($type, ['page', 'offering']) && $table == 'service_type') {
      $table_name = "node__field_of_$table";
      $field_name = "field_of_" . $table . "_target_id";
    }
    elseif ($table == 'news_type' || $table == 'event_type') {
      $table_name = "node__field_$table";
      $field_name = "field_" . $table . "_target_id";
    }
    else {
      $table_name = "node__field_shared_$table";
      $field_name = "field_shared_" . $table . "_target_id";
    }

    $arr = [];

    $str = "
      SELECT count(DISTINCT $field_name) AS ids
      FROM $table_name n
      WHERE n.entity_id = $nid
      AND n.langcode = '$langcode'
    ";
    $query = $this->connection->query($str);
    $row = $query->fetchAssoc();
    if ($row['ids'] <= 0) {
      $langcode = $default_lang;
    }

    $str = "
      SELECT DISTINCT entity_id AS nid, langcode, $field_name AS tid
      FROM $table_name n
      WHERE n.entity_id = $nid
      AND n.langcode = '$langcode'
    ";

    $query = $this->connection->query($str);
    while (($row = $query->fetchAssoc()) !== FALSE) {
      $term = $this->handleSpecialTerms($row['tid'], $langcode, $default_lang);
      if ($term && !in_array($term, $arr)) {
        $arr[] = $term;
      }
    }

    return $arr;
  }

  /**
   * Handle the terms of translation node.
   *
   * @param int $tid
   *   The term id.
   * @param string $langcode
   *   The langcode.
   * @param string $default_lang
   *   The default langcode.
   *
   * @return string
   *   The term name.
   */
  protected function handleSpecialTerms($tid, $langcode, $default_lang) {
    $str = "
      SELECT DISTINCT tfd.name
      FROM taxonomy_term_field_data tfd
      WHERE tfd.tid=$tid  AND tfd.langcode='$langcode'
      ";
    $query = $this->connection->query($str);
    $row = $query->fetchAssoc();
    if (!empty($row) && $row !== FALSE) {
      return trim($row['name']);
    }
    else {
      unset($query);
      $str2 = "
        SELECT DISTINCT tfd.name
        FROM taxonomy_term_field_data tfd
        WHERE tfd.tid=$tid  AND tfd.langcode='$default_lang'
      ";

      $query = $this->connection->query($str2);
      while (($row = $query->fetchAssoc()) !== FALSE) {
        return trim($row['name']);
      }
    }
  }

  /**
   * Get the marketo customizations data with SQL.
   *
   * @param int $nid
   *   The Node id.
   * @param string $langcode
   *   The language code.
   */
  protected function selectMarketoCustom($nid, $langcode = 'en') {
    if (empty($nid) || empty($langcode)) {
      return [];
    }

    $sql = "
      SELECT
      title.bundle AS type,
      m_data.id AS  mid,
      title.field_shared_custom_title_value AS title,
      descr.field_shared_form_description_value AS description,
      button.field_shared_button_text_value AS button
      FROM
      marketo_form_field_data m_data
      LEFT JOIN marketo_form__field_shared_custom_title title
      ON m_data.id = title.entity_id AND m_data.langcode = title.langcode
      LEFT JOIN marketo_form__field_shared_form_description descr
      ON  m_data.id = descr.entity_id AND m_data.langcode = descr.langcode
      LEFT JOIN marketo_form__field_shared_button_text button
      ON  m_data.id = button.entity_id AND m_data.langcode = button.langcode
      WHERE
      m_data.langcode = '$langcode'
      AND m_data.id IN (
        SELECT DISTINCT field_shared_marketo_custom_target_id
        FROM node__field_shared_marketo_custom WHERE entity_id = $nid);
    ";

    $query = $this->connection->query($sql);

    $count = 0;
    $marketos = [];
    while (($row = $query->fetchAssoc()) !== FALSE) {
      $mid = $row['mid'];

      $count++;
      $marketos[$mid] = [
        'type' => $row['type'],
        'type_name' => $this->getMarketFormName($row['type']),
        'title' => $row['title'],
        'description' => $row['description'],
        'button_text' => $row['button'],
      ];
    }

    return $marketos;
  }

  /**
   * Get the name of marketform.
   *
   * @param string $form_id
   *   The string of form_id.
   *
   * @return string
   *   The anme of form name.
   */
  public function getMarketFormName($form_id) {
    $name = "";
    if ($form_id) {
      $list = [
        'generic_form' => 'Contact Form (old)',
        'contact_form_configurable' => 'Contact Form (new)',
        'email_form' => 'Newsletter Form',
        'event_form' => 'Event Registration',
        'gated_content_form' => 'Gated Content Form',
        'mkto_pref_ctr' => 'Preference Center',
      ];
      $name = $list[$form_id];
    }
    return $name;

  }

  /**
   * Get the path alias of a node.
   *
   * @param int $nid
   *   The node ID.
   * @param string $langcode
   *   The language code.
   *
   * @return string
   *   The path alias, URL.
   */
  protected function getPath($nid, $langcode = "") {
    if (!$nid) {
      return "";
    }
    $front_path = $this->configFactory->get('system.site')->get('page.front');
    $host = $this->requestStack->getCurrentRequest()->getHost();
    if ($front_path == "/node/$nid") {
      $path = (!empty($langcode) && $langcode <> $this->defaultLang) ? "/$langcode" : "/";
      return "https://$host" . $path;
    }
    else {
      $path = "";
    }

    $path_node = (!empty($langcode) && $langcode <> $this->defaultLang) ? "/$langcode/node/$nid" : "/node/$nid";

    $sql = "
      SELECT base_table.alias AS alias
      FROM
        path_alias base_table
      WHERE base_table.path = '/node/$nid'
        AND base_table.langcode IN ('$langcode')
      ORDER BY base_table.id DESC
      ";

    $query = $this->connection->query($sql);

    if (($row = $query->fetchAssoc()) !== FALSE) {
      $path = (!empty($langcode) && $langcode <> 'en') ? "/$langcode" . $row['alias'] : $row['alias'];
    }
    if (empty(trim($path))) {
      $path = $path_node;
    }
    return "https://$host" . $path;
  }

  /**
   * Get the size of memory usage function.
   *
   * @return string
   *   The size of memerory usage.
   */
  public function getMemoryUsage() {

    $size = memory_get_usage(TRUE);
    $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];
    return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
  }

  /**
   * Generate cache ID.
   *
   * @param string $prefix
   *   The unique cache prefix.
   * @param string $args
   *   An array of request parameters.
   *
   * @return string
   *   The full cache id string.
   */
  private function generateCacheId($prefix, $args) {
    if (!is_string($args)) {
      $args = (string) $args;
    }
    $cache_key = Crypt::hashBase64(serialize($args));
    return $prefix . ':' . $cache_key;
  }

  /**
   * Get the default value of last_interest if it null.
   *
   * @param int $nid
   *   The node ID (entity_id).
   * @param string $field_name
   *   The column name in the DB table.
   * @param string $langcode
   *   The language code.
   *
   * @return string
   *   The value of last_interest
   */
  private function getLastInterest($nid, $field_name, $langcode = 'en') {
    if (!$nid || !$field_name) {
      return "";
    }

    // Example, field_shared_marketo_forms_last_interest .
    $field_name = "field_shared_marketo_forms_" . $field_name;
    $sql = "
      SELECT $field_name AS last_interest
      FROM
        node__field_shared_marketo_forms
      WHERE entity_id = $nid
        AND langcode = '$langcode'
      ";

    $query = $this->connection->query($sql);

    while (($row = $query->fetchAssoc()) !== FALSE) {

      return $row['last_interest'];
    }

  }

  /**
   * Get the description data.
   *
   * @param int $nid
   *   The node id.
   * @param string $langcode
   *   The language code.
   * @param string $field
   *   The partial name of the table.
   *
   * @return string
   *   Return the description.
   */
  private function getDescription($nid, $langcode, $field) {
    // node__field_shared_header_description.
    // node__field_shared_ref_description.
    $str = '';
    $table_name = "node__field_shared_" . $field;
    $field_name = "field_shared_" . $field . "_value";
    $sql = "
      SELECT $field_name AS description
      FROM
        $table_name
      WHERE entity_id = $nid
        AND langcode = '$langcode'
      ";

    $query = $this->connection->query($sql);

    if (($row = $query->fetchAssoc()) !== FALSE) {
      $str = $row['description'];
    }
    return (empty($str)) ? $this->getSubtitle($nid, $langcode, $field) : $str;
  }

  /**
   * Get the Subtitle if no Description.
   *
   * @param int $nid
   *   Node id.
   * @param string $langcode
   *   Language code.
   * @param string $field
   *   Field id name.
   *
   * @return string
   *   The content of Subtitle.
   */
  private function getSubtitle($nid, $langcode, $field) {
    $str = "";
    // node__field_shared_subtitle.
    $table_name = "node__field_shared_subtitle";
    $field_name = "field_shared_subtitle_value";
    $sql = "
      SELECT $field_name AS description
      FROM
        $table_name
      WHERE entity_id = $nid
        AND langcode = '$langcode'
      ";

    $query = $this->connection->query($sql);

    if (($row = $query->fetchAssoc()) !== FALSE) {
      $str = $row['description'];
    }
    return $str;
  }

  /**
   * Get the header media data.
   *
   * @param int $nid
   *   The node id.
   * @param string $langcode
   *   The language code.
   *
   * @return string
   *   Return the description.
   */
  private function getMedia($nid, $langcode) {
    // node__field_shared_header_media: field_shared_header_media_target_id.
    $str = '';
    $table_name = "node_media_file";
    $table_name_2 = 'node__field_shared_header_media';
    $field_name = "fid";
    $sql = "
      SELECT $field_name  FROM   $table_name AS nmf
      INNER JOIN $table_name_2 AS nfm
        ON nmf.nid = nfm.entity_id
        AND nmf.langcode = nfm.langcode
        AND nmf.mid = nfm.field_shared_header_media_target_id
      WHERE nid = $nid  AND nmf.langcode = '$langcode'
      ORDER BY nmf.mid DESC ";

    $query = $this->connection->query($sql);

    if (($row = $query->fetchAssoc()) !== FALSE) {
      $str = $this->getMediaUri($row['fid']);
    }
    return $str;
  }

  /**
   * Get the thumbnail url for an image.
   *
   * @param int $id
   *   The Meida file target_id.
   *
   * @return string
   *   The URI of thmbnail.
   */
  protected function getMediaUri($id) {
    $host = $this->requestStack->getCurrentRequest()->getHost();
    // $file = File::load($id);
    if ($file = $this->fileStorage->load($id)) {
      $relative_file_url = $file->createFileUrl(TRUE);
    }
    else {
      $relative_file_url = "";
    }
    return "https://$host$relative_file_url";
  }

  /**
   * Get the summary(ref_description) data.
   *
   * @param int $nid
   *   The node id.
   * @param string $langcode
   *   The language code.
   * @param string $type
   *   The node content_type.
   *
   * @return string
   *   Return the description.
   */
  private function getBasicParagraph($nid, $langcode, $type) {
    // node__field_shared_ref_description.
    $str = $this->queryParagraphs($nid, $langcode, $type, 'basic_content');
    if (empty(trim($str))) {
      $str = $this->queryParagraphs($nid, 'en', $type, 'basic_content');
    }
    return $str;
  }

  /**
   * Get the summary(ref_description) data.
   *
   * @param int $nid
   *   The node id.
   * @param string $langcode
   *   The language code.
   * @param string $type
   *   The node content_type.
   *
   * @return string
   *   Return the description.
   */
  private function getHalfParagraph($nid, $langcode, $type) {

    $str = $this->queryParagraphs($nid, $langcode, $type, 'text_and_form');
    if (empty(trim($str))) {
      $str = $this->queryParagraphs($nid, 'en', $type, 'text_and_form');
    }
    return $str;
  }

  /**
   * Query the content of Paragraphs.
   *
   * @param int $nid
   *   The node id.
   * @param string $langcode
   *   The language code.
   * @param string $type
   *   The content type id.
   * @param string $para_type
   *   The paragraph type id.
   *
   * @return string
   *   The content of paragraphs.
   */
  private function queryParagraphs($nid, $langcode, $type, $para_type) {
    // paragraph__field_text_and_form_content,
    // paragraph__field_basic_content_content.
    $str = "";
    $table_para = "paragraph__field_" . $para_type . "_content";
    $table_para_field = "field_" . $para_type . "_content_value";

    if ($type == 'market_access_profile') {
      return "";
    }
    elseif ($type == 'offering') {
      // Possible values:  "of_content" or "offering_types".
      $type_field = $this->queryOfferingPara($nid, $langcode, $type, $para_type);
      $table_node = "node__field_" . $type_field;
      $table_node_field = "field_" . $type_field . "_target_id";
    }
    elseif ($type == 'knowledge') {
      $table_node = "node__field_know_content";
      $table_node_field = "field_know_content_target_id";
    }
    else {

      $table_node = "node__field_" . $type . "_content";
      $table_node_field = "field_" . $type . "_content_target_id";
    }

    $sql = "SELECT $table_para_field AS content
      FROM $table_para
      WHERE langcode='$langcode'
        AND bundle IN ('$para_type')
        AND entity_id IN
        (SELECT $table_node_field
          FROM $table_node
          WHERE entity_id=$nid)
      ORDER BY revision_id
    ";

    $query = $this->connection->query($sql);
    if (($row = $query->fetchAssoc()) !== FALSE) {
      $str = $row['content'];
    }

    return $str;
  }

  /**
   * Query the content of Paragraphs.
   *
   * @param int $nid
   *   The node id.
   * @param string $langcode
   *   The language code.
   * @param string $type
   *   The content type id.
   *
   * @return string
   *   The partial name of DB table for the content type offering.
   */
  private function queryOfferingPara($nid, $langcode, $type) {
    // Possible values:  "of_content" or "offering_types".
    $sql = "SELECT field_of_content_target_id AS id
      FROM node__field_of_content
      WHERE bundle='$type'
        AND entity_id=$nid
    ";
    $query = $this->connection->query($sql);
    if (($row = $query->fetchAssoc()) !== FALSE) {
      $id = $row['id'];
    }
    else {
      $id = FALSE;
    }

    return ($id > 0) ? "of_content" : "offering_types";
  }

  /**
   * Get the Header Link data.
   *
   * @param int $nid
   *   The node id.
   * @param string $langcode
   *   The language code.
   * @param string $field
   *   The partial name of the table.
   *
   * @return string
   *   Return the description.
   */
  private function getHeaderLink($nid, $langcode, $field) {
    $table_name = "node__field_shared_" . $field;
    $field_name1 = "field_shared_" . $field . "_uri";
    $field_name2 = "field_shared_" . $field . "_title";

    $sql = "
      SELECT $field_name1 AS uri, $field_name2 AS title
      FROM
        $table_name
      WHERE entity_id = $nid
        AND langcode = '$langcode'
      ";

    $query = $this->connection->query($sql);

    if (($row = $query->fetchAssoc()) !== FALSE) {
      return $row;
    }
    return FALSE;
  }

  /**
   * Get the News Issue Number data.
   *
   * @param int $nid
   *   The node id.
   * @param string $langcode
   *   The language code.
   * @param string $field
   *   The partial name of the table.
   *
   * @return string
   *   Return the description.
   */
  private function getIssueNumber($nid, $langcode, $field) {
    $table_name = "node__field_news_issue_number";
    $field_name1 = "field_news_issue_number_value";

    $sql = "
      SELECT $field_name1 AS $field
      FROM  $table_name
      WHERE entity_id = $nid
        AND langcode = '$langcode'
      ";

    $query = $this->connection->query($sql);

    if (($row = $query->fetchAssoc()) !== FALSE) {
      return $row[$field];
    }
    return FALSE;
  }

  /**
   * Get the Marketo Forms - field_shared_marketo_forms.
   *
   * @param int $nid
   *   The node id.
   * @param string $langcode
   *   The language code.
   * @param string $field
   *   The partial name of the table.
   *
   * @return string
   *   Return the description.
   */
  private function getMarketoForms($nid, $langcode, $field) {
    $table_name = "node__field_shared_$field";
    $field_name1 = "field_shared_marketo_forms_last_interest";
    $field_name2 = "field_shared_marketo_forms_mkto_campaign";

    $sql = "
      SELECT $field_name1 AS 'last_interest',
      $field_name2 AS 'marketo_campaign'
      FROM  $table_name
      WHERE entity_id = $nid
      ";

    $query = $this->connection->query($sql);

    if (($row = $query->fetchAssoc()) !== FALSE) {
      return $row;
    }
    return FALSE;
  }

  /**
   * Get News Date - field_news_date.
   *
   * @param int $nid
   *   The node id.
   * @param string $langcode
   *   The language code.
   * @param string $field
   *   The partial name of the table.
   *
   * @return string
   *   Return the description.
   */
  private function getNewsDate($nid, $langcode, $field) {
    $table_name = "node__field_news_date";
    $field_name1 = "field_news_date_value";

    $sql = "
      SELECT $field_name1 AS 'news_date'
      FROM  $table_name
      WHERE entity_id = $nid
      ";

    $query = $this->connection->query($sql);

    if (($row = $query->fetchAssoc()) !== FALSE) {
      return $row['news_date'];
    }
    return FALSE;
  }

  /**
   * Get Sidebar Curated Related field_shared_rr_related.
   *
   * @param int $nid
   *   The node id.
   * @param string $langcode
   *   The language code.
   * @param string $field
   *   The partial name of the table.
   *
   * @return string
   *   Return the description.
   */
  private function getRrRelated($nid, $langcode, $field) {
    $paragraphs = [];
    $table_name = "node__field_shared_rr_related";
    $field_name1 = "field_shared_rr_related_target_id";

    $sql = "
      SELECT $field_name1 AS 'pid'
      FROM  $table_name
      WHERE entity_id = $nid
      ";

    $query = $this->connection->query($sql);

    while (($row = $query->fetchAssoc()) !== FALSE) {
      $paragraphs[] = $this->getParagrahEntity($row['pid'], $langcode);

    }
    return $paragraphs;

  }

  /**
   * Get Use Right Rail Display? - field_shared_rr_checkbox.
   *
   * @param int $nid
   *   The node id.
   * @param string $langcode
   *   The language code.
   * @param string $field
   *   The partial name of the table.
   *
   * @return string
   *   Return the description.
   */
  private function getRrCheckbox($nid, $langcode, $field) {

    $table_name = "node__field_shared_rr_checkbox";
    $field_name1 = "field_shared_rr_checkbox_value";

    $sql = "
      SELECT $field_name1 AS 'checked'
      FROM  $table_name
      WHERE entity_id = $nid
      ";

    $query = $this->connection->query($sql);

    if (($row = $query->fetchAssoc()) !== FALSE) {
      return $row['checked'];
    }

    return FALSE;

  }

  /**
   * Get Closing Argument - field_campaign_page_bottom .
   *
   * @param int $nid
   *   The node id.
   * @param string $langcode
   *   The language code.
   * @param string $field
   *   The partial name of the table.
   *
   * @return string
   *   Return the description.
   */
  private function getCampaignPageButom($nid, $langcode, $field) {

    $table_name = "node__field_campaign_page_bottom";
    $field_name1 = "field_campaign_page_bottom_value";
    $field_name2 = "field_campaign_page_bottom_format";

    $sql = "
      SELECT $field_name1 AS 'page_bottom',
        $field_name2 AS 'format'
      FROM  $table_name
      WHERE entity_id = $nid AND langcode='$langcode'
      ";

    $query = $this->connection->query($sql);

    if (($row = $query->fetchAssoc()) !== FALSE) {
      return $row;
    }

    return FALSE;

  }

  /**
   * Get Review Comments - field_shared_review_comments.
   *
   * @param int $nid
   *   The node id.
   * @param string $langcode
   *   The language code.
   * @param string $field
   *   The partial name of the table.
   *
   * @return string
   *   Return the description.
   */
  private function getReviewComments($nid, $langcode, $field) {

    $table_name = "node__field_shared_review_comments";
    $field_name1 = "field_shared_review_comments_value";
    $field_name2 = "field_shared_review_comments_format";

    $sql = "
      SELECT $field_name1 AS 'review_comments',
        $field_name2 AS 'format'
      FROM  $table_name
      WHERE entity_id = $nid AND langcode='$langcode'
      ";

    $query = $this->connection->query($sql);

    if (($row = $query->fetchAssoc()) !== FALSE) {
      return $row;
    }

    return FALSE;

  }

  /**
   * Get Value Proposition - body.
   *
   * @param int $nid
   *   The node id.
   * @param string $langcode
   *   The language code.
   * @param string $field
   *   The partial name of the table.
   *
   * @return string
   *   Return the description.
   */
  private function getBody($nid, $langcode, $field) {

    $table_name = "node__body";
    $field_name1 = "body_value";
    $field_name2 = "body_format";

    $sql = "
      SELECT $field_name1 AS 'body',
        $field_name2 AS 'format'
      FROM  $table_name
      WHERE entity_id = $nid AND langcode='$langcode'
      ";

    $query = $this->connection->query($sql);

    if (($row = $query->fetchAssoc()) !== FALSE) {
      return $row;
    }

    return FALSE;

  }

  /**
   * Get Meta Tags - field_shared_metatags.
   *
   * @param int $nid
   *   The node id.
   * @param string $langcode
   *   The language code.
   * @param string $field
   *   The partial name of the table.
   *
   * @return string
   *   Return the description.
   */
  private function getMetaTags($nid, $langcode, $field) {

    $table_name = "node__field_shared_metatags";
    $field_name1 = "field_shared_metatags_value";

    $sql = "
      SELECT $field_name1 AS 'metatags'
      FROM  $table_name
      WHERE entity_id = $nid AND langcode='$langcode'
      ";

    $query = $this->connection->query($sql);

    if (($row = $query->fetchAssoc()) !== FALSE) {
      $metaTagsArr = unserialize($row['metatags']);
      return $metaTagsArr;
    }

    return FALSE;

  }

  /**
   * Get View mode - field_view_mode_hero.
   *
   * @param int $nid
   *   The node id.
   * @param string $langcode
   *   The language code.
   * @param string $field
   *   The partial name of the table.
   *
   * @return string
   *   Return the description.
   */
  private function getViewModeHero($nid, $langcode, $field) {

    $table_name = "node__field_view_mode_hero";
    $field_name1 = "field_view_mode_hero_value";

    $sql = "
      SELECT $field_name1 AS 'view_mode'
      FROM  $table_name
      WHERE entity_id = $nid
      ";

    $query = $this->connection->query($sql);

    if (($row = $query->fetchAssoc()) !== FALSE) {
      return $row['view_mode'];
    }
    return FALSE;
  }

  /**
   * Get Event data.
   *
   * Get date, link, map, locaion, private_event, speaker, teimzone.
   *
   * @param int $nid
   *   The node id.
   * @param string $langcode
   *   The language code.
   *
   * @return array
   *   Return array of Event data.
   */
  protected function getEventData($nid, $langcode) {
    $event = [];
    // E.g.: field_event_date, field_private_event_dropdown.
    $fields = [
      'cost', 'date', 'link', 'map', 'location', 'language',
      'speakers', 'timezone', 'private_event_dropdown',
    ];

    foreach ($fields as $field) {
      if ($field == 'private_event_dropdown') {
        $table_name = "node__field_$field";
      }
      else {
        $table_name = "node__field_event_$field";
        $field_name1 = "field_event_" . $field . "_value";
      }

      if (in_array($field, ['cost', 'timezone', 'language'])) {
        $field_name1 = "field_event_" . $field . "_value";
        $sql = "
          SELECT $field_name1 AS $field
          FROM  $table_name
          WHERE entity_id = $nid
        ";

        $query = $this->connection->query($sql);

        if (($row = $query->fetchAssoc()) !== FALSE) {
          $event[$field] = $row[$field];
        }
      }

      if ($field == 'location') {
        $field_name2 = 'field_event_location_format';
        $sql = "
          SELECT $field_name1 AS $field, $field_name2 AS format
          FROM  $table_name
          WHERE entity_id = $nid AND langcode = '$langcode'
        ";

        $query = $this->connection->query($sql);

        if (($row = $query->fetchAssoc()) !== FALSE) {
          $event[$field] = $row;
        }
      }

      if ($field == 'date') {
        $field_name2 = 'field_event_date_end_value';
        $sql = "
          SELECT $field_name1 AS start_date, $field_name2 AS end_date
          FROM  $table_name
          WHERE entity_id = $nid
        ";

        $query = $this->connection->query($sql);

        if (($row = $query->fetchAssoc()) !== FALSE) {
          $event[$field] = $row;
        }
      }

      if ($field == 'private_event_dropdown') {
        $field_name1 = 'field_private_event_dropdown_value';
        $sql = "
          SELECT $field_name1 AS private
          FROM  $table_name
          WHERE entity_id = $nid
        ";

        $query = $this->connection->query($sql);

        if (($row = $query->fetchAssoc()) !== FALSE) {
          $event['private'] = $row['private'];
        }
      }

      if (in_array($field, ['link', 'map'])) {
        $field_name1 = "field_event_" . $field . "_uri";
        $field_name2 = "field_event_" . $field . "_title";
        $sql = "
          SELECT $field_name1 AS uri, $field_name2 AS title
          FROM  $table_name
          WHERE entity_id = $nid
        ";

        $query = $this->connection->query($sql);

        if (($row = $query->fetchAssoc()) !== FALSE) {
          $event[$field] = $row;
        }
      }

      if ($field == 'speakers') {
        $field_name1 = 'field_event_speakers_target_id';
        $sql = "
          SELECT $field_name1 AS target_id
          FROM  $table_name
          WHERE entity_id = $nid
        ";

        $query = $this->connection->query($sql);

        while (($row = $query->fetchAssoc()) !== FALSE) {
          $event[$field][] = $row;
        }
      }

    }
    // END foreach.
    return $event;
  }

  /**
   * Get the default value of last_interest if it null.
   *
   * @param int $nid
   *   The node ID (entity_id).
   * @param string $field
   *   The column name in the DB table.
   * @param string $langcode
   *   The language code.
   *
   * @return string
   *   The value of last_interest
   */
  private function getCta($nid, $field, $langcode = 'en') {
    if (!$nid || !$field) {
      return "";
    }
    // Example, rr_marketo_cta, marketo_link.
    $field_name = "field_shared_" . $field . "_value";
    $table = "node__field_shared_$field";
    $sql = "
      SELECT $field_name AS cta
      FROM $table
      WHERE entity_id = $nid
        AND langcode = '$langcode'
      ";

    $query = $this->connection->query($sql);

    while (($row = $query->fetchAssoc()) !== FALSE) {
      return $row['cta'];
    }
  }

}

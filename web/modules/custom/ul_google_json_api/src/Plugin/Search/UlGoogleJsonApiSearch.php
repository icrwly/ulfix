<?php

namespace Drupal\ul_google_json_api\Plugin\Search;

use Drupal\Core\Utility\Token;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\Url;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\search\SearchPageRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

use Drupal\Core\Language\LanguageInterface;
use Drupal\google_json_api\Plugin\Search\GoogleJsonApiSearch;

/**
 * Handles searching for node entities using the Search module index.
 *
 * @SearchPlugin(
 *   id = "ul_google_json_api_search",
 *   title = @Translation("UL Google JSON API Search")
 * )
 */
class UlGoogleJsonApiSearch extends GoogleJsonApiSearch {

  /**
   * Search Page Repository Service.
   *
   * @var \Drupal\search\SearchPageRepositoryInterface
   */
  private $searchPageRepository;

  /**
   * RequestStack object for getting requests.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  private $tokenManager;

  /**
   * The sub domain name au-nz, canada, etc.
   *
   * @var string
   */
  protected $subdomain;

  /**
   * The langcode lr (document) & hl (interface).
   *
   * @var array
   */
  protected $googleLangcodes;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   Configuration array containing information about search page.
   * @param string $plugin_id
   *   Identifier of custom plugin.
   * @param array $plugin_definition
   *   Provides definition of search plugin.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Plugin definition.
   * @param \GuzzleHttp\Client $httpClient
   *   Client for managing http requests.
   * @param \Drupal\search\SearchPageRepositoryInterface $searchPageRepository
   *   Repository for the search page.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   For retrieving information about the request.
   * @param \Drupal\Core\Utility\Token $tokenManager
   *   For managing Tokens.
   * @param \Drupal\Core\Pager\PagerManagerInterface $pagerManager
   *   Pager Manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   Logger for logging.
   */
  public function __construct(
      array $configuration,
      $plugin_id,
      array $plugin_definition,
      ConfigFactoryInterface $configFactory,
      Client $httpClient,
      SearchPageRepositoryInterface $searchPageRepository,
      RequestStack $requestStack,
      Token $tokenManager,
      PagerManagerInterface $pagerManager,
      LoggerChannelFactoryInterface $loggerFactory
      ) {
    parent::__construct(
      $configuration, $plugin_id, $plugin_definition,
      $configFactory, $httpClient, $searchPageRepository, $requestStack,
      $tokenManager, $pagerManager, $loggerFactory
    );

    $this->searchPageRepository = $searchPageRepository;
    $this->requestStack = $requestStack;
    $this->tokenManager = $tokenManager;
    $this->googleLangcodes = $this->setGoogleLangcode();
    $this->subdomain = $this->setSubdomain();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('http_client'),
      $container->get('search.search_page_repository'),
      $container->get('request_stack'),
      $container->get('token'),
      $container->get('pager.manager'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {

    $page = 0;
    $sort = '';
    $items_per_page = $this->configuration['resultsperpage'];
    $response = [];
    $responseitems = [];

    // Retrieve sort value from query string if present.
    if ($this->requestStack->getCurrentRequest()->query->has('sort')) {
      $sort = $this->requestStack->getCurrentRequest()->query->get('sort');
    }

    // Retrieve page number value from query string if present.
    if ($this->requestStack->getCurrentRequest()->query->has('page')) {
      $page = $this->requestStack->getCurrentRequest()->query->get('page');
    }

    // Calculate the start index for the API request.
    // 100 is the max due to constraint of Google Programmable Search.
    $start = min(100, ($page * $items_per_page) + 1);

    // $items_per_page is a multiple of 10.
    for ($i = $items_per_page; $i > 0; $i = $i - 10) {

      $query = [
        'q' => $this->getKeywords(),
        'sort' => $sort,
        'cx' => $this->configuration['cx'],
        'key' => $this->configuration['apikey'],
        'start' => $start,
      ];

      $this->setCustomQuery($query);

      $url = Url::fromUri($this->configuration['apiendpointurl'], ['query' => $query])->toString();

      // Get the JSON response from the Google API.
      try {
        $nextresponse = json_decode($this->getResponse($url), TRUE);

        // If nextresponse has items, make it the official response array and
        // append additonal items to items from previous responses.
        if (!empty($nextresponse['items']) && count($nextresponse['items'])) {
          $response = $nextresponse;
          $responseitems = array_merge($responseitems, $response['items']);
          $start = (($start + 10) > $response['queries']['request'][0]['totalResults']) ? -1 : $start + 10;
        }
        else {
          // No new items found.
          $start = -1;
        }

      }
      catch (\Exception $e) {
        $this->logger->error('Exception in UlGoogleJsonApiSearch::execute(): @message', ['@message' => $e->getMessage()]);
        return ['error' => TRUE];
      }

      // Break Google Programmable Search JSON API request loop if no new items
      // or $start is beyond Google Programmable Search 100 item response limit.
      if ($start == -1 || $start > 100) {
        break;
      }

    }

    // Update response array with all results.
    $response['items'] = $responseitems;

    // Retrieve total result count provided by Google Programmable Search.
    // This is an estimate from Google of all results, but Google
    // Programmable Search will only return up to 100 results.
    $totalresults = (!empty($response['queries']['request'][0]['totalResults'])) ?
      $response['queries']['request'][0]['totalResults'] : 0;

    // Create a pager customized for Google's limitation of 100.
    if (!empty($response['queries']['nextPage']) || !empty($response['queries']['previousPage'])) {
      $this->createSearchPager($totalresults, $items_per_page);
    }

    return $response;

  }

  /**
   * {@inheritdoc}
   */
  public function buildResults() {
    $results = $this->execute();

    $output = [];

    if (!empty($results['items'])) {

      // Determine the sort used in the request.
      $sortvalues = [
        'date:d' => 'Newest First',
        'date:a' => 'Oldest First',
        '' => 'Relevance',
      ];
      $sortvalue = (!empty($results['queries']['request'][0]['sort'])) ?
        $results['queries']['request'][0]['sort'] : '';
      $sortvalue = (!empty($sortvalues[$sortvalue])) ? $sortvalues[$sortvalue] : 'Unknown';

      // Retrieve the total results estimate from Google.
      $googletotalresults = (!empty($results['queries']['request'][0]['totalResults'])) ?
        $results['queries']['request'][0]['totalResults'] : 0;

      // Retrieve the starting result count and ending count for the page.
      $page = 0;
      if ($this->requestStack->getCurrentRequest()->query->has('page')) {
        $page = $this->requestStack->getCurrentRequest()->query->get('page');
      }
      $startcount = ($page * $this->configuration['resultsperpage']) + 1;
      $endcount = $startcount + count($results['items']) - 1;

      // Populate the data array used to populate Google JSON API custom tokens.
      $tokens['google_json_api'] = [
        'google_total_results' => $googletotalresults,
        'google_json_api_search_keywords' => $this->getKeywords(),
        'google_json_api_result_start' => $startcount,
        'google_json_api_result_end' => $endcount,
        'google_json_api_sort_value' => $sortvalue,
        'google_json_api_search_page' => $this->configuration['label'],
      ];

      // Display the results count message - configurable on the search page.
      $resultsmessage = $this->tokenManager->replace($this->configuration['results_message'], $tokens);

      if (count($results['items']) == 1 && !empty($this->configuration['results_message_singular'])) {

        $resultsmessage = $this->tokenManager->replace($this->configuration['results_message_singular'], $tokens);

      }

      $output[] = [
        '#theme' => 'google_json_api_results_message',
        '#message' => $resultsmessage,
        '#term' => $this->getKeywords(),
        '#plugin_id' => $this->getPluginId(),
        '#attached' => [
          'library' => [
            'google_json_api/googlejsonapiresults',
          ],
        ],
      ];

      // Display Google results limitation message from search page config.
      if ($googletotalresults > 100 && !empty($this->configuration['results_limitation_message'])) {

        $limitationmessage = $this->tokenManager->replace($this->configuration['results_limitation_message'], $tokens);

        $output[] = [
          '#theme' => 'google_json_api_results_limitation_message',
          '#message' => $limitationmessage,
          '#term' => $this->getKeywords(),
          '#plugin_id' => $this->getPluginId(),
          '#attached' => [
            'library' => [
              'google_json_api/googlejsonapiresults',
            ],
          ],
        ];

      }

      // Display Last Page Message.
      if (!empty($results['queries']['previousPage']) && empty($results['queries']['nextPage'])) {

        $lastpagemessage = $this->tokenManager->replace($this->configuration['results_last_page_message'], $tokens);

        $output[] = [
          '#theme' => 'google_json_api_results_last_page_message',
          '#message' => $lastpagemessage,
          '#term' => $this->getKeywords(),
          '#plugin_id' => $this->getPluginId(),
          '#attached' => [
            'library' => [
              'google_json_api/googlejsonapiresults',
            ],
          ],
        ];

      }

      // Display any promoted results for the search.
      if (!empty($results['promotions'])) {
        foreach ($results['promotions'] as $promo) {
          $output[] = [
            '#theme' => 'google_json_api_promoted_result',
            '#term' => $this->getKeywords(),
            '#promotion' => $promo,
            '#plugin_id' => $this->getPluginId(),
            '#attached' => [
              'library' => [
                'google_json_api/googlejsonapiresults',
              ],
            ],
          ];
        }
      }

      // Display spelling corrections/suggestions if they exist.
      if (!empty($results['spelling'])) {
        $url = Url::fromRoute('<current>', [
          'keys' => $results['spelling']['correctedQuery'],
        ]);

        $output[] = [
          '#theme' => 'google_json_api_spelling_correction',
          '#spelling' => $results['spelling'],
          '#url' => $url,
          '#term' => $this->getKeywords(),
          '#plugin_id' => $this->getPluginId(),
          '#attached' => [
            'library' => [
              'google_json_api/googlejsonapiresults',
            ],
          ],
        ];
      }

      // Display the normal results.
      $index = 0;
      foreach ($results['items'] as $result) {
        $weight = $this->setCustomWeight($result, $index);
        // Remove the domain if the feature is enabled.
        if (isset($this->configuration['removedomain']) && $this->configuration['removedomain']) {
          $url_parts = parse_url($result['link']);
          $new_link = str_replace($url_parts['scheme'] . '://' . $url_parts['host'], '', $result['link']);
          $result['link'] = $new_link;
        }

        $output['results'][] = [
          '#theme' => 'google_json_api_result',
          '#result' => $result,
          '#term' => $this->getKeywords(),
          '#plugin_id' => $this->getPluginId(),
          '#weight' => $weight,
          '#attached' => [
            'library' => [
              'google_json_api/googlejsonapiresults',
            ],
          ],
        ];

        $index++;
      }

    }

    return $output;
  }

  /**
   * Gets a response from a Google API URL.
   *
   * @param string $url
   *   The Google URL to query.
   *
   * @return string
   *   The response from Google.
   *
   * @throws \http\Exception
   */
  private function getResponse(string $url) {
    $options = [
      'connect_timeout' => 30,
      'headers' => [
        'Content-Type' => 'text/json',
      ],
    ];

    try {
      $request = $this->httpClient->request('GET', $url, $options);
    }
    catch (GuzzleException $e) {
      throw new \Exception('Error when making request to the JSON API: ' . $e->getMessage());
    }

    if ($request->getStatusCode() != 200) {
      throw new \Exception('Got status code other than 200: ' . $request->getStatusCode());
    }

    return $request->getBody();
  }

  /**
   * Narrow down the search query in the subdomain.
   */
  public function getHqKeywords() {
    $keyworkds = "";
    $subsites = $this->getSubsites();
    foreach ($subsites as $site) {
      if (str_starts_with($this->subdomain, $site)) {
        if ($site == 'au-nz') {
          $keyworkds = "australia zealand";
        }
        else {
          $keyworkds = $site;
        }

        break;
      }
    }

    return $keyworkds;
  }

  /**
   * Get array of subsites for special results.
   */
  public function getSubsites() {
    // The possible values of subsites:
    // ['au-nz', 'latam', 'emergeo', 'news-canada',
    // 'canada','shimadzu', 'emergobyul', 'enterprise',].
    return ['au-nz', 'canada', 'news-canada'];
  }

  /**
   * Build the custom query array.
   *
   * @param array $query
   *   The array of $query, entity reference.
   */
  public function setCustomQuery(array &$query) {
    $langcode = \Drupal::languageManager()->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();

    $query['hl'] = $this->googleLangcodes['hl'][$langcode];
    if (stristr($this->subdomain, 'latam')) {
      $query['lr'] = $this->googleLangcodes['lr'][$langcode];
    }

    if (!empty($this->getHqKeywords())) {
      $query['hq'] = $this->getHqKeywords();
    }
  }

  /**
   * Set the google query parameter "lr".
   *
   * Restricts the search to documents written in a particular language.
   * Map the Drupal langcode to google "lr" code.
   */
  public function setGoogleLangcode() {
    $langcodes = \Drupal::languageManager()->getLanguages();
    $langcodesList = array_keys($langcodes);
    $googleLangcodes = [];

    $lang_lr = 'en';
    $lang_hl = 'en';
    foreach ($langcodesList as $langcode) {
      switch ($langcode) {
        case 'zh-hans':
          $lang_lr = 'lang_zh-CN';
          $lang_hl = 'zh-CN';
          break;

        case 'zh-hant':
          $lang_lr = 'lang_zh-TW';
          $lang_hl = 'zh-TW';
          break;

        case 'fr-ca':
          $lang_lr = 'lang_fr';
          $lang_hl = 'fr';
          break;

        case 'pt-br':
          $lang_lr = 'lang_pt';
          $lang_hl = 'pt';
          break;

        default:
          $lang_lr = "lang_$langcode";
          $lang_hl = $langcode;
          break;
      }
      $googleLangcodes['lr'][$langcode] = $lang_lr;
      $googleLangcodes['hl'][$langcode] = $lang_hl;
    }

    return $googleLangcodes;
  }

  /**
   * Set the custom weight to re-order the render array.
   *
   * @todo More customized rules to re-order the search result.
   *
   * @param array $result
   *   The render array of search result, entity reference.
   * @param int $index
   *   The index of array item.
   */
  public function setCustomWeight(array &$result, $index) {
    $weight = $index;
    $sub = $this->subdomain;

    if (str_starts_with($sub, 'news-canada')) {
      if (stristr($result['link'], $sub)) {
        $weight = $weight - 100;
      }
      elseif (stristr($result['link'], '//canada.')) {
        $weight = $weight - 50;
      }
    }
    elseif (str_starts_with($sub, 'au-nz')) {
      if (stristr($result['link'], "$sub.ul")) {
        $weight = $weight - 50;
      }

    }
    return $weight;
  }

  /**
   * Set the subdomain name.
   */
  public function setSubdomain() {
    $subsites = $this->getSubsites();
    $host = \Drupal::request()->getSchemeAndHttpHost();
    if (!isset($host)) {
      $host = \Drupal::request()->getHost();
    }
    $host = parse_url($host, PHP_URL_HOST);
    $sub = explode('.', $host)[0];

    foreach ($subsites as $site) {
      // E.g. $sub="au-nz-vader"; $site="au-nz".
      if (str_starts_with($sub, $site)) {
        return $site;
      }
    }
    return $sub;
  }

}

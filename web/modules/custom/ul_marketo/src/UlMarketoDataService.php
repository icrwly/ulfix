<?php

namespace Drupal\ul_marketo;

use Drupal\taxonomy\Entity\Term;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Database\Connection;
use Drupal\Core\Config\ConfigFactory;
use Drupal\node\Entity\Node;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Drupal\Component\Datetime\DateTimePlus;
use Drupal\file\Entity\File;

/**
 * Provides tools for Sub COU and Last Interest values.
 */
class UlMarketoDataService {
  use StringTranslationTrait;

  /**
   * An associative array mapping last_interest to its parent sub_cou.
   *
   * @var array
   */
  protected $last_interest_to_sub_cou;

  /**
   * Maps sub_cou id to display value and related last_interest values.
   *
   * @var array
   */
  protected $last_interest_by_sub_cou;

  /**
   * ConfigFactory service.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a new UlMarketoService object.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   Config factory object.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database object.
   */
  public function __construct(ConfigFactory $config, Connection $connection) {
    $this->config = $config;
    $this->connection = $connection;

    // Load the configurations related to Last Interest to the appropriate data
    // arrays.
    $this->last_interest_by_sub_cou = $this->config->get('ul_marketo.last_interest_by_sub_cou')->getRawData();
    $this->last_interest_to_sub_cou = $this->config->get('ul_marketo.last_interest_to_sub_cou')->getRawData();

  }

  /**
   * Returns the marketo routing map array.
   *
   * @return array
   *   Marketo Routing Map array.
   */
  public function getMarketoRoutingMap() {
    return $this->marketo_routing_map;
  }

  /**
   * Returns the last interest to sub cou array.
   *
   * @return array
   *   Last Interest to Sub COU array.
   */
  public function getLastInterestToSubCou() {
    return $this->last_interest_to_sub_cou;
  }

  /**
   * Returns the last interest by sub cou array.
   *
   * @return array
   *   Last Interest by Sub COU array
   */
  public function getLastInterestBySubCou() {
    return $this->last_interest_by_sub_cou;
  }

  /**
   * Return the sub_cou value for the supplied last_interest value.
   *
   * @param string $last_interest
   *   The last_interest string.
   *
   * @return string
   *   The sub_cou that the last_interest relates to.
   */
  public function getSubCouForLastInterest($last_interest) {
    return $this->last_interest_to_sub_cou[$last_interest];
  }

  /**
   * Return the sub_cou value for the supplied last_interest value.
   *
   * @param int $index
   *   The $index of the last_interst array to get values for. If null a random
   *   pair will be selected.
   *
   * @return array
   *   Associative array containing the selected sub_cou and last_interest pair.
   */
  public function getLastInterest($index = NULL) {
    $keys = array_keys($this->last_interest_to_sub_cou);
    $max = count($keys) - 1;

    if (!isset($index)) {
      $index = rand(0, $max);
    }
    else {
      // If the index > number of last interests, use $index % max to get a
      // valid index.
      if ($index > $max) {
        $index = $index % $max;
      }
    }

    return [
      'sub_cou' => $this->last_interest_to_sub_cou[$keys[$index]],
      'last_interest' => $keys[$index],
    ];
  }

  /**
   * Returns a random last_interest value for a supplied sub_cou value.
   *
   * @var string
   */
  public function getRandomLastInterestForSubCou($sub_cou) {
    if (isset($this->last_interest_by_sub_cou[$sub_cou])) {
      $values = $this->last_interest_by_sub_cou[$sub_cou]['last_interests'];
      $max = count($values) - 1;
      $index = rand(0, $max);

      return $this->last_interest_by_sub_cou[$sub_cou]['last_interests'][$index];
    }
  }

  /**
   * Checks if a provided non-empty sub_cou value is valid.
   *
   * @var string
   */
  public function isValidSubCou($sub_cou) {
    if (!empty($sub_cou)) {
      $keys = array_keys($this->last_interest_by_sub_cou);
      return in_array($sub_cou, $keys);
    }

    return TRUE;
  }

  /**
   * Checks if the supplied last_interest and optional  sub_cou is valid.
   *
   * @var string
   *  Last Interest value.
   *
   * @var string
   *  Optional sub_cou value.
   */
  public function isValidLastInterest($last_interest, $sub_cou = NULL) {
    if (!empty($last_interest)) {
      // First make sure the last_interest is valid.
      if (!isset($this->last_interest_to_sub_cou[$last_interest])) {
        return FALSE;
      }
      // Next make sure sub_cou is valid for the selected last_interest if it's
      // defined.
      elseif (!empty($sub_cou)) {
        return $this->last_interest_to_sub_cou[$last_interest] == $sub_cou;
      }
    }

    return TRUE;
  }

  /**
   * Returns an array of Content Sub Type options for the selected content type.
   *
   * @param string $type
   *   Optional content type.
   *
   * @return array
   *   Array of sub type options for the supplied type.
   */
  public function getContentSubTypeOptions($type = NULL) {

    $sub_types = [
      'event' => 'event_types',
      'help' => 'help_types',
      'knowledge' => 'knowledge_types',
      'news' => 'news_types',
      'offering' => 'service_categories',
      'resource' => 'resource_types',
      'tool' => 'tool_types',
    ];

    if (!empty($type)) {
      $sub_types = $sub_types[$type];
    }
    else {
      $sub_types = array_values($sub_types);
    }

    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid', $sub_types, 'IN');
    $query->accessCheck(FALSE);
    $tids = $query->execute();
    $terms = Term::loadMultiple($tids);
    $current_vid = NULL;
    $options = [];
    $vocabulary_label = NULL;

    foreach ($terms as $term) {
      $vid = $term->bundle();
      if ($vid !== $current_vid) {
        $vocabulary_label = \Drupal::entityTypeManager()
          ->getStorage('taxonomy_vocabulary')
          ->load($vid)
          ->label();

        // We want 'Service Categories' to be called 'Offering Types'.
        if ($vocabulary_label == 'Service Categories') {
          $vocabulary_label = 'Offering Types';
        }

        $current_vid = $vid;

        $options[$vocabulary_label] = [];
      }

      $options[$vocabulary_label][$term->id()] = $term->getName();

    }

    // Sort options in each group.
    foreach ($options as $key => $list) {
      asort($options[$key]);
    }

    return $options;
  }

  /**
   * Replaces special characters with ascii equivalents.
   *
   * For example, replace em dash with '-', curly quotes with straight quotes,
   * etc.
   *
   * @var string
   *   Source string passes by reference
   */
  public function replaceSpecialCharacters(&$string) {

    // Array of special microsoft characters with curly quotes and long dashes.
    $search = [
      "\xe2\x80\x98",
      "\xe2\x80\x99",
      "\xe2\x80\x9c",
      "\xe2\x80\x9d",
      "\xe2\x80\x93",
      "\xe2\x80\x94",
      "\xe2\x80\xa6",
      chr(145),
      chr(146),
      chr(147),
      chr(148),
      chr(150),
      chr(151),
    ];

    // Ascii replacement characters.
    $replace = [
      "'",
      "'",
      '"',
      '"',
      '-',
      '-',
      '...',
      "'",
      "'",
      '"',
      '"',
      '-',
      '-',
    ];

    $string = str_replace($search, $replace, $string);
  }

  /**
   * Maps the routing fields to correct sub_cou and last_interest values.
   *
   * Mapping only performed if a mapping exists and the sub cou and last
   * interest values not already defined.
   *
   * @var object
   *  The node to be migrated, passed by reference.
   *
   * @var boolean
   *  Run in test mode, node data will be migrated but a message describing the
   *  migration result will be returned.
   *
   * @return array
   *   Associative array with the results, ['success' => <a mapping was found>,
   *   'message' => 'description of the mapping result>].
   */
  public function mapRoutingFields(&$node, $test_mode = FALSE) {

    $nid = $node->id();

    $result = [
      'mapped' => [],
      'message' => '',
    ];

    $routing_fields = $node->get('field_shared_marketo_forms')->getValue();
    if (!empty($routing_fields)) {
      $result['mapped'] = $routing_fields[0];
      // $result['mapped']['sub_cou'] = NULL;
      // $result['mapped']['last_interest'] = NULL;
      $result['mapped']['has_routing_info'] = ($result['mapped']['instance'] == 'MSD' && !empty($result['mapped']['oracle_industry']))
      || ($result['mapped']['instance'] == 'SFDC' && !empty($result['mapped']['area_interest']));

      // Check if sub_cou and last_interest have already been set via migration
      // or user input.
      $result['mapped']['has_last_interest'] = !empty($result['mapped']['sub_cou']) && !empty($result['mapped']['last_interest']);

      // Don't migrate nodes that already have last interesrt values set.
      if ($result['mapped']['has_last_interest']) {
        $result['message'] = $this->t("Node @nid: Already has sub_cou '@sub_cou' and last_interest '@last_interest'. Migration skipped.",
        [
          '@nid' => $node->id(),
          '@sub_cou' => $result['mapped']['sub_cou'],
          '@last_interest' => $result['mapped']['last_interest'],
        ]);
      }
      else {
        $instance = $result['mapped']['instance'];

        switch ($instance) {
          case 'MSD':
            $oracle_industry = trim($result['mapped']['oracle_industry']);
            $this->replaceSpecialCharacters($oracle_industry);

            $oracle_subindustry = trim($result['mapped']['oracle_subindustry']);
            $this->replaceSpecialCharacters($oracle_subindustry);

            // The $effective_oracle_subindustry is was gets used in the
            // mapping. Initially it is the same as the $oracle_subindustry.
            $effective_oracle_subindustry = $oracle_subindustry;

            if (empty($oracle_industry)) {
              $result['message'] = $this->t("Node @nid - 'MSD' instance requires Oracle Industry. No mapping is available.", ['@nid' => $nid]);
            }
            // If oracle_subindustry is missing, try to map using 'BLANK' value.
            elseif (empty($oracle_subindustry)) {
              $effective_oracle_subindustry = 'BLANK';
            }

            if (
              isset($this->marketo_routing_map[$instance])
              && isset($this->marketo_routing_map[$instance][$oracle_industry])
              && isset($this->marketo_routing_map[$instance][$oracle_industry][$effective_oracle_subindustry])
            ) {
              $mapped = $this->marketo_routing_map[$instance][$oracle_industry][$effective_oracle_subindustry];
              $result['mapped']['sub_cou'] = $mapped['sub_cou_id'];
              $result['mapped']['last_interest'] = $mapped['last_interest'];
              $result['message'] = $this->t("Node @nid - Instance:'@instance', Oracle Industry:'@oracle_industry', Oracle Subindustry:'@oracle_subindustry' mapped to Sub COU:'@sub_cou', Last Interest:'@last_interest'",
              [
                '@nid' => $nid,
                '@instance' => $instance,
                '@oracle_industry' => $oracle_industry,
                '@oracle_subindustry' => $oracle_subindustry,
                '@sub_cou' => $mapped['sub_cou_id'],
                '@last_interest' => $mapped['last_interest'],
              ]);

            }
            else {
              $result['message'] = $this->t("Node @nid - No mapping found for Instance:'@instance', Oracle Industry:'@oracle_industry', Oracle Subindustry:'@oracle_subindustry'",
              [
                '@nid' => $nid,
                '@instance' => $instance,
                '@oracle_industry' => $oracle_industry,
                '@oracle_subindustry' => $oracle_subindustry,
                '@sub_cou' => $mapped['sub_cou'],
                '@last_interest' => $mapped['last_interest'],
              ]);
            }
            break;

          case 'SFDC':
            $area_interest = trim($result['mapped']['area_interest']);
            $this->replaceSpecialCharacters($area_interest);

            if (
              isset($this->marketo_routing_map[$instance])
              && isset($this->marketo_routing_map[$instance][$area_interest])
            ) {
              $mapped = $this->marketo_routing_map[$instance][$area_interest];
              $result['mapped']['sub_cou'] = $mapped['sub_cou_id'];
              $result['mapped']['last_interest'] = $mapped['last_interest'];
              $result['message'] = $this->t("Node @nid - Instance:'@instance', Area Interest:'@area_interest' mapped to Sub COU:'@sub_cou', Last Interest:'@last_interest'",
              [
                '@nid' => $nid,
                '@instance' => $instance,
                '@area_interest' => $area_interest,
                '@sub_cou' => $mapped['sub_cou_id'],
                '@last_interest' => $mapped['last_interest'],
              ]);

            }
            else {
              $result['message'] = $this->t("Node @nid - No mapping found for Instance:'@instance', Area Interest:'@area_interest'",
              [
                '@nid' => $nid,
                '@instance' => $instance,
                '@area_interest' => $area_interest,
                '@sub_cou' => $mapped['sub_cou'],
                '@last_interest' => $mapped['last_interest'],
              ]);

            }

            break;

          case 'INDUSTRY':
            $result['message'] = $this->t("Node @nid: INDUSTRY instances are not migrated.", ['@nid' => $node->id()]);
            break;

        }
      }
    }
    else {
      $result['message'] = $this->t("Node @nid: No routing fields to migrate.", ['@nid' => $node->id()]);
    }

    // If there are sub_cou and last_interest values to map, and we're not in
    // test mode, update the node with the mapped values.
    if (!$test_mode && !empty($result['mapped']['sub_cou']) && !empty($result['mapped']['last_interest'])) {
      $node->set('field_shared_marketo_forms', $result['mapped']);
      $node->save();
    }

    return $result;

  }

  /**
   * Migrates the routing fields for all requested nodes.
   *
   * @var array $ids
   *   Array of node ids to migrate. If empty, all nodes will be migrated.
   *
   * @var boolean $test
   *   Boolean value to indicate if migration should be rn in test mode - no
   *   nodes will be updated.
   *
   * @var string $output
   *   File path to write the migration report to a csv file.
   */
  public function migrateRoutingFields($ids = NULL, $test = FALSE, $output = NULL) {
    $start_time = new DateTimePlus();
    \Drupal::logger('UL Marketo')->notice('Marketo migration started at ' . $start_time->format('Y-m-d h:i:a'));

    $query = $this->connection->select('node_field_data', 'n');
    $query->fields('n', ['nid']);

    $query->addJoin('right', 'node__field_shared_marketo_forms', 'm', 'n.nid=m.entity_id');
    $query->orderBy('m.field_shared_marketo_forms_instance');
    $query->orderBy('m.field_shared_marketo_forms_oracle_industry');
    $query->orderBy('m.field_shared_marketo_forms_oracle_subindustry');
    $query->orderBy('m.field_shared_marketo_forms_area_interest');

    if (!empty($ids)) {
      $query->condition('n.nid', $ids, 'IN');
    }

    $result = $query->execute();

    // Associative array of mapped nodes divided into threee groups.
    // 'all' - All nodes with the mapping result.
    //
    // 'mapped' - Nodes that have mappings.
    //
    // 'not_mapped' - Nodes that have routing values but no mapping was found.
    //
    // 'has_last_interest' - Nodes that are not changed because they already
    // have last interest values from migration or user input.
    $report = [
      'all' => [],
      'missing_routing_info' => [],
      'mapped' => [],
      'not_mapped' => [],
      'has_last_interest' => [],
    ];

    foreach ($result as $row) {
      $nid = $row->nid;

      $node = Node::load($nid);

      $mapping_result = $this->mapRoutingFields($node, $test);
      \Drupal::logger('UL Marketo')->notice($mapping_result['message']);

      // Add to report.
      $row_data = $mapping_result['mapped'];
      $row_data['nid'] = $nid;
      $row_data['title'] = $node->getTitle();
      $row_data['type'] = $node->bundle();
      $row_data['message'] = $mapping_result['message'];

      // All Nodes with Marketo Info.
      $report['all'][] = $row_data;

      // Nodes that are missing routing info and cannot be mapped.
      if (!$row_data['has_routing_info']) {
        $report['missing_routing_info'][] = $row_data;
      }

      // All nodes that have mapped routing fields.
      if (!empty($row_data['last_interest']) && !$row_data['has_last_interest']) {
        $report['mapped'][] = $row_data;
      }

      // Nodes that have routing fields but no mapping could be found.
      if (empty($row_data['last_interest']) && $row_data['has_routing_info']) {
        $report['not_mapped'][] = $row_data;
      }

      // Nodes that already have last_interest values from previous migration
      // or user input.
      if ($row_data['has_last_interest']) {
        $report['has_last_interest'][] = $row_data;
      }

    }

    $end_time = new DateTimePlus();

    $elapsed_time = $start_time->diff($end_time);

    \Drupal::logger('UL Marketo')->notice('Marketo migration completed in ' . $elapsed_time->format('%i minutes %s seconds'));

    $report['stats'] = [
      'Nodes targeted' => count($report['all']),
      'Nodes Missing Routing Info' => count($report['missing_routing_info']),
      'Nodes Mapped' => count($report['mapped']),
      'Nodes Not_Mapped' => count($report['not_mapped']),
      'Nodes with Existing Last Interest Values' => count($report['has_last_interest']),
      ' ' => '',
      'Start Time' => $start_time->format('Y-m-d h:i:a'),
      'End Time' => $end_time->format('Y-m-d h:i:a'),
      'Elapsed Time' => $elapsed_time->format('%i minutes %s seconds'),
    ];

    if (!empty($output)) {
      $this->saveMigrationReport($report, $output);
    }

  }

  /**
   * Saves the migration report to a csv file.
   *
   * @var array $report
   *   Report array to be written to the csv file.
   *
   * @var string $output
   *   File path to write the migration report to a csv file.
   */
  private function saveMigrationReport(&$report, $output) {

    $output_path = \Drupal::service('file_system')->realpath("private://" . $output);

    \Drupal::logger('UL Marketo')->notice('output path = ' . $output_path);

    $spreadsheet = new Spreadsheet();

    $cols = [
      'nid',
      'title',
      'type',
      'instance',
      'has_routing_info',
      'sub_cou',
      'last_interest',
      'message',
    ];

    $worksheets = [
      'all',
      'missing_routing_info',
      'mapped',
      'not_mapped',
      'has_last_interest',
    ];

    for ($w = 0; $w < count($worksheets); $w++) {

      if ($w > 0) {
        $spreadsheet->createSheet();
      }

      $sheet = $spreadsheet->getSheet($w);
      $sheet->setTitle($worksheets[$w]);

      $r = 1;
      for ($i = 0; $i < count($cols); $i++) {
        $j = $i + 1;
        $sheet->setCellValueByColumnAndRow($j, $r, $cols[$i]);
      }

      foreach ($report[$worksheets[$w]] as $row) {
        $r++;
        for ($i = 0; $i < count($cols); $i++) {
          $j = $i + 1;
          $col = $cols[$i];
          $value = $row[$col];
          $sheet->setCellValueByColumnAndRow($j, $r, $value);
        }
      }
    }

    // Add stats.
    $spreadsheet->createSheet();
    $sheet = $spreadsheet->getSheet(count($worksheets));
    $sheet->setTitle('Stats');

    $r = 1;
    foreach ($report['stats'] as $key => $value) {
      $sheet->setCellValueByColumnAndRow(1, $r, $key);
      $sheet->setCellValueByColumnAndRow(2, $r, $value);
      $r++;
    }

    $writer = new Xlsx($spreadsheet);

    $writer->save($output_path);

    $file = File::create([
      'uid' => 1,
      'filename' => $output,
      'uri' => 'private://' . $output,
      'status' => 1,
    ]);

    $file->save();

  }

  /**
   * Array mapping marketo routing fields to sub_cou and last_interest values.
   *
   * @var array
   */
  protected $marketo_routing_map = [
    'MSD' => [
      'Renewables' => [
        'HOMER Front Utility-Scale Hybrid Power Optimization' => [
          'sub_cou_id' => 'Sustainable Energy & Infrastructure',
          'sub_cou_display' => 'Sustainable Energy and Infrastructure',
          'last_interest' => 'HOMER Front Utility-Scale Hybrid Power Optimization',
        ],
        'HOMER Grid Distributed Generation Optimization' => [
          'sub_cou_id' => 'Sustainable Energy & Infrastructure',
          'sub_cou_display' => 'Sustainable Energy and Infrastructure',
          'last_interest' => 'HOMER Grid Distributed Generation Optimization',
        ],
        'HOMER Pro Standalone Microgrid Optimization' => [
          'sub_cou_id' => 'Sustainable Energy & Infrastructure',
          'sub_cou_display' => 'Sustainable Energy and Infrastructure',
          'last_interest' => 'HOMER Pro Standalone Microgrid Optimization',
        ],
        'Other' => [
          'sub_cou_id' => 'Sustainable Energy & Infrastructure',
          'sub_cou_display' => 'Sustainable Energy and Infrastructure',
          'last_interest' => 'N/A - Renewables',
        ],
        'BLANK' => [
          'sub_cou_id' => 'Sustainable Energy & Infrastructure',
          'sub_cou_display' => 'Sustainable Energy and Infrastructure',
          'last_interest' => 'N/A - Renewables',
        ],
        'N/A - Renewables' => [
          'sub_cou_id' => 'Sustainable Energy & Infrastructure',
          'sub_cou_display' => 'Sustainable Energy and Infrastructure',
          'last_interest' => 'N/A - Renewables',
        ],
        'Offshore Wind Advisory' => [
          'sub_cou_id' => 'Sustainable Energy & Infrastructure',
          'sub_cou_display' => 'Sustainable Energy and Infrastructure',
          'last_interest' => 'Offshore Wind Advisory',
        ],
        'Openwind Plant Design' => [
          'sub_cou_id' => 'Sustainable Energy & Infrastructure',
          'sub_cou_display' => 'Sustainable Energy and Infrastructure',
          'last_interest' => 'Openwind Plant Design',
        ],
        'Renewables Assets Monitoring Platform (RAMP)' => [
          'sub_cou_id' => 'Sustainable Energy & Infrastructure',
          'sub_cou_display' => 'Sustainable Energy and Infrastructure',
          'last_interest' => 'Renewables Assets Monitoring Platform (RAMP)',
        ],
        'Renewables Resource Maps or Data' => [
          'sub_cou_id' => 'Sustainable Energy & Infrastructure',
          'sub_cou_display' => 'Sustainable Energy and Infrastructure',
          'last_interest' => 'Renewables Resource Maps or Data',
        ],
        'Renewables-Plus-Storage Advisory' => [
          'sub_cou_id' => 'Sustainable Energy & Infrastructure',
          'sub_cou_display' => 'Sustainable Energy and Infrastructure',
          'last_interest' => 'Renewables-Plus-Storage Advisory',
        ],
        'Solar Advisory' => [
          'sub_cou_id' => 'Sustainable Energy & Infrastructure',
          'sub_cou_display' => 'Sustainable Energy and Infrastructure',
          'last_interest' => 'Solar Advisory',
        ],
        'Wind Advisory' => [
          'sub_cou_id' => 'Sustainable Energy & Infrastructure',
          'sub_cou_display' => 'Sustainable Energy and Infrastructure',
          'last_interest' => 'Wind Advisory',
        ],
        'Wind or Solar Forecasting' => [
          'sub_cou_id' => 'Sustainable Energy & Infrastructure',
          'sub_cou_display' => 'Sustainable Energy and Infrastructure',
          'last_interest' => 'Wind or Solar Forecasting',
        ],
        'Wind Turbine Digital Inspection Platform' => [
          'sub_cou_id' => 'Sustainable Energy & Infrastructure',
          'sub_cou_display' => 'Sustainable Energy and Infrastructure',
          'last_interest' => 'Wind Turbine Digital Inspection Platform',
        ],
        'Windnavigator Wind Speed Data Platform' => [
          'sub_cou_id' => 'Sustainable Energy & Infrastructure',
          'sub_cou_display' => 'Sustainable Energy and Infrastructure',
          'last_interest' => 'Windnavigator Wind Speed Data Platform',
        ],
        'Windographer Data Analysis' => [
          'sub_cou_id' => 'Sustainable Energy & Infrastructure',
          'sub_cou_display' => 'Sustainable Energy and Infrastructure',
          'last_interest' => 'Windographer Data Analysis',
        ],
      ],
      'Transaction Security' => [
        'Banking and Finance' => [
          'sub_cou_id' => 'Information Management Security',
          'sub_cou_display' => 'Information Management Security',
          'last_interest' => 'Banking and Finance',
        ],
        'Government and Federal' => [
          'sub_cou_id' => 'Information Management Security',
          'sub_cou_display' => 'Information Management Security',
          'last_interest' => 'Government and Federal',
        ],
        'Healthcare and eHealth' => [
          'sub_cou_id' => 'Information Management Security',
          'sub_cou_display' => 'Information Management Security',
          'last_interest' => 'Healthcare and eHealth',
        ],
        'Mobile and Telecom' => [
          'sub_cou_id' => 'Information Management Security',
          'sub_cou_display' => 'Information Management Security',
          'last_interest' => 'Mobile and Telecom',
        ],
        'Other' => [
          'sub_cou_id' => 'Information Management Security',
          'sub_cou_display' => 'Information Management Security',
          'last_interest' => 'N/A - Transaction Security',
        ],
        'BLANK' => [
          'sub_cou_id' => 'Information Management Security',
          'sub_cou_display' => 'Information Management Security',
          'last_interest' => 'N/A - Transaction Security',
        ],
        'N/A - Transaction Security' => [
          'sub_cou_id' => 'Information Management Security',
          'sub_cou_display' => 'Information Management Security',
          'last_interest' => 'N/A - Transaction Security',
        ],
        'Retail and Payments' => [
          'sub_cou_id' => 'Information Management Security',
          'sub_cou_display' => 'Information Management Security',
          'last_interest' => 'Retail and Payments',
        ],
        'Transit and Transportation' => [
          'sub_cou_id' => 'Information Management Security',
          'sub_cou_display' => 'Information Management Security',
          'last_interest' => 'Transit and Transportation',
        ],
      ],
      'Health Sciences' => [
        'Digital Health' => [
          'sub_cou_id' => 'Information Management Security',
          'sub_cou_display' => 'Information Management Security',
          'last_interest' => 'Digital Health',
        ],
        'Healthcare Product Testing & Certification' => [
          'sub_cou_id' => 'Medical Device Testing and Regulatory',
          'sub_cou_display' => 'Medical Device Testing and Regulatory',
          'last_interest' => 'Healthcare Product Testing & Certification',
        ],
        'INMETRO' => [
          'sub_cou_id' => 'Medical Device Testing and Regulatory',
          'sub_cou_display' => 'Medical Device Testing and Regulatory',
          'last_interest' => 'INMETRO',
        ],
        'Laboratory' => [
          'sub_cou_id' => 'Medical Device Testing and Regulatory',
          'sub_cou_display' => 'Medical Device Testing and Regulatory',
          'last_interest' => 'Laboratory',
        ],
        'Other' => [
          'sub_cou_id' => 'Medical Device Testing and Regulatory',
          'sub_cou_display' => 'Medical Device Testing and Regulatory',
          'last_interest' => 'N/A - Health Sciences',
        ],
        'BLANK' => [
          'sub_cou_id' => 'Medical Device Testing and Regulatory',
          'sub_cou_display' => 'Medical Device Testing and Regulatory',
          'last_interest' => 'N/A - Health Sciences',
        ],
        'N/A - Health Sciences' => [
          'sub_cou_id' => 'Medical Device Testing and Regulatory',
          'sub_cou_display' => 'Medical Device Testing and Regulatory',
          'last_interest' => 'N/A - Health Sciences',
        ],
        'Non-Clinical Testing' => [
          'sub_cou_id' => 'Medical Device Testing and Regulatory',
          'sub_cou_display' => 'Medical Device Testing and Regulatory',
          'last_interest' => 'Non-Clinical Testing',
        ],
        'QMS Certification' => [
          'sub_cou_id' => 'Medical Device Testing and Regulatory',
          'sub_cou_display' => 'Medical Device Testing and Regulatory',
          'last_interest' => 'QMS Certification',
        ],
      ],
      'Field Services' => [
        'PSS' => [
          'sub_cou_id' => 'Product Compliance',
          'sub_cou_display' => 'Product Compliance',
          'last_interest' => 'PSS',
        ],
        'CCC/CQC' => [
          'sub_cou_id' => 'Field Services',
          'sub_cou_display' => 'Field Services',
          'last_interest' => 'CCC/CQC',
        ],
        'EASA' => [
          'sub_cou_id' => 'Field Services',
          'sub_cou_display' => 'Field Services',
          'last_interest' => 'EASA (North America Service Offering)',
        ],
        'FES - Aerial, Ground, Pump' => [
          'sub_cou_id' => 'Field Services',
          'sub_cou_display' => 'Field Services',
          'last_interest' => 'Fire Equipment Services In-Service',
        ],
        'FES- ARFF, VIP, FES' => [
          'sub_cou_id' => 'Field Services',
          'sub_cou_display' => 'Field Services',
          'last_interest' => 'Fire Equipment Services Factory',
        ],
        'Field Evaluation' => [
          'sub_cou_id' => 'Field Services',
          'sub_cou_display' => 'Field Services',
          'last_interest' => 'Field Evaluation',
        ],
        'BLANK' => [
          'sub_cou_id' => 'Field Services',
          'sub_cou_display' => 'Field Services',
          'last_interest' => 'Field Evaluation',
        ],
        'Firestop (QFCP, SFRM, MACC)' => [
          'sub_cou_id' => 'Field Services',
          'sub_cou_display' => 'Field Services',
          'last_interest' => 'Fire Barrier Management',
        ],
        'Firestop Inspections, Fire Door, Dampers' => [
          'sub_cou_id' => 'Field Services',
          'sub_cou_display' => 'Field Services',
          'last_interest' => 'Fire Barrier Management',
        ],
        'LPS' => [
          'sub_cou_id' => 'Field Services',
          'sub_cou_display' => 'Field Services',
          'last_interest' => 'LPS',
        ],
        'Targeted Training' => [
          'sub_cou_id' => 'Field Services',
          'sub_cou_display' => 'Field Services',
          'last_interest' => 'Targeted Training',
        ],
        'Field Connect' => [
          'sub_cou_id' => 'Field Services',
          'sub_cou_display' => 'Field Services',
          'last_interest' => 'Field Connect',
        ],
        'Variation Notice (VN)' => [
          'sub_cou_id' => 'Field Services',
          'sub_cou_display' => 'Field Services',
          'last_interest' => 'Variation Notice (VN)',
        ],
        'Adjunct Services (UL-ES, FL-QA, BSAI, Simmons QA)' => [
          'sub_cou_id' => 'Field Services',
          'sub_cou_display' => 'Field Services',
          'last_interest' => 'Adjunct Services (UL-ES, FL-QA, BSAI, Simmons QA)',
        ],
      ],
      'Appliances' => [
        'Commercial Cooking' => [
          'sub_cou_id' => 'Appliances and HVAC',
          'sub_cou_display' => 'Major Appliances and HVAC',
          'last_interest' => 'Commercial Cooking',
        ],
        'Door Operators & Systems' => [
          'sub_cou_id' => 'Appliances and HVAC',
          'sub_cou_display' => 'Major Appliances and HVAC',
          'last_interest' => 'Door Operators & Systems',
        ],
        'Fans' => [
          'sub_cou_id' => 'Appliances and HVAC',
          'sub_cou_display' => 'Major Appliances and HVAC',
          'last_interest' => 'Fans',
        ],
        'Heaters & Heating Equipment' => [
          'sub_cou_id' => 'Appliances and HVAC',
          'sub_cou_display' => 'Major Appliances and HVAC',
          'last_interest' => 'Heaters & Heating Equipment',
        ],
        'Major Appliances' => [
          'sub_cou_id' => 'Appliances and HVAC',
          'sub_cou_display' => 'Major Appliances and HVAC',
          'last_interest' => 'Major Appliances',
        ],
        'Other' => [
          'sub_cou_id' => 'Appliances and HVAC',
          'sub_cou_display' => 'Major Appliances and HVAC',
          'last_interest' => 'N/A - Appliances',
        ],
        'BLANK' => [
          'sub_cou_id' => 'Appliances and HVAC',
          'sub_cou_display' => 'Major Appliances and HVAC',
          'last_interest' => 'N/A - Appliances',
        ],
        'N/A - Appliances' => [
          'sub_cou_id' => 'Appliances and HVAC',
          'sub_cou_display' => 'Major Appliances and HVAC',
          'last_interest' => 'N/A - Appliances',
        ],
        'Pools & Spa' => [
          'sub_cou_id' => 'Appliances and HVAC',
          'sub_cou_display' => 'Major Appliances and HVAC',
          'last_interest' => 'Pools & Spa',
        ],
        'Residential Refrigeration' => [
          'sub_cou_id' => 'Appliances and HVAC',
          'sub_cou_display' => 'Major Appliances and HVAC',
          'last_interest' => 'Residential Refrigeration',
        ],
        'Specialty Appliances' => [
          'sub_cou_id' => 'Appliances and HVAC',
          'sub_cou_display' => 'Major Appliances and HVAC',
          'last_interest' => 'Specialty Appliances',
        ],
        'Tools' => [
          'sub_cou_id' => 'Appliances and HVAC',
          'sub_cou_display' => 'Major Appliances and HVAC',
          'last_interest' => 'Tools',
        ],
      ],
      'Components' => [
        'Controls' => [
          'sub_cou_id' => 'Appliances and HVAC',
          'sub_cou_display' => 'Major Appliances and HVAC',
          'last_interest' => 'Controls',
        ],
        'Motors' => [
          'sub_cou_id' => 'Appliances and HVAC',
          'sub_cou_display' => 'Major Appliances and HVAC',
          'last_interest' => 'Motors',
        ],
        'Other' => [
          'sub_cou_id' => 'Appliances and HVAC',
          'sub_cou_display' => 'Major Appliances and HVAC',
          'last_interest' => 'N/A - Components',
        ],
        'BLANK' => [
          'sub_cou_id' => 'Appliances and HVAC',
          'sub_cou_display' => 'Major Appliances and HVAC',
          'last_interest' => 'N/A - Components',
        ],
        'N/A - Components' => [
          'sub_cou_id' => 'Appliances and HVAC',
          'sub_cou_display' => 'Major Appliances and HVAC',
          'last_interest' => 'N/A - Components',
        ],
        'Switches and Protectors' => [
          'sub_cou_id' => 'Appliances and HVAC',
          'sub_cou_display' => 'Major Appliances and HVAC',
          'last_interest' => 'Switches and Protectors',
        ],
      ],
      'HVAC' => [
        'Air Conditioner' => [
          'sub_cou_id' => 'Appliances and HVAC',
          'sub_cou_display' => 'Major Appliances and HVAC',
          'last_interest' => 'Air Conditioner',
        ],
        'Heating' => [
          'sub_cou_id' => 'Appliances and HVAC',
          'sub_cou_display' => 'Major Appliances and HVAC',
          'last_interest' => 'Heating',
        ],
        'Other' => [
          'sub_cou_id' => 'Appliances and HVAC',
          'sub_cou_display' => 'Major Appliances and HVAC',
          'last_interest' => 'N/A - HVAC',
        ],
        'BLANK' => [
          'sub_cou_id' => 'Appliances and HVAC',
          'sub_cou_display' => 'Major Appliances and HVAC',
          'last_interest' => 'N/A - HVAC',
        ],
        'N/A - HVAC' => [
          'sub_cou_id' => 'Appliances and HVAC',
          'sub_cou_display' => 'Major Appliances and HVAC',
          'last_interest' => 'N/A - HVAC',
        ],
        'Ventilation' => [
          'sub_cou_id' => 'Appliances and HVAC',
          'sub_cou_display' => 'Major Appliances and HVAC',
          'last_interest' => 'Ventilation',
        ],
      ],
      'Lighting' => [
        'LED Luminaires' => [
          'sub_cou_id' => 'Lighting',
          'sub_cou_display' => 'Lighting',
          'last_interest' => 'LED Luminaires',
        ],
        'LED Portable Luminaires' => [
          'sub_cou_id' => 'Lighting',
          'sub_cou_display' => 'Lighting',
          'last_interest' => 'LED Portable Luminaires',
        ],
        'Lighting Component' => [
          'sub_cou_id' => 'Lighting',
          'sub_cou_display' => 'Lighting',
          'last_interest' => 'Lighting Component',
        ],
        'Lighting Lamp' => [
          'sub_cou_id' => 'Lighting',
          'sub_cou_display' => 'Lighting',
          'last_interest' => 'Lighting Lamp',
        ],
        'Lighting/Luminaires' => [
          'sub_cou_id' => 'Lighting',
          'sub_cou_display' => 'Lighting',
          'last_interest' => 'Lighting/Luminaires',
        ],
        'Other' => [
          'sub_cou_id' => 'Lighting',
          'sub_cou_display' => 'Lighting',
          'last_interest' => 'N/A - Lighting',
        ],
        'BLANK' => [
          'sub_cou_id' => 'Lighting',
          'sub_cou_display' => 'Lighting',
          'last_interest' => 'N/A - Lighting',
        ],
        'N/A - Lighting' => [
          'sub_cou_id' => 'Lighting',
          'sub_cou_display' => 'Lighting',
          'last_interest' => 'N/A - Lighting',
        ],
        'Specialty Lamp Control Equipment' => [
          'sub_cou_id' => 'Lighting',
          'sub_cou_display' => 'Lighting',
          'last_interest' => 'Specialty Lamp Control Equipment',
        ],
        'Specialty Luminaires' => [
          'sub_cou_id' => 'Lighting',
          'sub_cou_display' => 'Lighting',
          'last_interest' => 'Specialty Luminaires',
        ],
        'Specialty Portable Luminaires' => [
          'sub_cou_id' => 'Lighting',
          'sub_cou_display' => 'Lighting',
          'last_interest' => 'Specialty Portable Luminaires',
        ],
      ],
      'Water' => [
        'Exposure Water' => [
          'sub_cou_id' => 'Water System',
          'sub_cou_display' => 'Water Systems',
          'last_interest' => 'Exposure Water',
        ],
        'Other' => [
          'sub_cou_id' => 'Water System',
          'sub_cou_display' => 'Water Systems',
          'last_interest' => 'N/A - Water',
        ],
        'BLANK' => [
          'sub_cou_id' => 'Water System',
          'sub_cou_display' => 'Water Systems',
          'last_interest' => 'N/A - Water',
        ],
        'N/A - Water' => [
          'sub_cou_id' => 'Water System',
          'sub_cou_display' => 'Water Systems',
          'last_interest' => 'N/A - Water',
        ],
        'Water Treatment Chemicals' => [
          'sub_cou_id' => 'Water System',
          'sub_cou_display' => 'Water Systems',
          'last_interest' => 'Water Treatment Chemicals',
        ],
      ],
      'High Tech' => [
        'Aerospace/Defence/Rail' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'Aerospace/Defence/Rail',
        ],
        'Appliances / Lighting' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'Appliances / Lighting',
        ],
        'Audio/Visual Equipment' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'Audio/Visual Equipment',
        ],
        'Automotive' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'Automotive',
        ],
        'Batteries / Charging' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'Batteries / Charging',
        ],
        'Consumer Electronics' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'Consumer Electronics',
        ],
        'E-Toys' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'E-Toys',
        ],
        'Facility - High Tech' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'Facility - High Tech',
        ],
        'Filament Wound Tubing' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'Filament Wound Tubing',
        ],
        'High Tech Components' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'High Tech Components',
        ],
        'High Tech Power Supplies' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'High Tech Power Supplies',
        ],
        'Industrial Electronics' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'Industrial Electronics',
        ],
        'Information Technology Equipment' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'Information Technology Equipment',
        ],
        'IOT Smart Cars' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'IOT Smart Cars',
        ],
        'IOT Smart Homes' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'IOT Smart Homes',
        ],
        'IOT Smart Phones' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'IOT Smart Phones',
        ],
        'IOT Wearables' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'IOT Wearables',
        ],
        'Medical Technologies' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'Medical Technologies',
        ],
        'Mobile and Telecommunications Products' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'Mobile and Telecommunications Products',
        ],
        'Other - High Tech' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'N/A - High Tech',
        ],
        'BLANK' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'N/A - High Tech',
        ],
        'N/A - High Tech' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'N/A - High Tech',
        ],
        'Other - Information and Communication Technology' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'N/A - Information and Communication Technology',
        ],
        'N/A - Information and Communication Technology' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'N/A - Information and Communication Technology',
        ],
        'Other - Internet of Things' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'N/A - Internet of Things',
        ],
        'N/A - Internet of Things' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'N/A - Internet of Things',
        ],
        'PCB / PWB / Components' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'PCB / PWB / Components',
        ],
        'Power Supplies - Automotive' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'Power Supplies - Automotive',
        ],
        'Power Supplies (small)' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'Power Supplies (small)',
        ],
        'Vulcanized Fiber' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'Vulcanized Fiber',
        ],
        'Wearable Electronics' => [
          'sub_cou_id' => 'Consumer Technologies',
          'sub_cou_display' => 'Consumer Technologies',
          'last_interest' => 'Wearable Electronics',
        ],
      ],
      'Small Appliances' => [
        'Animal Care and Control Appliances' => [
          'sub_cou_id' => 'Small Appliances',
          'sub_cou_display' => 'Small Appliances',
          'last_interest' => 'Animal Care and Control Appliances',
        ],
        'Floor Care Appliances' => [
          'sub_cou_id' => 'Small Appliances',
          'sub_cou_display' => 'Small Appliances',
          'last_interest' => 'Floor Care Appliances',
        ],
        'Garment Care Appliances' => [
          'sub_cou_id' => 'Small Appliances',
          'sub_cou_display' => 'Small Appliances',
          'last_interest' => 'Garment Care Appliances',
        ],
        'Grooming Appliances' => [
          'sub_cou_id' => 'Small Appliances',
          'sub_cou_display' => 'Small Appliances',
          'last_interest' => 'Grooming Appliances',
        ],
        'Heating Appliances' => [
          'sub_cou_id' => 'Small Appliances',
          'sub_cou_display' => 'Small Appliances',
          'last_interest' => 'Heating Appliances',
        ],
        'Home Comfort Appliances' => [
          'sub_cou_id' => 'Small Appliances',
          'sub_cou_display' => 'Small Appliances',
          'last_interest' => 'Home Comfort Appliances',
        ],
        'Home Comfort Appliances - Vaporizers' => [
          'sub_cou_id' => 'Small Appliances',
          'sub_cou_display' => 'Small Appliances',
          'last_interest' => 'Home Comfort Appliances - Vaporizers',
        ],
        'Kitchen Appliances' => [
          'sub_cou_id' => 'Small Appliances',
          'sub_cou_display' => 'Small Appliances',
          'last_interest' => 'Kitchen Appliances',
        ],
        'Massage and Exercise Equipment' => [
          'sub_cou_id' => 'Small Appliances',
          'sub_cou_display' => 'Small Appliances',
          'last_interest' => 'Massage and Exercise Equipment',
        ],
        'Other' => [
          'sub_cou_id' => 'Small Appliances',
          'sub_cou_display' => 'Small Appliances',
          'last_interest' => 'N/A - Small Appliances',
        ],
        'BLANK' => [
          'sub_cou_id' => 'Small Appliances',
          'sub_cou_display' => 'Small Appliances',
          'last_interest' => 'N/A - Small Appliances',
        ],
        'N/A - Small Appliances' => [
          'sub_cou_id' => 'Small Appliances',
          'sub_cou_display' => 'Small Appliances',
          'last_interest' => 'N/A - Small Appliances',
        ],
      ],
      'Automotive' => [
        'Automotive Functional Safety' => [
          'sub_cou_id' => 'Automotive Functional Safety',
          'sub_cou_display' => 'Automotive Functional Safety',
          'last_interest' => 'Automotive Functional Safety',
        ],
        'Autonomy Safety' => [
          'sub_cou_id' => 'Automotive Total',
          'sub_cou_display' => 'Automotive Autonomy Safety and Homologation',
          'last_interest' => 'Autonomy Safety',
        ],
        'Homologation' => [
          'sub_cou_id' => 'Automotive Total',
          'sub_cou_display' => 'Automotive Autonomy Safety and Homologation',
          'last_interest' => 'Homologation',
        ],
        'Other' => [
          'sub_cou_id' => 'Automotive Total',
          'sub_cou_display' => 'Automotive Autonomy Safety and Homologation',
          'last_interest' => 'N/A - Automotive',
        ],
        'N/A - Automotive' => [
          'sub_cou_id' => 'Automotive Total',
          'sub_cou_display' => 'Automotive Autonomy Safety and Homologation',
          'last_interest' => 'N/A - Automotive',
        ],
        'BLANK' => [
          'sub_cou_id' => 'Automotive Total',
          'sub_cou_display' => 'Automotive Autonomy Safety and Homologation',
          'last_interest' => 'N/A - Automotive',
        ],
      ],
      'CRS' => [
        'Food & Beverage' => [
          'sub_cou_id' => 'Food',
          'sub_cou_display' => 'Food and Beverage',
          'last_interest' => 'Food & Beverage',
        ],
        'Cannabidiol (CBD)' => [
          'sub_cou_id' => 'Health, Beauty and Wellness',
          'sub_cou_display' => 'Health, Beauty and Wellness',
          'last_interest' => 'Cannabidiol (CBD)',
        ],
        'Dietary Supplements' => [
          'sub_cou_id' => 'Health, Beauty and Wellness',
          'sub_cou_display' => 'Health, Beauty and Wellness',
          'last_interest' => 'Dietary Supplements',
        ],
        'Household & Industrial Cleaning' => [
          'sub_cou_id' => 'Health, Beauty and Wellness',
          'sub_cou_display' => 'Health, Beauty and Wellness',
          'last_interest' => 'Household & Industrial Cleaning',
        ],
        'OTC & Pharmaceuticals' => [
          'sub_cou_id' => 'Health, Beauty and Wellness',
          'sub_cou_display' => 'Health, Beauty and Wellness',
          'last_interest' => 'OTC & Pharmaceuticals',
        ],
        'Personal Care & Beauty' => [
          'sub_cou_id' => 'Health, Beauty and Wellness',
          'sub_cou_display' => 'Health, Beauty and Wellness',
          'last_interest' => 'Personal Care & Beauty',
        ],
        'Consumer Products - Other' => [
          'sub_cou_id' => 'Hardlines and Softlines',
          'sub_cou_display' => 'Hardlines and Softlines',
          'last_interest' => 'Consumer Products - Other',
        ],
        'BLANK' => [
          'sub_cou_id' => 'Hardlines and Softlines',
          'sub_cou_display' => 'Hardlines and Softlines',
          'last_interest' => 'Consumer Products - Other',
        ],
        'CRS - Private Label' => [
          'sub_cou_id' => 'Hardlines and Softlines',
          'sub_cou_display' => 'Hardlines and Softlines',
          'last_interest' => 'CRS - Private Label',
        ],
        'CRS - Retail' => [
          'sub_cou_id' => 'Hardlines and Softlines',
          'sub_cou_display' => 'Hardlines and Softlines',
          'last_interest' => 'CRS - Retail',
        ],
        'General Merchandise & Hardlines' => [
          'sub_cou_id' => 'Hardlines and Softlines',
          'sub_cou_display' => 'Hardlines and Softlines',
          'last_interest' => 'General Merchandise & Hardlines',
        ],
        'Jewelry & Watches' => [
          'sub_cou_id' => 'Hardlines and Softlines',
          'sub_cou_display' => 'Hardlines and Softlines',
          'last_interest' => 'Jewelry & Watches',
        ],
        'Premiums, Promotional & Licensed Goods' => [
          'sub_cou_id' => 'Hardlines and Softlines',
          'sub_cou_display' => 'Hardlines and Softlines',
          'last_interest' => 'Premiums, Promotional & Licensed Goods',
        ],
        'Textiles, Apparel, & Footwear' => [
          'sub_cou_id' => 'Hardlines and Softlines',
          'sub_cou_display' => 'Hardlines and Softlines',
          'last_interest' => 'Textiles, Apparel, & Footwear',
        ],
        'Toys & Juvenile Products' => [
          'sub_cou_id' => 'Hardlines and Softlines',
          'sub_cou_display' => 'Hardlines and Softlines',
          'last_interest' => 'Toys & Juvenile Products',
        ],
      ],
      'Furniture' => [
        'Audio & Video Carts and Stands' => [
          'sub_cou_id' => 'Furniture Testing',
          'sub_cou_display' => 'Furniture Testing',
          'last_interest' => 'Audio & Video Carts and Stands',
        ],
        'Cabinets / Vanities' => [
          'sub_cou_id' => 'Furniture Testing',
          'sub_cou_display' => 'Furniture Testing',
          'last_interest' => 'Cabinets / Vanities',
        ],
        'Children\'s' => [
          'sub_cou_id' => 'Furniture Testing',
          'sub_cou_display' => 'Furniture Testing',
          'last_interest' => 'Children’s',
        ],
        'Educational' => [
          'sub_cou_id' => 'Furniture Testing',
          'sub_cou_display' => 'Furniture Testing',
          'last_interest' => 'Educational',
        ],
        'Foam' => [
          'sub_cou_id' => 'Furniture Testing',
          'sub_cou_display' => 'Furniture Testing',
          'last_interest' => 'Foam',
        ],
        'Furniture Components' => [
          'sub_cou_id' => 'Furniture Testing',
          'sub_cou_display' => 'Furniture Testing',
          'last_interest' => 'Furniture Components',
        ],
        'Furniture Test Equipment' => [
          'sub_cou_id' => 'Furniture Testing',
          'sub_cou_display' => 'Furniture Testing',
          'last_interest' => 'Furniture Test Equipment',
        ],
        'General Furniture' => [
          'sub_cou_id' => 'Furniture Testing',
          'sub_cou_display' => 'Furniture Testing',
          'last_interest' => 'General Furniture',
        ],
        'Healthcare / Institutional Furniture' => [
          'sub_cou_id' => 'Furniture Testing',
          'sub_cou_display' => 'Furniture Testing',
          'last_interest' => 'Healthcare / Institutional Furniture',
        ],
        'Hospitality' => [
          'sub_cou_id' => 'Furniture Testing',
          'sub_cou_display' => 'Furniture Testing',
          'last_interest' => 'Hospitality',
        ],
        'Mattresses and Bedding' => [
          'sub_cou_id' => 'Furniture Testing',
          'sub_cou_display' => 'Furniture Testing',
          'last_interest' => 'Mattresses and Bedding',
        ],
        'Other' => [
          'sub_cou_id' => 'Furniture Testing',
          'sub_cou_display' => 'Furniture Testing',
          'last_interest' => 'N/A - Furniture',
        ],
        'BLANK' => [
          'sub_cou_id' => 'Furniture Testing',
          'sub_cou_display' => 'Furniture Testing',
          'last_interest' => 'N/A - Furniture',
        ],
        'N/A - Furniture' => [
          'sub_cou_id' => 'Furniture Testing',
          'sub_cou_display' => 'Furniture Testing',
          'last_interest' => 'N/A - Furniture',
        ],
        'Office' => [
          'sub_cou_id' => 'Furniture Testing',
          'sub_cou_display' => 'Furniture Testing',
          'last_interest' => 'Office',
        ],
        'Outdoor Furniture' => [
          'sub_cou_id' => 'Furniture Testing',
          'sub_cou_display' => 'Furniture Testing',
          'last_interest' => 'Outdoor Furniture',
        ],
        'Residential' => [
          'sub_cou_id' => 'Furniture Testing',
          'sub_cou_display' => 'Furniture Testing',
          'last_interest' => 'Residential',
        ],
        'Retail Fixtures, Displays & Kiosk' => [
          'sub_cou_id' => 'Furniture Testing',
          'sub_cou_display' => 'Furniture Testing',
          'last_interest' => 'Retail Fixtures, Displays & Kiosk',
        ],
      ],
      'Life Safety & Security' => [
        'Personal Flotation Devices (PFD)' => [
          'sub_cou_id' => 'Personal Flotation Devices',
          'sub_cou_display' => 'Personal Flotation Devices',
          'last_interest' => 'Personal Flotation Devices (PFD)',
        ],
        'Life Safety & Security' => [
          'sub_cou_id' => 'Resistance / Security',
          'sub_cou_display' => 'Building Materials and Safety',
          'last_interest' => 'Life Safety & Security',
        ],
        'Other' => [
          'sub_cou_id' => 'Resistance / Security',
          'sub_cou_display' => 'Building Materials and Safety',
          'last_interest' => 'N/A - Life Safety & Security',
        ],
        'N/A - Life Safety & Security' => [
          'sub_cou_id' => 'Resistance / Security',
          'sub_cou_display' => 'Building Materials and Safety',
          'last_interest' => 'N/A - Life Safety & Security',
        ],
        'BLANK' => [
          'sub_cou_id' => 'Resistance / Security',
          'sub_cou_display' => 'Building Materials and Safety',
          'last_interest' => 'N/A - Life Safety & Security',
        ],
        'Security Equipment' => [
          'sub_cou_id' => 'Resistance / Security',
          'sub_cou_display' => 'Building Materials and Safety',
          'last_interest' => 'Security Equipment',
        ],
        'Fire and Emergency Vehicles' => [
          'sub_cou_id' => 'Fire Equipment Services',
          'sub_cou_display' => 'Fire Equipment Services',
          'last_interest' => 'Fire and Emergency Vehicles',
        ],
        'Initiating and Indicating Devices' => [
          'sub_cou_id' => 'Suppression / Detection',
          'sub_cou_display' => 'Suppression and Detection Systems and Services',
          'last_interest' => 'Initiating and Indicating Devices',
        ],
        'Alarm Certificate Service' => [
          'sub_cou_id' => 'Suppression / Detection',
          'sub_cou_display' => 'Suppression and Detection Systems and Services',
          'last_interest' => 'Alarm Certificate Service',
        ],
        'Fire Alarm Control & Communication Equipment' => [
          'sub_cou_id' => 'Suppression / Detection',
          'sub_cou_display' => 'Suppression and Detection Systems and Services',
          'last_interest' => 'Fire Alarm Control & Communication Equipment',
        ],
        'Personal Protection Equipment (PPE)' => [
          'sub_cou_id' => 'Personal Protective Equipment',
          'sub_cou_display' => 'Personal Protection Equipment',
          'last_interest' => 'Personal Protection Equipment (PPE)',
        ],
      ],
      'Environment' => [
        'Architecture - Commercial' => [
          'sub_cou_id' => 'Product Sustainability',
          'sub_cou_display' => 'Product Sustainability',
          'last_interest' => 'Architecture – Commercial',
        ],
        'Architecture - Mixed Use' => [
          'sub_cou_id' => 'Product Sustainability',
          'sub_cou_display' => 'Product Sustainability',
          'last_interest' => 'Architecture – Mixed Use',
        ],
        'Architecture - Residential' => [
          'sub_cou_id' => 'Product Sustainability',
          'sub_cou_display' => 'Product Sustainability',
          'last_interest' => 'Architecture – Residential',
        ],
        'Cleaning Products' => [
          'sub_cou_id' => 'Product Sustainability',
          'sub_cou_display' => 'Product Sustainability',
          'last_interest' => 'Cleaning Products',
        ],
        'Construction - Commercial' => [
          'sub_cou_id' => 'Product Sustainability',
          'sub_cou_display' => 'Product Sustainability',
          'last_interest' => 'Construction – Commercial',
        ],
        'Construction - Design Build' => [
          'sub_cou_id' => 'Product Sustainability',
          'sub_cou_display' => 'Product Sustainability',
          'last_interest' => 'Construction – Design Build',
        ],
        'Construction - Residential' => [
          'sub_cou_id' => 'Product Sustainability',
          'sub_cou_display' => 'Product Sustainability',
          'last_interest' => 'Construction – Residential',
        ],
        'Distributor' => [
          'sub_cou_id' => 'Product Sustainability',
          'sub_cou_display' => 'Product Sustainability',
          'last_interest' => 'Distributor',
        ],
        'Energy' => [
          'sub_cou_id' => 'Product Sustainability',
          'sub_cou_display' => 'Product Sustainability',
          'last_interest' => 'Energy',
        ],
        'Environment - Other' => [
          'sub_cou_id' => 'Product Sustainability',
          'sub_cou_display' => 'Product Sustainability',
          'last_interest' => 'Environment – Other',
        ],
        'BLANK' => [
          'sub_cou_id' => 'Product Sustainability',
          'sub_cou_display' => 'Product Sustainability',
          'last_interest' => 'Environment – Other',
        ],
        'Facility Management' => [
          'sub_cou_id' => 'Product Sustainability',
          'sub_cou_display' => 'Product Sustainability',
          'last_interest' => 'Facility Management',
        ],
        'Industry Association' => [
          'sub_cou_id' => 'Product Sustainability',
          'sub_cou_display' => 'Product Sustainability',
          'last_interest' => 'Industry Association',
        ],
        'Manufacturer - Building Products' => [
          'sub_cou_id' => 'Product Sustainability',
          'sub_cou_display' => 'Product Sustainability',
          'last_interest' => 'Manufacturer – Building Products',
        ],
        'Manufacturer - Chemicals' => [
          'sub_cou_id' => 'Product Sustainability',
          'sub_cou_display' => 'Product Sustainability',
          'last_interest' => 'Manufacturer – Chemicals',
        ],
        'Manufacturer - Consumer Goods' => [
          'sub_cou_id' => 'Product Sustainability',
          'sub_cou_display' => 'Product Sustainability',
          'last_interest' => 'Manufacturer – Consumer Goods',
        ],
        'Mechanical' => [
          'sub_cou_id' => 'Product Sustainability',
          'sub_cou_display' => 'Product Sustainability',
          'last_interest' => 'Mechanical',
        ],
        'Non-Profit' => [
          'sub_cou_id' => 'Product Sustainability',
          'sub_cou_display' => 'Product Sustainability',
          'last_interest' => 'Non-Profit',
        ],
        'Paints' => [
          'sub_cou_id' => 'Product Sustainability',
          'sub_cou_display' => 'Product Sustainability',
          'last_interest' => 'Paints',
        ],
        'Raw Products' => [
          'sub_cou_id' => 'Product Sustainability',
          'sub_cou_display' => 'Product Sustainability',
          'last_interest' => 'Raw Products',
        ],
        'Real Estate' => [
          'sub_cou_id' => 'Product Sustainability',
          'sub_cou_display' => 'Product Sustainability',
          'last_interest' => 'Real Estate',
        ],
        'Retailer - Building Products' => [
          'sub_cou_id' => 'Product Sustainability',
          'sub_cou_display' => 'Product Sustainability',
          'last_interest' => 'Retailer – Building Products',
        ],
        'Retailer - Consumder Goods' => [
          'sub_cou_id' => 'Product Sustainability',
          'sub_cou_display' => 'Product Sustainability',
          'last_interest' => 'Retailer – Consumer Goods',
        ],
        'Retailer - Furniture' => [
          'sub_cou_id' => 'Product Sustainability',
          'sub_cou_display' => 'Product Sustainability',
          'last_interest' => 'Retailer – Furniture',
        ],
      ],
      'Building Materials & Systems' => [
        'Air Ducts & Systems' => [
          'sub_cou_id' => 'Resistance / Security',
          'sub_cou_display' => 'Building Materials and Safety',
          'last_interest' => 'Air Ducts & Systems',
        ],
        'Building Envelope Products' => [
          'sub_cou_id' => 'Resistance / Security',
          'sub_cou_display' => 'Building Materials and Safety',
          'last_interest' => 'Building Envelope Products',
        ],
        'Building Products' => [
          'sub_cou_id' => 'Resistance / Security',
          'sub_cou_display' => 'Building Materials and Safety',
          'last_interest' => 'Building Products',
        ],
        'Construction Equipment' => [
          'sub_cou_id' => 'Resistance / Security',
          'sub_cou_display' => 'Building Materials and Safety',
          'last_interest' => 'Construction Equipment',
        ],
        'Environmental Claims Products' => [
          'sub_cou_id' => 'Resistance / Security',
          'sub_cou_display' => 'Building Materials and Safety',
          'last_interest' => 'Environmental Claims Products',
        ],
        'Fire Resistive Products' => [
          'sub_cou_id' => 'Resistance / Security',
          'sub_cou_display' => 'Building Materials and Safety',
          'last_interest' => 'Fire Resistive Products',
        ],
        'Ladders, Scaffoldings & Hoists' => [
          'sub_cou_id' => 'Resistance / Security',
          'sub_cou_display' => 'Building Materials and Safety',
          'last_interest' => 'Ladders, Scaffoldings & Hoists',
        ],
        'Other' => [
          'sub_cou_id' => 'Resistance / Security',
          'sub_cou_display' => 'Building Materials and Safety',
          'last_interest' => 'N/A - Building Materials & Systems',
        ],
        'BLANK' => [
          'sub_cou_id' => 'Resistance / Security',
          'sub_cou_display' => 'Building Materials and Safety',
          'last_interest' => 'N/A - Building Materials & Systems',
        ],
        'N/A - Building Materials & Systems' => [
          'sub_cou_id' => 'Resistance / Security',
          'sub_cou_display' => 'Building Materials and Safety',
          'last_interest' => 'N/A - Building Materials & Systems',
        ],
        'Opening Protective Products' => [
          'sub_cou_id' => 'Resistance / Security',
          'sub_cou_display' => 'Building Materials and Safety',
          'last_interest' => 'Opening Protective Products',
        ],
        'Roofing Products' => [
          'sub_cou_id' => 'Resistance / Security',
          'sub_cou_display' => 'Building Materials and Safety',
          'last_interest' => 'Roofing Products',
        ],
        'Plumbing Products' => [
          'sub_cou_id' => 'Suppression / Detection',
          'sub_cou_display' => 'Suppression and Detection Systems and Services',
          'last_interest' => 'Plumbing Products',
        ],
        'Fire Suppression Products' => [
          'sub_cou_id' => 'Suppression / Detection',
          'sub_cou_display' => 'Suppression and Detection Systems and Services',
          'last_interest' => 'Fire Suppression Products',
        ],
        'Building Inspection Services' => [
          'sub_cou_id' => 'Suppression / Detection',
          'sub_cou_display' => 'Suppression and Detection Systems and Services',
          'last_interest' => 'Building Inspection Services',
        ],
      ],
      'Functional Safety' => [
        'None' => [
          'sub_cou_id' => 'Industrial Functional Safety',
          'sub_cou_display' => 'Industrial Functional Safety',
          'last_interest' => 'N/A - Functional Safety',
        ],
        'BLANK' => [
          'sub_cou_id' => 'Industrial Functional Safety',
          'sub_cou_display' => 'Industrial Functional Safety',
          'last_interest' => 'N/A - Functional Safety',
        ],
      ],
      'Energy' => [
        'E-Mobility' => [
          'sub_cou_id' => 'Renewable Energy TIC',
          'sub_cou_display' => 'Renewable Energy',
          'last_interest' => 'E-Mobility',
        ],
        'Energy Systems' => [
          'sub_cou_id' => 'Renewable Energy TIC',
          'sub_cou_display' => 'Renewable Energy',
          'last_interest' => 'Energy Systems',
        ],
        'Solar' => [
          'sub_cou_id' => 'Renewable Energy TIC',
          'sub_cou_display' => 'Renewable Energy',
          'last_interest' => 'Solar',
        ],
        'Wind' => [
          'sub_cou_id' => 'Renewable Energy TIC',
          'sub_cou_display' => 'Renewable Energy',
          'last_interest' => 'Wind',
        ],
        'Other' => [
          'sub_cou_id' => 'Power and Automation TIC',
          'sub_cou_display' => 'Power and Automation Safety',
          'last_interest' => 'N/A - Energy & Power Technologies',
        ],
        'BLANK' => [
          'sub_cou_id' => 'Power and Automation TIC',
          'sub_cou_display' => 'Power and Automation Safety',
          'last_interest' => 'N/A - Energy & Power Technologies',
        ],
        'N/A - Energy & Power Technologies' => [
          'sub_cou_id' => 'Power and Automation TIC',
          'sub_cou_display' => 'Power and Automation Safety',
          'last_interest' => 'N/A - Energy & Power Technologies',
        ],
        'Oil & Gas' => [
          'sub_cou_id' => 'Power and Automation TIC',
          'sub_cou_display' => 'Power and Automation Safety',
          'last_interest' => 'Oil & Gas',
        ],
      ],
      'Power Distribution' => [
        'Circuit Protection' => [
          'sub_cou_id' => 'Power and Automation TIC',
          'sub_cou_display' => 'Power and Automation Safety',
          'last_interest' => 'Circuit Protection',
        ],
        'Electrical Distribution' => [
          'sub_cou_id' => 'Power and Automation TIC',
          'sub_cou_display' => 'Power and Automation Safety',
          'last_interest' => 'Electrical Distribution',
        ],
        'Other' => [
          'sub_cou_id' => 'Power and Automation TIC',
          'sub_cou_display' => 'Power and Automation Safety',
          'last_interest' => 'N/A - Energy & Power Technologies',
        ],
        'BLANK' => [
          'sub_cou_id' => 'Power and Automation TIC',
          'sub_cou_display' => 'Power and Automation Safety',
          'last_interest' => 'N/A - Energy & Power Technologies',
        ],
        'N/A - Energy & Power Technologies' => [
          'sub_cou_id' => 'Power and Automation TIC',
          'sub_cou_display' => 'Power and Automation Safety',
          'last_interest' => 'N/A - Energy & Power Technologies',
        ],
        'Wiring Devices' => [
          'sub_cou_id' => 'Power and Automation TIC',
          'sub_cou_display' => 'Power and Automation Safety',
          'last_interest' => 'Wiring Devices',
        ],
      ],
      'Industrial Systems and Components' => [
        'Electronic Controls' => [
          'sub_cou_id' => 'Power and Automation TIC',
          'sub_cou_display' => 'Power and Automation Safety',
          'last_interest' => 'Electronic Controls',
        ],
        'Factory Control Systems' => [
          'sub_cou_id' => 'Power and Automation TIC',
          'sub_cou_display' => 'Power and Automation Safety',
          'last_interest' => 'Factory Control Systems',
        ],
        'Other' => [
          'sub_cou_id' => 'Power and Automation TIC',
          'sub_cou_display' => 'Power and Automation Safety',
          'last_interest' => 'N/A - Energy & Power Technologies',
        ],
        'BLANK' => [
          'sub_cou_id' => 'Power and Automation TIC',
          'sub_cou_display' => 'Power and Automation Safety',
          'last_interest' => 'N/A - Energy & Power Technologies',
        ],
        'N/A - Energy & Power Technologies' => [
          'sub_cou_id' => 'Power and Automation TIC',
          'sub_cou_display' => 'Power and Automation Safety',
          'last_interest' => 'N/A - Energy & Power Technologies',
        ],
        'Switching Devices' => [
          'sub_cou_id' => 'Power and Automation TIC',
          'sub_cou_display' => 'Power and Automation Safety',
          'last_interest' => 'Switching Devices',
        ],
      ],
      'Hazardous Locations' => [
        'Other' => [
          'sub_cou_id' => 'Power and Automation TIC',
          'sub_cou_display' => 'Power and Automation Safety',
          'last_interest' => 'Hazardous Locations',
        ],
        'BLANK' => [
          'sub_cou_id' => 'Power and Automation TIC',
          'sub_cou_display' => 'Power and Automation Safety',
          'last_interest' => 'Hazardous Locations',
        ],
        'N/A - Energy & Power Technologies' => [
          'sub_cou_id' => 'Power and Automation TIC',
          'sub_cou_display' => 'Power and Automation Safety',
          'last_interest' => 'N/A - Energy & Power Technologies',
        ],
        'Oil & Gas' => [
          'sub_cou_id' => 'Power and Automation TIC',
          'sub_cou_display' => 'Power and Automation Safety',
          'last_interest' => 'Oil & Gas',
        ],
      ],
      'Performance Materials' => [
        'Electrical Insulation Materials and System' => [
          'sub_cou_id' => 'Materials and Plastics',
          'sub_cou_display' => 'Materials and Plastics',
          'last_interest' => 'Electrical Insulation Materials and Systems',
        ],
        'Gaskets and Seals' => [
          'sub_cou_id' => 'Materials and Plastics',
          'sub_cou_display' => 'Materials and Plastics',
          'last_interest' => 'Gaskets and Seals',
        ],
        'Marking and Labeling' => [
          'sub_cou_id' => 'Materials and Plastics',
          'sub_cou_display' => 'Materials and Plastics',
          'last_interest' => 'Marking and Labeling',
        ],
        'Other' => [
          'sub_cou_id' => 'Materials and Plastics',
          'sub_cou_display' => 'Materials and Plastics',
          'last_interest' => 'N/A - Performance Materials',
        ],
        'BLANK' => [
          'sub_cou_id' => 'Materials and Plastics',
          'sub_cou_display' => 'Materials and Plastics',
          'last_interest' => 'N/A - Performance Materials',
        ],
        'N/A - Performance Materials' => [
          'sub_cou_id' => 'Materials and Plastics',
          'sub_cou_display' => 'Materials and Plastics',
          'last_interest' => 'N/A - Performance Materials',
        ],
        'Performance Materials Automotive' => [
          'sub_cou_id' => 'Materials and Plastics',
          'sub_cou_display' => 'Materials and Plastics',
          'last_interest' => 'Performance Materials Automotive',
        ],
        'Plastics' => [
          'sub_cou_id' => 'Materials and Plastics',
          'sub_cou_display' => 'Materials and Plastics',
          'last_interest' => 'Plastics',
        ],
        'Positioning Devices' => [
          'sub_cou_id' => 'Materials and Plastics',
          'sub_cou_display' => 'Materials and Plastics',
          'last_interest' => 'Positioning Devices',
        ],
        'Tape, Tubing, Sleeving' => [
          'sub_cou_id' => 'Materials and Plastics',
          'sub_cou_display' => 'Materials and Plastics',
          'last_interest' => 'Tape, Tubing, Sleeving',
        ],
      ],
      'Additive Manufacturing' => [
        'Conformity Advisory' => [
          'sub_cou_id' => 'Materials and Plastics',
          'sub_cou_display' => 'Materials and Plastics',
          'last_interest' => 'Conformity Advisory',
        ],
        'Equipment Compliance' => [
          'sub_cou_id' => 'Materials and Plastics',
          'sub_cou_display' => 'Materials and Plastics',
          'last_interest' => 'Equipment Compliance',
        ],
        'Facility Safety' => [
          'sub_cou_id' => 'Materials and Plastics',
          'sub_cou_display' => 'Materials and Plastics',
          'last_interest' => 'Facility Safety',
        ],
        'Materials and Process' => [
          'sub_cou_id' => 'Materials and Plastics',
          'sub_cou_display' => 'Materials and Plastics',
          'last_interest' => 'Materials and Process',
        ],
        'Other' => [
          'sub_cou_id' => 'Materials and Plastics',
          'sub_cou_display' => 'Materials and Plastics',
          'last_interest' => 'N/A - Additive Manufacturing',
        ],
        'BLANK' => [
          'sub_cou_id' => 'Materials and Plastics',
          'sub_cou_display' => 'Materials and Plastics',
          'last_interest' => 'N/A - Additive Manufacturing',
        ],
        'N/A - Additive Manufacturing' => [
          'sub_cou_id' => 'Materials and Plastics',
          'sub_cou_display' => 'Materials and Plastics',
          'last_interest' => 'N/A - Additive Manufacturing',
        ],
        'Training' => [
          'sub_cou_id' => 'Materials and Plastics',
          'sub_cou_display' => 'Materials and Plastics',
          'last_interest' => 'Training',
        ],
      ],
      'Wire & Cable' => [
        'Components (Wire & Cable)' => [
          'sub_cou_id' => 'Wire and Cable',
          'sub_cou_display' => 'Wire and Cable',
          'last_interest' => 'Components (Wire & Cable)',
        ],
        'Components and Accessories for W&C' => [
          'sub_cou_id' => 'Wire and Cable',
          'sub_cou_display' => 'Wire and Cable',
          'last_interest' => 'Components and Accessories for W&C',
        ],
        'Compounds for use in W&C' => [
          'sub_cou_id' => 'Wire and Cable',
          'sub_cou_display' => 'Wire and Cable',
          'last_interest' => 'Compounds for use in W&C',
        ],
        'Copper Wire' => [
          'sub_cou_id' => 'Wire and Cable',
          'sub_cou_display' => 'Wire and Cable',
          'last_interest' => 'Copper Wire',
        ],
        'Fiber Optic Cable and Components' => [
          'sub_cou_id' => 'Wire and Cable',
          'sub_cou_display' => 'Wire and Cable',
          'last_interest' => 'Fiber Optic Cable and Components',
        ],
        'Metallic Cable' => [
          'sub_cou_id' => 'Wire and Cable',
          'sub_cou_display' => 'Wire and Cable',
          'last_interest' => 'Metallic Cable',
        ],
        'Other' => [
          'sub_cou_id' => 'Wire and Cable',
          'sub_cou_display' => 'Wire and Cable',
          'last_interest' => 'N/A - Wire & Cable',
        ],
        'BLANK' => [
          'sub_cou_id' => 'Wire and Cable',
          'sub_cou_display' => 'Wire and Cable',
          'last_interest' => 'N/A - Wire & Cable',
        ],
        'N/A - Wire & Cable' => [
          'sub_cou_id' => 'Wire and Cable',
          'sub_cou_display' => 'Wire and Cable',
          'last_interest' => 'N/A - Wire & Cable',
        ],
        'Optical Fiber' => [
          'sub_cou_id' => 'Wire and Cable',
          'sub_cou_display' => 'Wire and Cable',
          'last_interest' => 'Optical Fiber',
        ],
        'Power Cords' => [
          'sub_cou_id' => 'Wire and Cable',
          'sub_cou_display' => 'Wire and Cable',
          'last_interest' => 'Power Cords',
        ],
        'Seasonal Products' => [
          'sub_cou_id' => 'Wire and Cable',
          'sub_cou_display' => 'Wire and Cable',
          'last_interest' => 'Seasonal Products',
        ],
        'Wiring Harnesses' => [
          'sub_cou_id' => 'Wire and Cable',
          'sub_cou_display' => 'Wire and Cable',
          'last_interest' => 'Wiring Harnesses',
        ],
      ],
    ],
    'SFDC' => [
      'Compliance Reporting' => [
        'sub_cou_id' => 'Sustainability',
        'sub_cou_display' => 'Sustainability Management and Reporting',
        'last_interest' => 'Compliance Reporting',
      ],
      'ESG Reporting' => [
        'sub_cou_id' => 'Sustainability',
        'sub_cou_display' => 'Sustainability Management and Reporting',
        'last_interest' => 'ESG Reporting',
      ],
      'ESG Reporting for Property' => [
        'sub_cou_id' => 'Sustainability',
        'sub_cou_display' => 'Sustainability Management and Reporting',
        'last_interest' => 'ESG Reporting for Property',
      ],
      'GRI Reporting' => [
        'sub_cou_id' => 'Sustainability',
        'sub_cou_display' => 'Sustainability Management and Reporting',
        'last_interest' => 'GRI Reporting',
      ],
      'Sustainability Management and Reporting' => [
        'sub_cou_id' => 'Sustainability',
        'sub_cou_display' => 'Sustainability Management and Reporting',
        'last_interest' => 'Sustainability Management and Reporting',
      ],
      'Waste Management and Reporting' => [
        'sub_cou_id' => 'Sustainability',
        'sub_cou_display' => 'Sustainability Management and Reporting',
        'last_interest' => 'Waste Management and Reporting',
      ],
      'Carbon Management and Reporting' => [
        'sub_cou_id' => 'Sustainability',
        'sub_cou_display' => 'Sustainability Management and Reporting',
        'last_interest' => 'Carbon Management and Reporting',
      ],
      'Incident/Audit Management Software' => [
        'sub_cou_id' => 'EHS',
        'sub_cou_display' => 'Environment, Health and Safety (EHS)',
        'last_interest' => 'Incident/Audit Management Software',
      ],
      'Medical Billing Software' => [
        'sub_cou_id' => 'EHS',
        'sub_cou_display' => 'Environment, Health and Safety (EHS)',
        'last_interest' => 'Medical Billing Software',
      ],
      'Occupational Health Software' => [
        'sub_cou_id' => 'EHS',
        'sub_cou_display' => 'Environment, Health and Safety (EHS)',
        'last_interest' => 'Occupational Health Software',
      ],
      'Occupational Health and Safety Management Software' => [
        'sub_cou_id' => 'EHS',
        'sub_cou_display' => 'Environment, Health and Safety (EHS)',
        'last_interest' => 'Occupational Health Software',
      ],
      'Advisory Services' => [
        'sub_cou_id' => 'EHS',
        'sub_cou_display' => 'Environment, Health and Safety (EHS)',
        'last_interest' => 'Advisory Services',
      ],
      'EHS Online Training' => [
        'sub_cou_id' => 'EHS',
        'sub_cou_display' => 'Environment, Health and Safety (EHS)',
        'last_interest' => 'EHS Online Training',
      ],
      'Learning & Talent Management Systems' => [
        'sub_cou_id' => 'EHS',
        'sub_cou_display' => 'Environment, Health and Safety (EHS)',
        'last_interest' => 'Learning & Talent Management Systems',
      ],
      'Safety Consulting Services' => [
        'sub_cou_id' => 'EHS',
        'sub_cou_display' => 'Environment, Health and Safety (EHS)',
        'last_interest' => 'Safety Consulting Services',
      ],
      'Healthy Buildings' => [
        'sub_cou_id' => 'Sustainable Real Estate',
        'sub_cou_display' => 'Sustainable Real Estate',
        'last_interest' => 'Healthy Buildings',
      ],
      'Product Performance Benchmarking' => [
        'sub_cou_id' => 'Benchmarks',
        'sub_cou_display' => 'Product Performance Benchmarking',
        'last_interest' => 'Product Performance Benchmarking',
      ],
      'Custom Course Development' => [
        'sub_cou_id' => 'ComplianceWire',
        'sub_cou_display' => 'Learning Management',
        'last_interest' => 'Custom Course Development',
      ],
      'Life Sciences Online Training' => [
        'sub_cou_id' => 'ComplianceWire',
        'sub_cou_display' => 'Learning Management',
        'last_interest' => 'Life Sciences Online Training',
      ],
      'Regulatory Compliance Training' => [
        'sub_cou_id' => 'ComplianceWire',
        'sub_cou_display' => 'Learning Management',
        'last_interest' => 'Regulatory Compliance Training',
      ],
      'Product Promotion and Intelligence - Certification' => [
        'sub_cou_id' => 'Sourcing & Data Insights',
        'sub_cou_display' => 'Product Sourcing and Data Insights',
        'last_interest' => 'Product Promotion and Intelligence - Certification',
      ],
      'Materials & Components Search Engines' => [
        'sub_cou_id' => 'Sourcing & Data Insights',
        'sub_cou_display' => 'Product Sourcing and Data Insights',
        'last_interest' => 'Materials & Components Search Engines',
      ],
      'Materials Search' => [
        'sub_cou_id' => 'Sourcing & Data Insights',
        'sub_cou_display' => 'Product Sourcing and Data Insights',
        'last_interest' => 'Materials Search',
      ],
      'Lead Generation & Digital Advertising' => [
        'sub_cou_id' => 'Sourcing & Data Insights',
        'sub_cou_display' => 'Product Sourcing and Data Insights',
        'last_interest' => 'Lead Generation & Digital Advertising',
      ],
      'Product Marketing Services' => [
        'sub_cou_id' => 'Sourcing & Data Insights',
        'sub_cou_display' => 'Product Sourcing and Data Insights',
        'last_interest' => 'Product Marketing Services',
      ],
      'Product Promotion & Differentiation' => [
        'sub_cou_id' => 'Sourcing & Data Insights',
        'sub_cou_display' => 'Product Sourcing and Data Insights',
        'last_interest' => 'Product Promotion & Differentiation',
      ],
      'Technical Datasheet Solutions' => [
        'sub_cou_id' => 'Sourcing & Data Insights',
        'sub_cou_display' => 'Product Sourcing and Data Insights',
        'last_interest' => 'Technical Datasheet Solutions',
      ],
      'Hazard Communication Software' => [
        'sub_cou_id' => 'Product Compliance',
        'sub_cou_display' => 'Product Compliance',
        'last_interest' => 'Hazard Communication Software',
      ],
      'Regulatory Compliance & SDS Authoring' => [
        'sub_cou_id' => 'Product Compliance',
        'sub_cou_display' => 'Product Compliance',
        'last_interest' => 'Regulatory Compliance & SDS Authoring',
      ],
      'Supply Chain Management Software' => [
        'sub_cou_id' => 'Product Compliance',
        'sub_cou_display' => 'Product Compliance',
        'last_interest' => 'Supply Chain Management Software',
      ],
      'Chemical Policy Management Software' => [
        'sub_cou_id' => 'Product Compliance',
        'sub_cou_display' => 'Product Compliance',
        'last_interest' => 'Chemical Policy Management Software',
      ],
      'Chemical Registration' => [
        'sub_cou_id' => 'Product Compliance',
        'sub_cou_display' => 'Product Compliance',
        'last_interest' => 'Chemical Registration',
      ],
      'Chemical Regulatory Advisory Services' => [
        'sub_cou_id' => 'Product Compliance',
        'sub_cou_display' => 'Product Compliance',
        'last_interest' => 'Chemical Regulatory Advisory Services',
      ],
      'Chemical Regulatory Database (LoLi)' => [
        'sub_cou_id' => 'Product Compliance',
        'sub_cou_display' => 'Product Compliance',
        'last_interest' => 'Chemical Regulatory Database (LoLi)',
      ],
      'Chemical Regulatory Training' => [
        'sub_cou_id' => 'Product Compliance',
        'sub_cou_display' => 'Product Compliance',
        'last_interest' => 'Chemical Regulatory Training',
      ],
      'Regulatory Advisory Services & Training' => [
        'sub_cou_id' => 'Product Compliance',
        'sub_cou_display' => 'Product Compliance',
        'last_interest' => 'Regulatory Advisory Services & Training',
      ],
      'Regulatory Insights & Monitoring' => [
        'sub_cou_id' => 'Product Compliance',
        'sub_cou_display' => 'Product Compliance',
        'last_interest' => 'Regulatory Insights & Monitoring',
      ],
      'Safety Data Sheet (SDS) Authoring' => [
        'sub_cou_id' => 'Product Compliance',
        'sub_cou_display' => 'Product Compliance',
        'last_interest' => 'Safety Data Sheet (SDS) Authoring',
      ],
      'Technical Competency Development/Management' => [
        'sub_cou_id' => 'Product Compliance',
        'sub_cou_display' => 'Product Compliance',
        'last_interest' => 'Technical Competency Development/Management',
      ],
      'Transport & Dangerous Goods' => [
        'sub_cou_id' => 'Product Compliance',
        'sub_cou_display' => 'Product Compliance',
        'last_interest' => 'Transport & Dangerous Goods',
      ],
      'Chemical Policy Development & Management' => [
        'sub_cou_id' => 'Product Compliance',
        'sub_cou_display' => 'Product Compliance',
        'last_interest' => 'Chemical Policy Development & Management',
      ],
      'Chemical R&D Software' => [
        'sub_cou_id' => 'Product Compliance',
        'sub_cou_display' => 'Product Compliance',
        'last_interest' => 'Chemical R&D Software',
      ],
      'Chemical Toxicology' => [
        'sub_cou_id' => 'Product Compliance',
        'sub_cou_display' => 'Product Compliance',
        'last_interest' => 'Chemical Toxicology',
      ],
      'R&D Decision Support' => [
        'sub_cou_id' => 'Product Compliance',
        'sub_cou_display' => 'Product Compliance',
        'last_interest' => 'R&D Decision Support',
      ],
      'Retail Compliance' => [
        'sub_cou_id' => 'Product Compliance',
        'sub_cou_display' => 'Product Compliance',
        'last_interest' => 'Retail Compliance',
      ],
      'Retail Software Solutions' => [
        'sub_cou_id' => 'Product Compliance',
        'sub_cou_display' => 'Product Compliance',
        'last_interest' => 'Retail Software Solutions',
      ],
      'Supply Chain Stewardship' => [
        'sub_cou_id' => 'Product Compliance',
        'sub_cou_display' => 'Product Compliance',
        'last_interest' => 'Supply Chain Stewardship',
      ],
      'Audio & Video Carts and Stands' => [
        'sub_cou_id' => 'Furniture Testing',
        'sub_cou_display' => 'Furniture Testing',
        'last_interest' => 'Audio & Video Carts and Stands',
      ],
      'Cabinets/Vanities' => [
        'sub_cou_id' => 'Furniture Testing',
        'sub_cou_display' => 'Furniture Testing',
        'last_interest' => 'Cabinets / Vanities',
      ],
      'Children\'s' => [
        'sub_cou_id' => 'Furniture Testing',
        'sub_cou_display' => 'Furniture Testing',
        'last_interest' => 'Children’s',
      ],
      'Educational' => [
        'sub_cou_id' => 'Furniture Testing',
        'sub_cou_display' => 'Furniture Testing',
        'last_interest' => 'Educational',
      ],
      'Foam' => [
        'sub_cou_id' => 'Furniture Testing',
        'sub_cou_display' => 'Furniture Testing',
        'last_interest' => 'Foam',
      ],
      'Furniture Components' => [
        'sub_cou_id' => 'Furniture Testing',
        'sub_cou_display' => 'Furniture Testing',
        'last_interest' => 'Furniture Components',
      ],
      'Furniture Test Equipment' => [
        'sub_cou_id' => 'Furniture Testing',
        'sub_cou_display' => 'Furniture Testing',
        'last_interest' => 'Furniture Test Equipment',
      ],
      'General Furniture' => [
        'sub_cou_id' => 'Furniture Testing',
        'sub_cou_display' => 'Furniture Testing',
        'last_interest' => 'General Furniture',
      ],
      'Healthcare / Institutional Furniture' => [
        'sub_cou_id' => 'Furniture Testing',
        'sub_cou_display' => 'Furniture Testing',
        'last_interest' => 'Healthcare / Institutional Furniture',
      ],
      'Hospitality' => [
        'sub_cou_id' => 'Furniture Testing',
        'sub_cou_display' => 'Furniture Testing',
        'last_interest' => 'Hospitality',
      ],
      'Mattresses and Bedding' => [
        'sub_cou_id' => 'Furniture Testing',
        'sub_cou_display' => 'Furniture Testing',
        'last_interest' => 'Mattresses and Bedding',
      ],
      'N/A - Furniture' => [
        'sub_cou_id' => 'Furniture Testing',
        'sub_cou_display' => 'Furniture Testing',
        'last_interest' => 'N/A - Furniture',
      ],
      'Office' => [
        'sub_cou_id' => 'Furniture Testing',
        'sub_cou_display' => 'Furniture Testing',
        'last_interest' => 'Office',
      ],
      'Outdoor Furniture' => [
        'sub_cou_id' => 'Furniture Testing',
        'sub_cou_display' => 'Furniture Testing',
        'last_interest' => 'Outdoor Furniture',
      ],
      'Residential' => [
        'sub_cou_id' => 'Furniture Testing',
        'sub_cou_display' => 'Furniture Testing',
        'last_interest' => 'Residential',
      ],
      'Retail Fixtures, Displays & Kiosk' => [
        'sub_cou_id' => 'Furniture Testing',
        'sub_cou_display' => 'Furniture Testing',
        'last_interest' => 'Retail Fixtures, Displays & Kiosk',
      ],
      'Architecture - Commercial' => [
        'sub_cou_id' => 'Product Sustainability',
        'sub_cou_display' => 'Product Sustainability',
        'last_interest' => 'Architecture – Commercial',
      ],
      'Architecture - Mixed Use' => [
        'sub_cou_id' => 'Product Sustainability',
        'sub_cou_display' => 'Product Sustainability',
        'last_interest' => 'Architecture – Mixed Use',
      ],
      'Architecture - Residential' => [
        'sub_cou_id' => 'Product Sustainability',
        'sub_cou_display' => 'Product Sustainability',
        'last_interest' => 'Architecture – Residential',
      ],
      'Cleaning Products' => [
        'sub_cou_id' => 'Product Sustainability',
        'sub_cou_display' => 'Product Sustainability',
        'last_interest' => 'Cleaning Products',
      ],
      'Construction - Commercial' => [
        'sub_cou_id' => 'Product Sustainability',
        'sub_cou_display' => 'Product Sustainability',
        'last_interest' => 'Construction – Commercial',
      ],
      'Construction - Design Build' => [
        'sub_cou_id' => 'Product Sustainability',
        'sub_cou_display' => 'Product Sustainability',
        'last_interest' => 'Construction – Design Build',
      ],
      'Construction - Residential' => [
        'sub_cou_id' => 'Product Sustainability',
        'sub_cou_display' => 'Product Sustainability',
        'last_interest' => 'Construction – Residential',
      ],
      'Distributor' => [
        'sub_cou_id' => 'Product Sustainability',
        'sub_cou_display' => 'Product Sustainability',
        'last_interest' => 'Distributor',
      ],
      'Energy' => [
        'sub_cou_id' => 'Product Sustainability',
        'sub_cou_display' => 'Product Sustainability',
        'last_interest' => 'Energy',
      ],
      'Environment - Other' => [
        'sub_cou_id' => 'Product Sustainability',
        'sub_cou_display' => 'Product Sustainability',
        'last_interest' => 'Environment – Other',
      ],
      'Facility Management' => [
        'sub_cou_id' => 'Product Sustainability',
        'sub_cou_display' => 'Product Sustainability',
        'last_interest' => 'Facility Management',
      ],
      'Industry Association' => [
        'sub_cou_id' => 'Product Sustainability',
        'sub_cou_display' => 'Product Sustainability',
        'last_interest' => 'Industry Association',
      ],
      'Manufacturer - Building Products' => [
        'sub_cou_id' => 'Product Sustainability',
        'sub_cou_display' => 'Product Sustainability',
        'last_interest' => 'Manufacturer – Building Products',
      ],
      'Manufacturer - Chemicals' => [
        'sub_cou_id' => 'Product Sustainability',
        'sub_cou_display' => 'Product Sustainability',
        'last_interest' => 'Manufacturer – Chemicals',
      ],
      'Manufacturer - Consumer Goods' => [
        'sub_cou_id' => 'Product Sustainability',
        'sub_cou_display' => 'Product Sustainability',
        'last_interest' => 'Manufacturer – Consumer Goods',
      ],
      'Mechanical' => [
        'sub_cou_id' => 'Product Sustainability',
        'sub_cou_display' => 'Product Sustainability',
        'last_interest' => 'Mechanical',
      ],
      'Non-Profit' => [
        'sub_cou_id' => 'Product Sustainability',
        'sub_cou_display' => 'Product Sustainability',
        'last_interest' => 'Non-Profit',
      ],
      'Paints' => [
        'sub_cou_id' => 'Product Sustainability',
        'sub_cou_display' => 'Product Sustainability',
        'last_interest' => 'Paints',
      ],
      'Raw Products' => [
        'sub_cou_id' => 'Product Sustainability',
        'sub_cou_display' => 'Product Sustainability',
        'last_interest' => 'Raw Products',
      ],
      'Real Estate' => [
        'sub_cou_id' => 'Product Sustainability',
        'sub_cou_display' => 'Product Sustainability',
        'last_interest' => 'Real Estate',
      ],
      'Retailer - Building Products' => [
        'sub_cou_id' => 'Product Sustainability',
        'sub_cou_display' => 'Product Sustainability',
        'last_interest' => 'Retailer – Building Products',
      ],
      'Retailer - Consumer Goods' => [
        'sub_cou_id' => 'Product Sustainability',
        'sub_cou_display' => 'Product Sustainability',
        'last_interest' => 'Retailer – Consumer Goods',
      ],
      'Retailer - Furniture' => [
        'sub_cou_id' => 'Product Sustainability',
        'sub_cou_display' => 'Product Sustainability',
        'last_interest' => 'Retailer – Furniture',
      ],
      'Electrical Insulation Materials and Systems' => [
        'sub_cou_id' => 'Materials and Plastics',
        'sub_cou_display' => 'Materials and Plastics',
        'last_interest' => 'Electrical Insulation Materials and Systems',
      ],
      'Gaskets and Seals' => [
        'sub_cou_id' => 'Materials and Plastics',
        'sub_cou_display' => 'Materials and Plastics',
        'last_interest' => 'Gaskets and Seals',
      ],
      'Marking and Labeling' => [
        'sub_cou_id' => 'Materials and Plastics',
        'sub_cou_display' => 'Materials and Plastics',
        'last_interest' => 'Marking and Labeling',
      ],
      'N/A - Performance Materials' => [
        'sub_cou_id' => 'Materials and Plastics',
        'sub_cou_display' => 'Materials and Plastics',
        'last_interest' => 'N/A - Performance Materials',
      ],
      'Performance Materials Automotive' => [
        'sub_cou_id' => 'Materials and Plastics',
        'sub_cou_display' => 'Materials and Plastics',
        'last_interest' => 'Performance Materials Automotive',
      ],
      'Plastics' => [
        'sub_cou_id' => 'Materials and Plastics',
        'sub_cou_display' => 'Materials and Plastics',
        'last_interest' => 'Plastics',
      ],
      'Positioning Devices' => [
        'sub_cou_id' => 'Materials and Plastics',
        'sub_cou_display' => 'Materials and Plastics',
        'last_interest' => 'Positioning Devices',
      ],
      'Tape, Tubing, Sleeving' => [
        'sub_cou_id' => 'Materials and Plastics',
        'sub_cou_display' => 'Materials and Plastics',
        'last_interest' => 'Tape, Tubing, Sleeving',
      ],
      'Conformity Advisory' => [
        'sub_cou_id' => 'Materials and Plastics',
        'sub_cou_display' => 'Materials and Plastics',
        'last_interest' => 'Conformity Advisory',
      ],
      'Equipment Compliance' => [
        'sub_cou_id' => 'Materials and Plastics',
        'sub_cou_display' => 'Materials and Plastics',
        'last_interest' => 'Equipment Compliance',
      ],
      'Facility Safety' => [
        'sub_cou_id' => 'Materials and Plastics',
        'sub_cou_display' => 'Materials and Plastics',
        'last_interest' => 'Facility Safety',
      ],
      'Materials and Process' => [
        'sub_cou_id' => 'Materials and Plastics',
        'sub_cou_display' => 'Materials and Plastics',
        'last_interest' => 'Materials and Process',
      ],
      'N/A - Additive Manufacturing' => [
        'sub_cou_id' => 'Materials and Plastics',
        'sub_cou_display' => 'Materials and Plastics',
        'last_interest' => 'N/A - Additive Manufacturing',
      ],
      'Training' => [
        'sub_cou_id' => 'Materials and Plastics',
        'sub_cou_display' => 'Materials and Plastics',
        'last_interest' => 'Training',
      ],
    ],
  ];

}

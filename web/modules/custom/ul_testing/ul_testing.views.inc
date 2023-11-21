<?php

/**
 * @file
 * Provide Views data for the UL Testing.
 *
 * @ingroup views_module_handlers
 */

/**
 * Implements hook_views_data().
 */
function ul_testing_views_data() {
  $data = [];
  // Base data.
  $data['ul_testing_test_node']['table']['group'] = t('UL Testing');
  $data['ul_testing_test_node']['table']['base'] = [
    'title' => t('UL Testing Test Node'),
    'help' => t('Table to track test nodes created by the UL Testing module.'),
    'field' => 'id',
  ];

  $data['ul_testing_test_node']['id'] = [
    'title' => t('ID'),
    'help' => t('ID'),
    'field' => [
      'id' => 'standard',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'argument' => [
      'id' => 'string',
    ],
  ];

  $data['ul_testing_test_node']['qa_id'] = [
    'title' => t('QA ID'),
    'help' => t('QA ID'),
    'field' => [
      'id' => 'standard',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'argument' => [
      'id' => 'string',
    ],
  ];

  $data['ul_testing_test_node']['nid'] = [
    'title' => t('Test Node ID'),
    'help' => t('Test Node ID'),
    'field' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'numeric',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'relationship' => [
      'base' => 'node_field_data',
      'base field' => 'nid',
      'id' => 'standard',
      'label' => t('Test node'),
    ],
  ];

  $data['ul_testing_test_node']['test_group'] = [
    'title' => t('Test Group'),
    'help' => t('Test Group'),
    'field' => [
      'id' => 'standard',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'argument' => [
      'id' => 'string',
    ],
  ];

  $data['ul_testing_test_node']['type'] = [
    'title' => t('Type'),
    'help' => t('Type'),
    'field' => [
      'id' => 'standard',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'argument' => [
      'id' => 'string',
    ],
  ];

  $data['ul_testing_test_node']['table']['join'] = [
    'node_field_data' => [
      'left_field' => 'nid',
      'field' => 'nid',
    ],
  ];

  return $data;
}

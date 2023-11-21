<?php

/**
 * @file
 * Provide Views data for the Ul Report.
 *
 * @ingroup views_module_handlers
 */

/**
 * Implements hook_views_data().
 */
function ul_report_views_data() {
  $data = [];

  // Integrate custom marketo_report table with views.
  // Base data.
  $data['marketo_report']['table']['group'] = t('Marketo Report');
  $data['marketo_report']['table']['base'] = [
    'title' => t('Marketo Report'),
    'help' => t('Mysql View for marketo report data.'),
    'field' => 'nid',
  ];

  $fields = [
    'nid',
    'created',
    'changed',
    'status',
    'uid',
    'user_name',
    'title',
    'type',
    'langcode',
    'instance',
    'sub_cou',
    'last_interest',
    'header_cta',
    'header_cta_form_name',
    'rr_cta',
    'rr_cta_form_name',
    'marketo_paragraphs',
    'form_types',
    'marketo_customizations',
  ];

  // Use standard settings for all fields by default.
  foreach ($fields as $field) {
    $data['marketo_report'][$field] = [
      'title' => ucwords(str_replace('_', ' ', $field)),
      'help' => ucwords(str_replace('_', ' ', $field)),
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
  }

  // For clarity, set the title of the Marketo Paragraphs to Body CTA.
  $data['marketo_report']['marketo_paragraphs']['title'] = 'Body CTA';
  $data['marketo_report']['marketo_paragraphs']['help'] = 'Content Paragraphs with Marketo CTAs';

  $data['marketo_report']['nid'] = [
    'title' => t('Marketo Node ID'),
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
      'label' => 'Marketo node',
    ],
  ];

  $data['marketo_report']['table']['join'] = [
    'node_field_data' => [
      'left_field' => 'nid',
      'field' => 'nid',
    ],
  ];

  $data['node_field_data']['last_interest_filter'] = [
    'title' => t('Last Interest'),
    'group' => t('Content'),
    'filter' => [
      'title' => t('Last Interest'),
      'help' => t('Provides a custom filter for Marketo nodes by their last interest value.'),
      'field' => 'last_interest',
      'id' => 'last_interest_views_filter',
    ],
  ];

  $data['node_field_data']['sub_cou_filter'] = [
    'title' => t('Sub COU'),
    'group' => t('Content'),
    'filter' => [
      'title' => t('Sub COU'),
      'help' => t('Provides a custom filter for Marketo nodes by their sub COU value.'),
      'field' => 'sub_cou',
      'id' => 'sub_cou_views_filter',
    ],
  ];

  $data['node_field_data']['marketo_customization_type_filter'] = [
    'title' => t('Marketo Customization Type'),
    'group' => t('Content'),
    'filter' => [
      'title' => t('Marketo Customization Type'),
      'help' => t('Provides a custom filter for Marketo Customization Type.'),
      'id' => 'marketo_customization_type_views_filter',
    ],
  ];

  $data['node_field_data']['content_sub_type'] = [
    'title' => t('Content Sub Type'),
    'group' => t('Content'),
    'field' => [
      'title' => t('Content Sub Type'),
      'help' => t('The Sub Type of the node.'),
      'id' => 'content_sub_type_views_field',
    ],
    'filter' => [
      'title' => t('Content Sub Type'),
      'help' => t('Provides a custom filter for Content Sub Type.'),
      'id' => 'content_sub_type_views_filter',
    ],
  ];

  // Integrate custom node_media_file table with views.
  // Base data.
  $data['node_media_file']['table']['group'] = t('Node Media and Files');
  $data['node_media_file']['table']['entity type'] = 'node';

  // Node Media File - nid.
  $data['node_media_file']['nid'] = [
    'title' => 'Node ID',
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
  ];

  // Node Media File - mid.
  $data['node_media_file']['mid'] = [
    'title' => 'Media ID',
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
  ];

  // Node Media File - fid.
  $data['node_media_file']['fid'] = [
    'title' => 'File ID',
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
  ];

  // Create relationship between file_managed and node_media_file.
  $data['file_managed']['node_media_file_fid']['relationship'] = [
    'title' => t('Node Referencing File'),
    'help' => t('Relate file entities to the nodes referencing them.'),
    'id' => 'standard',
    'base' => 'node_media_file',
    'base field' => 'fid',
    'field' => 'fid',
    'label' => t('Node Referencing File'),
  ];

  $data['node_media_file']['table']['join'] = [
    'file_managed' => [
      'field' => 'fid',
      'left_field' => 'fid',
    ],
  ];

  $data['node_field_data']['table']['join'] = [
    'node_media_file' => [
      'field' => 'nid',
      'left_field' => 'nid',
      'extra' => [
        'field' => 'langcode',
        'left_field' => 'langcode',
      ],
    ],
  ];

  // Create relationship between node_media_file and node_field_data.
  $data['node_media_file']['nid']['relationship'] = [
    'title' => t('Node Referencing Media or File'),
    'help' => t('Relate file or media entities to the nodes referencing them.'),
    'id' => 'standard',
    'base' => 'node_field_data',
    'base field' => 'nid',
    'label' => t('Node Referencing Media or File'),
  ];

  $data['node_media_file']['nid']['relationship']['extra'][] = [
    'field' => 'langcode',
    'left_field' => 'langcode',
  ];

  // Create relationship between media_field_data and node_media_file.
  $data['media_field_data']['node_media_file_mid']['relationship'] = [
    'title' => t('Node Referencing Media'),
    'help' => t('Relate media entities to the nodes referencing them.'),
    'id' => 'standard',
    'base' => 'node_media_file',
    'base field' => 'mid',
    'field' => 'mid',
    'label' => t('Node Referencing Media'),
  ];

  $data['node_media_file']['table']['join'] = [
    'media_field_data' => [
      'field' => 'mid',
      'left_field' => 'mid',
    ],
  ];

  // Create relationship between media_field_data and users_field_data.
  $data['media_field_data']['media_to_user']['relationship'] = [
    'title' => t('Media Author'),
    'help' => t('Relate media entities to the users who created them.'),
    'id' => 'standard',
    'base' => 'users_field_data',
    'base field' => 'uid',
    'field' => 'uid',
    'label' => t('Media Author'),
  ];

  $data['users_field_data']['table']['join'] = [
    'media_field_data' => [
      'field' => 'uid',
      'left_field' => 'uid',
    ],
  ];

  $data['media_field_data']['translation_changed'] = [
    'title' => t('Media translation changed'),
    'group' => t('Media'),
    'field' => [
      'title' => t('Media Changed'),
      'help' => t('The date the media translation was changed.'),
      'id' => 'media_changed_field',
    ],
    'sort' => [
      'field' => 'media_field_data_changed',
      'id' => 'standard'
    ]
  ];

  return $data;
}

<?php

/**
 * @file
 * Defines view fields for the CRC asset.
 */

/**
 * Implements hook_views_data().
 */
function ul_crc_asset_views_data() {
  $data = [];

  $base_table = 'crc_asset';

  $data[$base_table]['table']['group'] = t('UL CRC Asset');
  $data[$base_table]['table']['base'] = [
    'title' => t('UL CRC Asset'),
    'help' => t('UL CRC remote assets.'),
    'query_id' => 'ul_crc_query',
  ];

  // We need to embed Entity browser bulk select.
  $data[$base_table]['entity_browser_select'] = [
    'title' => t('Entity browser bulk select form'),
    'help' => t('Add a form element that lets you use a view as a base to select entities in entity browser.'),
    'field' => [
      'id' => 'entity_browser_select',
      'real field' => 'filename',
    ],
  ];

  $data[$base_table]['id'] = [
    'title' => t('CRC ID'),
    'help' => t('CRC Asset ID'),
    'field' => [
      'id' => 'standard',
    ],
  ];

  $data[$base_table]['filename'] = [
    'title' => t('Name'),
    'help' => t('The filename.'),
    'field' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'string',
    ],
  ];
  $data[$base_table]['available_languages'] = [
    'title' => t('Available Languages'),
    'help' => t('Languages that the asset is available in'),
    'field' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'string',
    ],
  ];
  $data[$base_table]['language'] = [
    'title' => t('Language'),
    'help' => t('Language of asset'),
    'field' => [
      'id' => 'standard',
    ],
    'filter' => [
      'title' => t('Language'),
      'help' => t('Provides a custom filter for CRC Asset Language.'),
      'field' => 'language',
      'id' => 'crc_asset_language_views_filter',
    ],
  ];

  $data[$base_table]['created_at'] = [
    'title' => t('Asset creation time'),
    'help' => t('Asset created timestamp.'),
    'field' => [
      'id' => 'date',
    ],
  ];
  $data[$base_table]['updated_at'] = [
    'title' => t('Asset update time'),
    'help' => t('Asset update timestamp.'),
    'field' => [
      'id' => 'date',
    ],
  ];
  $data[$base_table]['file_type'] = [
    'title' => t('File Type'),
    'help' => t('The file type.'),
    'field' => [
      'id' => 'standard',
    ],
  ];
  $data[$base_table]['exclude_external_videos'] = [
    'title' => t('Exclude External Videos'),
    'help' => t('Exclude External Videos'),
    'field' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'boolean',
    ],
  ];
  $data[$base_table]['file_extension'] = [
    'title' => t('File Extension'),
    'help' => t('The file extension.'),
    'field' => [
      'id' => 'standard',
    ],
  ];
  $data[$base_table]['sm_thumbnail_url'] = [
    'title' => t('Thumbnail (small)'),
    'help' => t('The generated thumbnail'),
    'field' => [
      'id' => 'ul_crc_asset',
    ],
  ];
  $data[$base_table]['med_thumbnail_url'] = [
    'title' => t('Thumbnail (medium)'),
    'help' => t('The generated thumbnail'),
    'field' => [
      'id' => 'ul_crc_asset',
    ],
  ];
  $data[$base_table]['original_url'] = [
    'title' => t('Original URL'),
    'help' => t('The original URL to the asset.'),
    'field' => [
      'id' => 'standard',
    ],
  ];

  return $data;
}

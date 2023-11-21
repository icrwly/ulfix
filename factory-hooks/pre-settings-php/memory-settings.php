<?php

/**
 * @file
 * Set server memory settings.
 */

// HTTP Client Config Timeout.
// This was added to prevent request timeouts.
$settings['http_client_config']['timeout'] = 90;

$increase_memory = FALSE;

// Increase memory for Drush requests.
if (PHP_SAPI === 'cli') {
  $increase_memory = '2048M';
}
// Increase memory for POST requests to /batch for batch processing.
elseif (($_SERVER['REQUEST_METHOD'] == 'POST') && (strpos($_SERVER['REQUEST_URI'], 'batch') !== FALSE)) {
  $increase_memory = '256M';
}

if ($increase_memory) {
  ini_set('memory_limit', $increase_memory);
}

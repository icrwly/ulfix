<?php

/**
 * @file
 * Backup this site.
 *
 * Creates a backup through Workflow API 24 hours, database only.
 */

$data = json_encode(
  [
    // Workflow type.
    'type' => 'do_export',
    'params' => [
      // Required, do not change.
      "entry_type" => "backup",
      // Backup retention in seconds - default is 24 hours. // @todo Increase.
      "ttl" => 86400,
      // Set to true to backup files.
      "files" => FALSE,
      // Set to true to backup code.
      "code" => FALSE,
      // Set to true to backup database.
      "database" => TRUE,
      // If ANY of the 3 elements (files, code, db) are not backed up, the one
      // click restore button will not be available.
    ],
  ]
);

echo "--- Start workflow: backup -- \n\n";
pantheon_curl('https://api.live.getpantheon.com/sites/self/environments/self/workflows', $data, 8443, 'POST');
// @todo Consider adding a wait/pause here.
echo "--- End workflow: backup -- \n\n";

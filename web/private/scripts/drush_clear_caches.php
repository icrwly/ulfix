<?php

/**
 * @file
 * Clears drupal caches.
 */

  echo "Rebuilding Drupal cache...\n";
  $drush_command = 'drush cr 2>&1';
  echo "Executing Drush command: $drush_command\n";
  passthru($drush_command, $exit_code);
  if ($exit_code > 0) {
    echo "Drush command failed with exit code: $exit_code.\n";
  }
  else {
    echo "Drush command executed successfully.\n";
  }


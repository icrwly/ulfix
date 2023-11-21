<?php
/**
 * @file
 * Imports all config changes.
 */

 echo "Importing Drupal yml config...\n";
 $drush_command = 'drush config:import --yes 2>&1';
 echo "Executing Drush command: $drush_command\n";
 passthru($drush_command, $exit_code);
 if ($exit_code > 0) {
   echo "Drush command failed with exit code: $exit_code.\n";
 }
 else {
   echo "Drush command executed successfully.\n";
 }
 




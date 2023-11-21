<?php

/**
 * @file
 * Runs `$ drush deploy:hook` command.
 */

 echo "Running Drupal drupal deploy hooks...\n";
 $drush_command = 'drush deploy:hook --yes 2>&1';
 echo "Executing Drush command: $drush_command\n";
 passthru($drush_command, $exit_code);
 if ($exit_code > 0) {
   echo "Drush command failed with exit code: $exit_code.\n";
 }
 else {
   echo "Drush command executed successfully.\n";
 }
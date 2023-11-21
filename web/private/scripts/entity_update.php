<?php

/**
 * @file
 * Runs `$ drush updb` command.
 */

echo "Running Drupal entity updates...\n";

passthru('drush en devel_entity_updates -y');

$drush_command = 'drush entup --yes 2>&1';
echo "Executing Drush command: $drush_command\n";
passthru($drush_command, $exit_code);
if ($exit_code > 0) {
  echo "Drush command failed with exit code: $exit_code.\n";
}
else {
  echo "Drush command executed successfully.\n";
}

#!/bin/bash
# This drush command is provided by the Advanced CSS/JS Aggregation module.
# Clears the old aggregated cached files and generates new ones.
# Similar to what happens when using the 'Drastic Measurements' in the Op. page.
# Executes via drush alias.

# See https://docs.acquia.com/site-factory/theme/external#refresh.
# $1 = The hosting site group.
# $2 = The hosting environment.
# $5 = The site domain.
site="$1"
env="$2"

# local drush executable:
repo="/var/www/html/$site.$env"

cd $repo
drush @$1.$2 --uri=$5 advagg-clear-all-files

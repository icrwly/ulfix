<?php

/**
 * @file
 * Change value of $conf property to prevent DB deadlocks.
 *
 * @see https://docs.acquia.com/site-factory/extend/hooks/settings-php/#avoiding-database-deadlocks
 */

$conf['acquia_hosting_settings_autoconnect'] = FALSE;

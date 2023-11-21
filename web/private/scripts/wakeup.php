<?php

/**
 * Send a simple request to the current site to wake it up and initiate traffic prior to deploying code.
 */

// Render Environment name with link to site, <https://{ENV}-{SITENAME}.pantheon.io|{ENV}>
$url = 'https://' . $_ENV['PANTHEON_ENVIRONMENT'] . '-' . $_ENV['PANTHEON_SITE_NAME'] . '.pantheonsite.io';

// Watch for messages with `terminus workflows watch --site=SITENAME`
$result = file_get_contents($url);
print("Sent a wakeup call to: $url");

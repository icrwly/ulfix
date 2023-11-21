<?php

/**
 * @file
 * Defines Docksal site mappings.
 */

$host = getenv('VIRTUAL_HOST');
$host = $host ?? 'ulplatform.docksal.site';

$sites["enterprise.$host"] = 'ul_enterprise_profile';
$sites["guidelines.$host"] = 'ul_guidelines_profile';

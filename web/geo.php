<?php

/**
 * @file
 * Returns country code of site visitor plus current datetime.
 */

header('Content-Type: application/json');

$cfIpcountry = 'null';
if (isset($_SERVER['HTTP_CF_IPCOUNTRY'])) {
  $cfIpcountry = $_SERVER['HTTP_CF_IPCOUNTRY'];
}

$ret = [];
$ret['country'] = $cfIpcountry;
$ret['datetime'] = date('r');
echo json_encode($ret);

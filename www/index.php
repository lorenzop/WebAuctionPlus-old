<?php

define('psm\DEBUG',          TRUE);
define('psm\DEFAULT_MODULE', 'wa');
//define('psm\DEFAULT_PAGE',   'current');

// load the portal
include(__DIR__.'/portal/Portal.php');
$portal = \psm\Portal::factory();

?>
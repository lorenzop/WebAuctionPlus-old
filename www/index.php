<?php

define('psm\DEBUG',          TRUE);
define('psm\DEFAULT_MODULE', 'wa');
define('psm\DEFAULT_PAGE',   'current');

// load the portal
include('portal/Portal.php');
$portal = new \psm\Portal('wa');

?>
<?php


global $config;
unset($_SESSION[$config['session name']]);
$lastpage=getVar('lastpage');
if(empty($lastpage)) ForwardTo('./');
else                 ForwardTo($lastpage);
exit();


?>
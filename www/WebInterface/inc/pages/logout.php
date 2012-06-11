<?php
// do logout


CSRF::ValidateToken();
global $config;
$config['user']->doLogout();
ForwardTo(getLastPage());
exit();


?>
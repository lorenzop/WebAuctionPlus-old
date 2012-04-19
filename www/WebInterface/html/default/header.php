<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
global $config,$html,$user;
$output='';


// page header
$output.=
'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <title>{sitepage title}</title>
  <link rel="icon" type="image/x-icon" href="images/favicon.ico" />
  <style type="text/css" title="currentStyle">
  </style>
';
// css
$html->loadCss('main.css');
$html->loadCss('table_jui.css');
$html->loadCss('css/'.SanFilename($config['ui Pack']).'/jquery-ui-1.8.19.custom.css');
//$html->loadCss('jquery-ui-1.8.16.custom.css');
//$html->loadCss($cssFile.'.css');
$output.="  <style type=\"text/css\">\n{css}\n  </style>\n";
// finish header
$output.='
  <script type="text/javascript" language="javascript" src="js/jquery-1.7.2.min.js"></script>
  <script type="text/javascript" language="javascript" src="js/jquery.dataTables-1.9.0.min.js"></script>
  <script type="text/javascript" language="javascript" src="js/inputfunc.js"></script>
{AddToHeader}
</head>
<body>
<div id="holder">
';

switch($html->getPageFrame()){
case 'default':
  $output.='
<table border="0" cellspacing="0" cellpadding="0" id="profile-box">
<tr>
  <td rowspan="4"><img src="./?page=mcface&amp;username='.$user->getName().'" alt="" width="64" height="64" id="mcface" /></td>
  <td>Name:</td><td>'.$user->getName().
      ($user->hasPerms('isAdmin')?'&nbsp;<span style="font-size: small;"><b>[ADMIN]</b></span>':'').'</td>
</tr>
<tr><td>Money:</td><td>'.$user->Money.'</td></tr>
<tr><td>Mail:</td><td>'. $user->numMail.'</td></tr>
<tr><td colspan="2">'.date('jS M Y H:i:s').'</td></tr>
</table>
<div id="menu-box">


<a href="./">Home</a><br />
<a href="./?page=myitems">My Items</a><br />
<a href="./?page=myauctions">My Auctions</a><br />
<a href="./?page=playerstats">Player Stats</a><br />
<a href="./?page=info">Item Info</a><br />
<a href="./?page=transactionLog">Transaction Log</a><br />
<a href="./?page=logout">Logout</a>


</div>
<div id="title-box">
  <h1>{site title}</h1>
  <h2>{page title}</h2>
</div>
';
  break;
case 'basic':
  $output.='
<h1 style="margin-bottom: 30px; text-align: center;">WebAuction Plus</h1>
';
  break;
}


return($output."\n\n\n");
?>

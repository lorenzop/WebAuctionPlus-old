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
  <link rel="icon" type="image/x-icon" href="{path=static}favicon.ico" />
';
// css
RenderHTML::LoadCss('main.css');
RenderHTML::LoadCss('table_jui.css');
RenderHTML::LoadCss($config['paths']['local']['static jquery'].'jquery-ui-1.8.19.custom.css');
//RenderHTML::LoadCss($cssFile.'.css');
$output.='
<style type="text/css">
{css}
</style>
';
// finish header
$output.='
  <script type="text/javascript" language="javascript" src="{path=static}js/jquery-1.7.2.min.js"></script>
  <script type="text/javascript" language="javascript" src="{path=static}js/jquery.dataTables-1.9.0.min.js"></script>
  <script type="text/javascript" language="javascript" src="{path=static}js/inputfunc.js"></script>
{AddToHeader}
</head>
<body>
';

switch($html->getPageFrame()){
case 'default':
  $output.='
<div id="holder">
<div id="profile-box">
{if logged in}
<table border="0" cellspacing="0" cellpadding="0">
<tr>
  <td rowspan="4"><img src="http://minotar.net/avatar/'.$user->getName().'" alt="" width="64" height="64" id="mcface" /></td>
  <td>Name:</td><td>'.$user->getName().
      ($user->hasPerms('isAdmin')?'&nbsp;<a href="admin/" style="font-size: small; font-weight: bold; color: #000000;">[ADMIN]</a>':'').'</td>
</tr>
<tr><td>Money:&nbsp;&nbsp;</td><td>'.FormatPrice($user->Money).'</td></tr>
<tr><td>Mail: &nbsp;&nbsp;</td><td>'. $user->numMail.'</td></tr>
<tr><td colspan="2" style="font-size: 100%; font-weight: bold; text-align: center;">'.@date('jS M Y H:i:s').'</td></tr>
</table>
{else}
<center style="font-size: 30px; margin-top: 10px;"><a href="./?page=login"><u>Login here!</u></a></center>
<center style="font-size: small;">If you don\'t have an account, you can create one using the command \'/wa password <somepassword>\' in game.</center>
{endif}
</div>
<div id="menu-box">


<a href="./">Home</a><br />
{if logged in}
<a href="./?page=myitems">My Items</a><br />
<a href="./?page=myauctions">My Auctions</a><br />
<!--
<a href="./?page=playerstats">Player Stats</a><br />
<a href="./?page=info">Item Info</a><br />
<a href="./?page=transactionlog">Transaction Log</a><br />
-->
<a href="./?page=logout{token}">Logout</a><br />
{else}
<a href="./?page=login">Login</a><br />
{endif}


</div>
<div id="title-box">
  <div id="title-box2">
    <h1 style="margin-bottom: 10px; text-align: center; font-family: Arial;">WebAuction<sup>Plus</sup></h1>
    <h2>{page title}</h2>
  </div>
</div>
';
  break;
case 'basic':
  $output.='
<table border="0" cellspacing="0" cellpadding="0" align="center" style="width: 100%; height: 100%;">
<tr><td style="height: 1px;"><h1 style="margin-bottom: 30px; text-align: center; font-family: Arial; font-size: 45px;">WebAuction<sup>Plus</sup></h1></td></tr>
<tr><td>
';
}


return($output."\n\n\n");
?>
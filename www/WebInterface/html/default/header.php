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
$output.='
<link rel="stylesheet" type="text/css" href="{path=theme}main.css" />
<link rel="stylesheet" type="text/css" href="{path=theme}table_jui.css" />
<link rel="stylesheet" type="text/css" href="{path=static jquery}jquery-ui-1.8.19.custom.css" />
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
<table border="0" cellspacing="0" cellpadding="0" id="titletable">



<tr><td style="width: 1%;">
{if logged in}

<!-- profile box -->
<table border="0" cellspacing="0" cellpadding="0" style="padding-bottom: 2px; text-align:  left; font-size:   20px; font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;">
<tr>
  <td rowspan="4"><img src="./?page=mcskin&user='.$user->getName().'&view=body" alt="" width="60" height="120" id="mcface" /></td>
  <td height="30">Name:</td><td>'.$user->getName().
      ($user->hasPerms('isAdmin')?'&nbsp;<a href="./?dir=admin" style="font-size: small; font-weight: bold;">[ADMIN]</a>':'').'</td>
</tr>
<tr><td height="30">Money:&nbsp;&nbsp;</td><td>'.str_replace(' ','&nbsp;',FormatPrice($user->getMoney())).'</td></tr>
<tr><td colspan="2" align="center" style="font-size: smaller;">'.@date('jS M Y H:i:s').'</td></tr>
</table>

{else}

<!-- login form -->
<form action="./" name="loginform" method="post">
{token form}
<input type="hidden" name="page"     value="login" />
<input type="hidden" name="lastpage" value="./" />
<table border="0" cellspacing="0" cellpadding="0" id="profile-box">
<tr>
  <td align="right"><label for="'.LOGIN_FORM_USERNAME.'">Username:&nbsp;</label></td>
  <td width="290"><input type="text"  name="'.LOGIN_FORM_USERNAME.'" value="" class="input" size="30" tabindex="1" id="'.LOGIN_FORM_USERNAME.'" /></td>
  <td rowspan="3"><input type="submit" name="Submit" value="Submit" class="button" tabindex="3" style="margin-left: 10px;" /></td>
</tr>
<tr><td style="height: 5px;"></td></tr>
<tr>
  <td align="right"><label    for="'.LOGIN_FORM_PASSWORD.'">Password:&nbsp;</label></td>
  <td><input type="password" name="'.LOGIN_FORM_PASSWORD.'" value="" class="input" size="30" tabindex="2" id="'.LOGIN_FORM_PASSWORD.'" /></td>
</tr>
<tr><td style="height: 5px;"></td></tr>
<tr><td colspan="3" align="center" style="font-size: small;">If you don\'t have an account, you can create one using the command:<br />/wa password &lt;somepassword&gt;</td></tr>
</table>
</form>
<script type="text/javascript">
function formfocus() {
  document.getElementById(\''.LOGIN_FORM_USERNAME.'\').focus();
}
window.onload = formfocus;
</script>

{endif}
</td>



<td id="title-box">
  <h1 style="margin-bottom: 10px;">WebAuction<sup>Plus</sup></h1>
  <h2>{page title}</h2>
</td>



{if logged in}
<!-- menu -->
<td id="menu-box">
<a href="./">Home</a><br />
<a href="./?page=myitems">My Items</a><br />
<a href="./?page=myauctions">My Auctions</a><br />
<!--
<a href="./?page=playerstats">Player Stats</a><br />
<a href="./?page=info">Item Info</a><br />
<a href="./?page=transactionlog">Transaction Log</a><br />
-->
<a href="./?page=logout{token}">Logout</a><br />
</td>
{else}
<td id="no-menu-box"></td>
{endif}



</tr></table>
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
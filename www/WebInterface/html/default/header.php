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
';
// css
//$html->loadCss('main.css');
$html->loadCss('bootstrap.css');
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
 <style>
      body {
        padding-top: 60px; //* 60px to make the container go all the way to the bottom of the topbar *//
      }
    </style>
<div class="container-fluid">
    <div class="row-fluid">
    <div class="span2">
    <form class="well"><table class="table">
        <thead>
          <tr>
            <th><img src="http://minotar.net/avatar/'.$user->getName().'" alt="" width="64" height="64" id="mcface" /></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Name:</td><td>'.$user->getName().
      ($user->hasPerms('isAdmin')?'<p style="font-size: small; font-weight: bold; color: #000000;">[ADMIN]</p>':'').'</td>
          </tr>
          <tr>
           
          </tr>
          <tr>
            <td>Money:</td><td>'.FormatPrice($user->Money).'</td>
          </tr>
		  <tr>
		  
		  </tr>
		  <tr>
		  <td>Mail: &nbsp;&nbsp;</td><td>'. $user->numMail.'</td>
		  </tr>
		  <tr>
		  
		  </tr>
		  <tr>
		  <td>'.@date('jS M Y H:i:s').'</td>
		  </tr>
        </tbody>
      </table>
</form>
<form class="well">
        <ul class="nav nav-list">
          <li><a href="./"><i class="icon-home"></i> Home</a></li>
          <li><a href="./?page=myitems"><i class="icon-shopping-cart"></i> My Items</a></li>
          <li><a href="./?page=myauctions"><i class="icon-tag"></i> My Auctions</a></li>
          <li><a href="./?page=logout"><i class="icon-lock"></i> Logout</a></li>
        </ul>
</div>    
<div class="span10"></form>

';
  break;
case 'basic':
  $output.='
    <style type="text/css">
      body {
        padding-bottom: 40px;
      }
	  
	  div.page-header {
        padding-left: 20px;
      }
    </style>
	<div class="page-header">
    <h1>WebAuction<sup>Plus</sup></h1>
    </div>
';
  break;
}


return($output."\n\n\n");
?>

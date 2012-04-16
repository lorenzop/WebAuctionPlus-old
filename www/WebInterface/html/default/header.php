<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
global $html,$user;
$output='';


// page header
$output.=
  '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n".
  '<html xmlns="http://www.w3.org/1999/xhtml">'."\n".
  '<head>'."\n".
  '  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />'."\n".
  '  <title>WebAuction</title>'."\n".
  '  <link rel="icon" type="image/x-icon" href="images/favicon.ico" />'."\n".
  '  <style type="text/css" title="currentStyle">'."\n".
  '  </style>'."\n";
// css
$html->loadCss('main.css');
$html->loadCss('table_jui.css');
//$html->loadCss($uiPack.'/jquery-ui-1.8.18.custom.css');
//$html->loadCss('jquery-ui-1.8.16.custom.css');
//$html->loadCss($cssFile.'.css');
$output.='<style type="text/css">'."\n".
         "{css}\n".
         "</style>\n";
// finish header
$output.=
  '  <script type="text/javascript" language="javascript" src="js/jquery-1.7.2.min.js"></script>'."\n".
  '  <script type="text/javascript" language="javascript" src="js/jquery.dataTables-1.9.0.min.js"></script>'."\n".
  '  <script type="text/javascript" language="javascript" src="js/inputfunc.js"></script>'."\n".
  '  <script type="text/javascript" language="javascript" charset="utf-8">'."\n".
  '    $(document).ready(function() {'."\n".
  '      oTable = $(\'#mainTable\').dataTable({'."\n".
//  '        "bProcessing"     : true,'."\n".
  '        "bJQueryUI": true,'."\n".
//  '        "bStateSave"      : true,'."\n".
  '        "sPaginationType": "full_numbers"'."\n".
//  '        "sAjaxSource"     : "scripts/server_processing.php"'."\n".
  '      });'."\n".
  '    } );'."\n".
  '  </script>'."\n".
  '</head>'."\n";

switch($html->getPageFrame()){
case 'default':
  $output.=
    '<body>'."\n".
    '<div id="holder">'."\n".
    '<table border="0" cellspacing="0" cellpadding="0" id="profile-box">'."\n".
    "<tr>\n".
    '  <td rowspan="4"><img src="./?page=mcface&amp;username='.$user->getName().'" '.
         'alt="" width="64" height="64" id="mcface" /></td>'."\n".
    '  <td>Name:</td><td>'. $user->getName().
      ($user->hasPerms('isAdmin')?'&nbsp;<span style="font-size: small;"><b>[ADMIN]</b></span>':'').'</td>'."\n".
    "</tr>\n".
    '<tr><td>Money:</td><td>'.$user->Money.'</td></tr>'."\n".
    '<tr><td>Mail:</td><td>'. $user->numMail.'</td></tr>'."\n".
    '<tr><td colspan="2">'.date('jS M Y H:i:s').'</td></tr>'."\n".
    "</table>\n".
    '<div id="menu-box">'.
    '


<a href="./">Home</a><br />
<a href="./?page=myitems">My Items</a><br />
<a href="./?page=myauctions">My Auctions</a><br />
<a href="./?page=playerstats">Player Stats</a><br />
<a href="./?page=info">Item Info</a><br />
<a href="./?page=transactionLog">Transaction Log</a><br />
<a href="./?page=logout">Logout</a>


</div>'."\n".
    '<div id="title-box"><h1 style="margin-bottom: 30px;">WebAuction Plus</h1></div>'."\n".
    "\n\n\n";
  break;
case 'basic':
  $output.=
    '<body>'."\n".
    '<div id="holder">'."\n".
    '<h1 style="margin-bottom: 30px;">WebAuction Plus</h1>'."\n";
  break;
}


return($output);
?>

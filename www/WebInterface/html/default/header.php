<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
global $html;
$output='';


// page header
$output.=
  '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">'."\n".
  '<html>'."\n".
  '<head>'."\n".
  '  <meta http-equiv="content-type" content="text/html; charset=utf-8" />'."\n".
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
  '  <script type="text/javascript" charset="utf-8">'."\n".
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
    '<body bgcolor="#53568B">'."\n".
    '';
  break;
case 'basic':
  $output.=
    '<body bgcolor="#53568B">'."\n".
    '<div id="holder">'."\n".
    '<h1 style="margin-bottom: 30px;">WebAuction Plus</h1>'."\n";
  break;
}


return($output);
?>

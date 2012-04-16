<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
global $config,$html;
$output='';


$output.="</div>\n";
//switch($html->getPageFrame()){
//case 'default':
//case 'basic':
  $output.=
    '<div id="footer" class="clear" style="text-align:center; padding:10px">'."\n".
    '  <!-- Paste advert code here -->'."\n\n".
    '  <!---------------------------->'."\n".
    '  <p style="font-size: large; color: #FFFFFF;"><span style="background-color: #000000;">'."\n".
    '    &nbsp;<a href="http://dev.bukkit.org/server-mods/webauctionplus/"      target="_blank" style="color: #FFFFFF;"><u>WebAuctionPlus</u> '.$config['version'].'</a> By lorenzop&nbsp;<br />'."\n".
    '    &nbsp;Based on <a href="http://dev.bukkit.org/server-mods/webauction/" target="_blank" style="color: #FFFFFF;"><u>WebAuction</u></a> By Exote&nbsp;<br />'."\n".
    "  </span></p>\n".
    "</div>\n";
//  break;
//}
$output.=
  "</body>\n".
  "</html>\n";


return($output);
?>

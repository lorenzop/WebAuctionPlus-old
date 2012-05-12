<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
global $config,$html,$num_queries;
$output='';


//<div class="spacer"></div>
$output.="<br /><br /><br /><br /><br /><br /></div>\n";
//switch($html->getPageFrame()){
//case 'default':
//case 'basic':
  $output.='
<div id="footer" class="clear" style="text-align:center; padding:10px">
  <!-- Paste advert code here -->

  <!-- ====================== -->
  <p style="margin-bottom: 10px; font-size: large; color: #FFFFFF;"><span style="background-color: #000000;">&nbsp;'.
    '<a href="http://dev.bukkit.org/server-mods/webauctionplus/" '.
    'target="_blank" style="color: #FFFFFF;"><u>WebAuctionPlus</u> '.SettingsClass::getString('Version').'</a> By lorenzop&nbsp;<br />'.
    '<span style="font-size: medium;">&nbsp;Based on <a href="http://dev.bukkit.org/server-mods/webauction/" '.
    'target="_blank" style="color: #FFFFFF;"><u>WebAuction</u></a> By Exote&nbsp;</span></span></p>
  <p style="margin-bottom: 10px; font-size: smaller; color: #FFFFFF;"><span style="background-color: #000000;">'.
    '<b>&nbsp;Rendered page in '.GetRenderTime().' Seconds with '.((int)@$num_queries).' Queries&nbsp;</b></span></p>
  <p style="font-size: smaller; color: #FFFFFF;"><span style="background-color: #000000;">'.
    '<a href="http://validator.w3.org/#validate_by_input" target="_blank">'.
    '<img src="images/valid-xhtml10.png" alt="Valid XHTML 1.0 Transitional" width="88" height="31" /></a></span></p>
</div>
';
//  break;
//}
$output.='
</body>
</html>
';


return("\n\n\n".$output);
?>
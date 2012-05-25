<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
global $config,$html,$num_queries;
$output='';


//<div class="spacer"></div>
$output.="<br /><br /><br /><br /><br /><br /></div>\n";
//switch($html->getPageFrame()){
//case 'default':
//case 'basic':
  $output.='
<hr />
<div style="text-align:center;" class="container">

	   
	  <footer>
		<p>&copy; WebAuction<sup>Plus</sup> 2012
	  <h6 class="footer">WebAuction<sup>Plus</sup> 1.0.8 By lorenzop</h6><br /><h7 class="footer">Based on WebAuction By Exote</h7></p>
	  </footer>
	
	
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

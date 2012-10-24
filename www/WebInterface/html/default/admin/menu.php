<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}


return('
<table border="0" cellspacing="0" cellpadding="2" class="formtable" style="float: right; margin-right: 10px; width: 200px; background-color: #ccddff;">
<tr><td align="center" style="background-color: #eeeeaa; padding-top: 10px; padding-bottom: 10px; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #000000;"><font size="+1">Admin Menu</font></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td align="center"><a href="./?pagedir=admin&page=home">Dashboard</a></td></tr>
<tr><td align="center"><a href="./?pagedir=admin&page=settings">Settings</a></td></tr>
<tr><td align="center"><a href="./?pagedir=admin&page=accounts">Accounts</a></td></tr>
<tr><td align="center"><a href="./?pagedir=admin&page=itempacks">Item Packs</a></td></tr>
<tr><td>&nbsp;</td></tr>
</table>
');


?>
<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// settings page
$outputs=array();


//$outputs['css']='
//';


//{messages}
$outputs['body']=
RenderHTML::LoadHTML('admin/menu.php').'
<!-- stats -->
<table border="0" cellpadding="5" cellspacing="0" align="center" class="formtable" style="width: 500px; margin-bottom: 30px;">
<tr>
  <td width="50%"></td>
  <td width="50%"></td>
</tr>
<tr><td align="center" colspan="2"><font size="+2">Stats</font></td></tr>
<tr><td height="10"></td></tr>

<tr><td align="right">Total Auctions:</td><td>{total auctions}</td></tr>
<tr><td align="right">Total Buy-Nows:</td><td>{total buynows}</td></tr>
<tr><td align="right">Total Items For Sale:</td><td>{total items for sale}</td></tr>
<tr><td align="right">Total Accounts:</td><td>{total accounts}</td></tr>

<tr><td height="10"></td></tr>
</table>

<!-- troubleshooting -->
<table border="0" cellpadding="5" cellspacing="0" align="center" class="formtable" style="width: 250px; margin-bottom: 30px; float: right; margin-right: 20px;">
<tr><td align="center"><font size="+2">Troubleshooting</font></td></tr>

<tr><td><ul>
  <li><a href="./?page=mcskin&user=lorenzop&view=body&testing=1">Test mcskin.php</a><font size="-1"> - This should show a bunch of gibberish if working. Error messages are bad, but helpful to find the problem.</font></li>
</ul></td></tr>

<tr><td height="10"></td></tr>
</table>
';


return($outputs);
?>
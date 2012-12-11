<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// settings page
$outputs=array();


$outputs['css']='
.settingDesc{
  display: none;
  margin-bottom: 20px;
  padding: 5px;
  background-color: #FAFFBD;
  font-size: smaller;
  border-width:   1px;
  border-style:   solid;
  border-color:   #333333;
  border-radius:  6px;
  -moz-border-radius: 6px;
}
.settingDescLink{
  font-size: 65%;
}
';


//{messages}
$outputs['body top']=
RenderHTML::LoadHTML('admin/menu.php').'
<form action="./" method="post">
{token form}
<input type="hidden" name="pagedir" value="{pagedir}" />
<input type="hidden" name="page"    value="{page}"    />
<input type="hidden" name="action"  value="save"      />
<table border="0" cellpadding="5" cellspacing="0" align="center" class="formtable" style="width: 500px; margin-bottom: 30px;">
<tr>
  <td width="45%"></td>
  <td width="65%"></td>
</tr>
';


$outputs['body bottom']='
<tr><td height="10"></td></tr>
<tr><td align="center" colspan="2"><input type="submit" value="Save" class="input" /></td></tr>
<tr><td height="10"></td></tr>
</table>
</form>
';


// group title
$outputs['group']='
<tr><td align="center" colspan="2"><font size="+2">{title}</font></td></tr>
';


// text
$outputs['text']='
<tr>
  <td align="right">{has description}<a href="#" onclick="toggle(\'desc_{name}\'); return false;" class="settingDescLink"><font color="#0000ff">[ ? ]</font></a>&nbsp;{/has description}{title}:</td>
  <td><input type="text" name="{name}" value="{value}" /></td>
</tr>
{has description}
<tr><td colspan="2"><div id="desc_{name}" class="settingDesc">{description}</div></td></tr>
{/has description}
';
// checkbox
//  <td align="right">{has description}<a href="javascript:document.getElementById(\'desc_{name}\').style.display = \'block\';" class="settingDescLink"><font color="#0000ff">[ ? ]</font></a> {/has description}{title}:</td>
$outputs['checkbox']='
<tr>
  <td align="right">{has description}<a href="#" onclick="toggle(\'desc_{name}\'); return false;" class="settingDescLink"><font color="#0000ff">[ ? ]</font></a>&nbsp;{/has description}{title}:</td>
  <td align="left"><input type="checkbox" name="{name}" {value} /></td>
</tr>
{has description}
<tr><td colspan="2"><div id="desc_{name}" class="settingDesc">{description}</div></td></tr>
{/has description}
';
// int
$outputs['int']='
<tr>
  <td align="right">{has description}<a href="#" onclick="toggle(\'desc_{name}\'); return false;" class="settingDescLink"><font color="#0000ff">[ ? ]</font></a>&nbsp;{/has description}{title}:</td>
  <td><input type="text" name="{name}" value="{value}" /></td>
</tr>
{has description}
<tr><td colspan="2"><div id="desc_{name}" class="settingDesc">{description}</div></td></tr>
{/has description}
';
// double
$outputs['double']='
<tr>
  <td align="right">{has description}<a href="#" onclick="toggle(\'desc_{name}\'); return false;" class="settingDescLink"><font color="#0000ff">[ ? ]</font></a>&nbsp;{/has description}{title}:</td>
  <td><input type="text" name="{name}" value="{value}" /></td>
</tr>
{has description}
<tr><td colspan="2"><div id="desc_{name}" class="settingDesc">{description}</div></td></tr>
{/has description}
';
// listbox
$outputs['list']='
<tr>
  <td align="right">{has description}<a href="#" onclick="toggle(\'desc_{name}\'); return false;" class="settingDescLink"><font color="#0000ff">[ ? ]</font></a>&nbsp;{/has description}{title}:</td>
  <td>
    <select name="{name}" style="width: 150px;">
    {list rows}
    </select>
  </td>
</tr>
{has description}
<tr><td colspan="2"><div id="desc_{name}" class="settingDesc">{description}</div></td></tr>
{/has description}
';
$outputs['list row']='
<option value="{value}" {selected}>{value}</option>
';


//$outputs['error']='
//<h2 style="color: #ff0000; text-align: center;">{message}</h2>
//';
//$outputs['success']='
//<h2 style="color: #00ff00; text-align: center;">{message}</h2>
//';


return($outputs);
?>
<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// admin - settings
require('admin_common.php');
if(!defined('ADMIN_OK')){echo 'Permission Denied!'; exit();}


// save settings
if($config['action']=='save'){
  CSRF::ValidateToken();

  function SaveSettings(){
    function SaveSetting_string($name, $value){global $config;
      RunQuery("UPDATE `".$config['table prefix']."Settings` SET `value` = '".mysql_san($value)."' WHERE `name` = '".mysql_san($name)."' LIMIT 1", __file__, __line__);}
    function SaveSetting_boolean($name, $value){global $config;
      RunQuery("UPDATE `".$config['table prefix']."Settings` SET `value` = ".((int)toBoolean($value))." WHERE `name` = '".mysql_san($name)."' LIMIT 1", __file__, __line__);}
    function SaveSetting_integer($name, $value){global $config;
      RunQuery("UPDATE `".$config['table prefix']."Settings` SET `value` = ".((int)$value)." WHERE `name` = '".mysql_san($name)."' LIMIT 1", __file__, __line__);}
    function SaveSetting_double($name, $value){global $config;
      RunQuery("UPDATE `".$config['table prefix']."Settings` SET `value` = ".((float)$value)." WHERE `name` = '".mysql_san($name)."' LIMIT 1", __file__, __line__);}

    // website theme
    $theme = trim(getVar('Website_Theme', 'str'));
    SaveSetting_string('Website Theme', $theme);
    // jquery ui pack
    $jqueryuipack = trim(getVar('jQuery_UI_Pack', 'str'));
    if(in_array($jqueryuipack, array(
      'dark-hive',
      'dot.luv',
      'redmond',
      'start'
    )))
      SaveSetting_string('jQuery UI Pack', $jqueryuipack);

    // require login
    $requirelogin = getVar('Require_Login', 'bool');
    SaveSetting_boolean('Require Login', $requirelogin);
    // ez login
    $ezlogin = getVar('ez_Login', 'bool');
    SaveSetting_boolean('ez Login', $ezlogin);
    // csrf protection
    $csrfprotection = getVar('CSRF_Protection', 'bool');
    SaveSetting_boolean('CSRF Protection', $csrfprotection);

    // inventory rows
    $inventoryrows = getVar('Inventory_Rows', 'str');
    $inventoryrows = (int) substr($inventoryrows, 0, 1);
    $inventoryrows = MinMax($inventoryrows, 0, 6);
    SaveSetting_integer('Inventory Rows', $inventoryrows);
    // max sell price
    $maxsellprice = getVar('Max_Sell_Price', 'double');
    SaveSetting_double('Max Sell Price', $maxsellprice);
    // max selling per player
    $maxsellingperplayer = getVar('Max_Selling_Per_Player', 'int');
    SaveSetting_double('Max Selling Per Player', $maxsellingperplayer);
    //$output .= render_setting_row($outputs, 'Custom Description'     , 'checkbox',
    //$output .= render_setting_row($outputs, 'Item Packs'             , 'text',

    // language
    $language = trim(getVar('Language', 'str'));
    $language = trim(substr($language, 0, 2));
    if(in_array($language, array(
      'en',
      'de',
      'fr',
      'nl'
    )))
      SaveSetting_string('Language', $language);
    // currency prefix
    $currencyprefix = getVar('Currency_Prefix', 'str');
    SaveSetting_string('Currency Prefix', $currencyprefix);
    // currency postfix
    $currency_postfix = getVar('Currency_Postfix', 'str');
    SaveSetting_string('Currency Postfix', $currency_postfix);

  } SaveSettings();

    echo '<center><h2>Settings saved successfully!</h2><br /><a href="'.getLastPage().'">Back to last page</a></center>';
    ForwardTo(getLastPage(), 2);
    exit();
//  }
  echo $config['error']; exit();
}


function RenderPage_admin_settings(){global $config;
  // load page html
  $config['title'] = 'Settings';
  $outputs = RenderHTML::LoadHTML('pages/admin/settings.php');
  if(!empty($outputs['css']))
    $config['html']->AddCss($outputs['css']);
  $output = '';

  // assemble form
  function render_setting_row($outputs, $name, $type='text', $desc){global $config;
    $title = $name;
    // get setting value
    if(!isset($config['settings'][$name])) return('<p>Unknown setting: '.$name.'</p>');
    $value = $config['settings'][$name];
    if(is_array($value)) $value = $value['value'];
    // listbox field
    if(is_array($type)){
      $output = '';
      foreach($type as $v){
        $temp = $outputs['list row'];
        $temp = str_replace('{value}', $v, $temp);
        if($name == 'Inventory Rows')
          $temp = str_replace('{selected}', (substr($v,0,1)==$value ? 'selected' : ''), $temp);
        else if($name == 'Language')
          $temp = str_replace('{selected}', (substr($v,0,2)==$value ? 'selected' : ''), $temp);
        else
          $temp = str_replace('{selected}', ($v==$value?'selected':''), $temp);
        $output .= $temp;
      }
      $output = str_replace('{list rows}', $output, $outputs['list']);
    // checkbox field
    }else if($type == 'checkbox'){
      $output = $outputs['checkbox'];
      if($value == TRUE) $value = ' checked="checked" '; else $value = '';
    // int field
    }else if($type == 'int'){
      $output = $outputs['int'];
    // double field
    }else if($type == 'double'){
      $output = $outputs['double'];
    // text field
    }else{
      $output = $outputs['text'];
    }
    // replace tags
    $tags = array(
      'name'  => str_replace(' ', '_', $name),
      'value' => $value,
      'title' => $title
    );
    RenderHTML::Block($output, 'has description', !empty($desc));
    if(!empty($desc)) $tags['description'] = $desc;
    RenderHTML::RenderTags($output, $tags);
    return($output);
  }
  function render_setting_group($outputs, $title){
    $output = $outputs['group'];
    $output = str_replace('{title}', $title, $output);
    return($output);
  }

  // website
  $output .= render_setting_group($outputs, 'Website');
  $output .= render_setting_row($outputs, 'Website Theme'          , array(
      'default'),
    '<br /><br />Default: default');
  $output .= render_setting_row($outputs, 'jQuery UI Pack'         , array(
      'dark-hive',
      'dot.luv',
      'redmond',
      'start'),
    'The jQuery library is what handles creating the list tables for items or auctions. This setting changes the theme style for those tables.<br /><br />Default: redmond');

  // security
  $output .= render_setting_group($outputs, 'Security');
  $output .= render_setting_row($outputs, 'Require Login'          , 'checkbox',
    'If enabled, players will be forwarded to a login page and will be required to log in before they can continue. If disabled, players can view the Current Auctions page as a guest, but will still be required to log in to buy or sell.<br /><br />Default: off');
  $output .= render_setting_row($outputs, 'ez Login'               , 'checkbox',
    'If enabled, this will display a login form in the top left corner of the page if the player has not logged in. This does not affect anything if the above Require Login setting is enabled.<br /><br />Default: on');
  $output .= render_setting_row($outputs, 'CSRF Protection'        , 'checkbox',
    'When enabled, this protection will prevent hacking attempts where a player could have actions performed on their account without knowing.<br /><br />Default: on');

  // auctions
  $output .= render_setting_group($outputs, 'Auctions');
  $output .= render_setting_row($outputs, 'Inventory Rows'         , array(
      '1x9 (9 slots)',
      '2x9 (18 slots)',
      '3x9 (27 slots)',
      '4x9 (36 slots)',
      '5x9 (45 slots)',
      '6x9 (54 slots)'),
    'This sets the size of the mailbox (virtual chest) in game. It is safe to lower this value even if players have the larger previous chest size completely full. The plugin will hold onto the overflow until the player removes some of the items from the mailbox, then will show the extras the next time they open the mailbox again.<br /><br />Default: 6x9 (54 slots)');
  $output .= render_setting_row($outputs, 'Max Sell Price'         , 'double',
    '<br /><br />Default: ');
  $output .= render_setting_row($outputs, 'Max Selling Per Player' , 'int',
    '<br /><br />Default: ');
//  $output .= render_setting_row($outputs, 'Custom Description'     , 'checkbox',
//    '<br /><br />Default: ');
//  $output .= render_setting_row($outputs, 'Item Packs'             , 'text',
//    '<br /><br />Default: ');

  // localization
  $output .= render_setting_group($outputs, 'localization');
  $output .= render_setting_row($outputs, 'Language'               , array(
      'en (English)',
      'de ()',
      'fr (French)',
      'nl ()') ,
    'This language setting affects both this website and the in-game plugin.<br /><br />Default: en (English)');
  $output .= render_setting_row($outputs, 'Currency Prefix'        , 'text',
    '<br /><br />Default: $_');
  $output .= render_setting_row($outputs, 'Currency Postfix'       , 'text',
    '<br /><br />Default: _Cents');

return($outputs['body top'].
       $output.
       $outputs['body bottom']);
}


?>
<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
if(!is_array(@$tags)){$tags=array();}


// replace {tag} tags
function RenderTags(&$html){global $config,$tags;
  // loop until finished
  for($t=0;$t<10;$t++){$StillWorking=FALSE;
    // remove comments
    $html=preg_replace('/\{\*(.*?)\*\}/s','',$html);
//    // includes
//    if(strpos($html,'{include=')!==FALSE){$StillWorking=TRUE;
//      $html=preg_replace_callback("/\{include=(.*?)\}/s",array($this,'_includes'),$html);}
//    // modules
//    if(strpos($html,'{module=')!==FALSE){$StillWorking=TRUE;
//      $html=preg_replace_callback("/\{module=(.*?)\}/s",array($this,'_modules'),$html);}
    // done?
    if(!$StillWorking){break;}
  }
  // remove comments (again)
  $html=preg_replace('/\{\*(.*?)\*\}/s','',$html);
  // {if x}{endif}
  $html=preg_replace_callback('/\{if (.*?)\{endif\}/s',create_function('$matches','
    global $config;
    $match=explode(\'}\',substr($matches[0],4,-7),2);
    // not
    $not=FALSE; if(substr($match[0],0,1)==\'!\'){$not=TRUE; $match[0]=substr($match[0],1);}
    // framed
    if($match[0]==\'framed\'){
      $match[0]=$config[\'framed\'];
    // user logged in
    }elseif($match[0]==\'loggedin\'){
      $match[0]=($config[\'user\'][\'name\']!=\'\');
    }
    // not
    if($not){$match[0]=!(boolean)$match[0];
    }else{   $match[0]=(boolean)$match[0];}
    if($match[0]){return($match[1]);
    }else{        return(\'\');}
  '),$html);
  // paths
  foreach($config['paths'] as $v1=>$v2){
    $v2=str_replace('{theme}',$config['theme'],$v2);
    $html=str_replace('{path='.$v1.'}',$v2,$html);
  }
  // tags
  foreach($tags as $v1=>$v2){
    $html=str_replace('{'.$v1.'}',$v2,$html);}
}


// load a .css file
function loadCss($file){global $config,$paths,$outputs;
  $file=SanFilename($file);
  if(substr($file,-4)!='.css'){$file.='.css';}
  // default theme
  $file2=str_replace('{theme}',$config['theme'],$paths['theme']).$file;
  if(file_exists($file2)){
    $outputs['css'].="\n".file_get_contents($file2)."\n";
    return;
  }
  echo '<p>File not found: '.$file."</p>\n";
}


?>

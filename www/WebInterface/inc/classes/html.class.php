<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// this class handles html generation, templates, and tags
class RenderHTML{

protected $outputs = array();
protected $tags    = array();
protected $Frame   = '';
private   $tempHeader = '';


function __construct(&$outputs, &$tags){global $config;
  $this->outputs = &$outputs;
  $this->tags    = &$tags;
  if(!is_array(@$outputs) || count(@$outputs)==0)
    $outputs=array(
      'header'=>'',
      'css'   =>'',
      'body'  =>'',
      'footer'=>''
    );
  if(!is_array(@$tags)) $tags=array();
  $this->setPageFrame();
}

// display page
public function Display(){global $config,$lpaths;
  // render header/footer
  $this->outputs['header'] = include($this->getLocalThemePath().'header.php');
  $this->outputs['footer'] = include($this->getLocalThemePath().'footer.php');
  $this->outputs['header'] = str_replace('{AddToHeader}',$this->tempHeader,$this->outputs['header']);
  // insert css
  $this->outputs['header']=str_replace('{css}', "\n".$this->outputs['css']."\n", $this->outputs['header']);
  // render tags
  $this->tags['site title']     = $config['site title'];
  $this->tags['page title']     = $config['title'];
  $this->tags['sitepage title'] = $config['site title'].(empty($config['title'])?'':' - '.$config['title']);
  $this->RenderTags($this->outputs['header']);
  $this->RenderTags($this->outputs['body']);
  $this->RenderTags($this->outputs['footer']);
  // output page
  echo $this->outputs['header']."\n";
  echo $this->outputs['body']  ."\n";
  echo $this->outputs['footer']."\n";
}

// Frame = default | basic | none
public function setPageFrame($Frame='default'){
  $this->Frame=$Frame;
}
public function getPageFrame(){
  return($this->Frame);
}

// add to html header
public function addToHeader($text){
  $this->tempHeader.="\n".$text."\n";
}




// replace {tag} tags
function RenderTags(&$html){global $config,$tags;
//  // loop until finished
//  for($t=0;$t<10;$t++){$StillWorking=FALSE;
//    // remove comments
//    $html=preg_replace('/\{\*(.*?)\*\}/s','',$html);
//    // includes
//    if(strpos($html,'{include=')!==FALSE){$StillWorking=TRUE;
//      $html=preg_replace_callback("/\{include=(.*?)\}/s",array($this,'_includes'),$html);}
//    // modules
//    if(strpos($html,'{module=')!==FALSE){$StillWorking=TRUE;
//      $html=preg_replace_callback("/\{module=(.*?)\}/s",array($this,'_modules'),$html);}
//    // done?
//    if(!$StillWorking){break;}
//  }
  // remove comments (again)
  $html=preg_replace('/\{\*(.*?)\*\}/s','',$html);
//  // {if x}{endif}
//  $html=preg_replace_callback('/\{if (.*?)\{endif\}/s',create_function($matches,'
//    $match=explode('}',substr($matches[0],4,-7),2);
//    // not
//    $not=FALSE; if(substr($match[0],0,1)=='!'){$not=TRUE; $match[0]=substr($match[0],1);}
//    // framed
//    if($match[0]=='framed'){
//      $match[0]=$config['framed'];
//    // user logged in
//    }elseif($match[0]=='loggedin'){
//      $match[0]=($config['user'] != NULL);
//    }
//    // not
//    if($not){$match[0]=!(boolean)$match[0];
//    }else{   $match[0]= (boolean)$match[0];}
//    if($match[0]) return($match[1]);
//    else          return($match[2]);
  // paths
  foreach($config['paths'] as $paths){
    foreach($paths as $pathName=>$path){
      $path=str_replace('{theme}'             ,$config['theme'],$path);
      $html=str_replace('{path='.$pathName.'}',$path           ,$html);
    }
  }
  unset($pathName, $path);
  // tags
  foreach($tags as $tagName=>$tag){
    $html=str_replace('{'.$tagName.'}',$tag,$html);
  }
  unset($tagName, $tag);
}


public function getLocalThemePath($theme=''){global $config;
  if($theme=='') $theme=$config['theme'];
  return str_replace('{theme}', $config['theme'], $config['paths']['local']['theme']);
}



// load a .css file
public function loadCss($file){global $config,$paths;
//  $file=SanFilename($file);
  $output = '';
  if(substr($file,-4)!='.css'){$file.='.css';}
  // current theme
  if(file_exists(               $this->getLocalThemePath().$file)){
    $output = file_get_contents($this->getLocalThemePath().$file);
  // default theme
  }elseif(file_exists(          $this->getLocalThemePath('default').$file)){
    $output = file_get_contents($this->getLocalThemePath('default').$file);
  // website root
  }elseif(file_exists(          $file)){
    $output = file_get_contents($file);
  }
  if(empty($output)){echo '<p>File not found: '.$file."</p>\n"; return;}
  // remove comments
  $output=preg_replace('/\/\*(.*?)\*\//s','',$output);
  $this->outputs['css'] .= "\n".$output."\n";
}


}
?>
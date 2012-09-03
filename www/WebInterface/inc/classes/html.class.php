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
  $this->outputs['header'] = RenderHTML::LoadHTML('header.php');
  $this->outputs['footer'] = RenderHTML::LoadHTML('footer.php');
  $this->outputs['header'] = str_replace('{AddToHeader}',$this->tempHeader,$this->outputs['header']);
  // insert css
  $this->outputs['css'] = trim($this->outputs['css']);
  if(!empty($this->outputs['css']))
    $this->outputs['css'] = "\n".$this->outputs['css']."\n";
  $this->outputs['header'] = str_replace('{css}', $this->outputs['css'], $this->outputs['header']);
  // common tags
  $this->tags['site title']     = $config['site title'];
  $this->tags['page title']     = $config['title'];
  $this->tags['lastpage']       = getLastPage();
  $this->tags['sitepage title'] = $config['site title'].(empty($config['title'])?'':' - '.$config['title']);
  $this->tags['token']          = CSRF::getTokenURL();
  $this->tags['token form']     = CSRF::getTokenForm();
  // finish rendering page
  $output = $this->outputs['header']."\n".
            $this->outputs['body']  ."\n".
            $this->outputs['footer']."\n";
  RenderHTML::RenderTags($output, $this->tags);
  echo $output;
  unset($output, $this->outputs);
}


// block tags
public static function showBlock(&$html, $tag) {
  $html = str_replace('{'.$tag.'}', '', $html);
  $html = str_replace('{/'.$tag.'}','', $html);
}
public static function hideBlock(&$html, $tag) {
  $html = preg_replace('/\{'.$tag.'\}(.*?)\{\/'.$tag.'\}/s','',$html);
}
public static function Block(&$html, $tag, $show) {
  if($show) self::showBlock($html, $tag);
  else      self::hideBlock($html, $tag);
}


// Frame = default | basic | none
public function setPageFrame($Frame='default'){
  $this->Frame = $Frame;
}
public function getPageFrame(){
  return($this->Frame);
}
// add to html header
public function addToHeader($text){
  $this->tempHeader .= "\n".$text."\n";
}
// add tags
public function addTags($tags){
  if(!is_array($tags)) return;
  foreach($tags as $name => $value)
    $this->tags[$name] = $value;
}


// replace {tag} tags
public static function RenderTags(&$html, $tags=array()){global $config;
  if(is_array($html)) $html = '$'."html can't be an array!!!";
//  // include tags (loop until finished)
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
  // remove comments
  $html = preg_replace('/\{\*(.*?)\*\}/s','',$html);
  // {if x} {else} {endif}
  $html = preg_replace_callback('/\{if (.*?)\{endif\}/s',array('RenderHTML','ifCallback'),$html);
  // paths
  foreach($config['paths'] as $paths){
    foreach($paths as $pathName=>$path){
      $path = str_replace('{theme}'             , $config['theme'], $path);
      $html = str_replace('{path='.$pathName.'}', $path           , $html);
    }
  }
  unset($pathName, $path);
  // global tags
  if(!isset($tags['page'])) $tags['page'] = $config['page'];
  // replace tags
  $searches = array(); $replaces = array();
  foreach($tags as $search => $replace){
    $searches[] = '{'.$search.'}';
    $replaces[] = $replace;
  }
  $html = @str_replace(
    $searches,
    $replaces,
    $html);
  unset($tags, $searches, $replaces);
}
// {if x} tag callback function
protected static function ifCallback($matches){global $config;
  $match = explode('}', substr($matches[0],4,-7), 2);
  $match[0] = trim($match[0]);
  $not = FALSE;
  $value = NULL;
  if(substr($match[0],0,1) == '!'){
    $not = TRUE;
    $match[0] = substr($match[0],1);
  }
  // common variables
  // permissions
  if(substr($match[0],0,10) == 'permission'){
    $match[0] = substr($match[0],11,-1);
    $value = $config['user']->hasPerms($match[0]);
  }elseif($match[0] == 'logged in'){
    if($config['user'] == NULL) $value = FALSE;
    else $value = $config['user']->isOk();
  }
////    // framed
////    if($match[0]=='framed'){
////      $match[0]=$config['framed'];
////    // user logged in
////    }elseif($match[0]=='loggedin'){
////      $match[0]=($config['user'] != NULL);
////    }
  // unknown variable
  if($value === NULL){
    error_log('Unknown variable in if tag: '.$match[0]);
    return('Unable to process tag!');
  }
  // not !
  if($not) $value = !$value;
  // has {else}
  if(strpos($match[1],'{else}') !== FALSE){
    $match[1] = explode('{else}', $match[1]);
    if($value) return($match[1][0]);
    else       return($match[1][1]);
  // no {else}
  }else{
    if($value) return($match[1]);
  }
  return('');
}


public static function getLocalThemePath($theme=''){global $config;
  if(empty($theme)) $theme = $config['theme'];
  return(str_replace('{theme}', $theme, $config['paths']['local']['theme']));
}


// load html theme file
public static function LoadHTML($file){
  $output = '';
  if(substr($file,-4) != '.php') $file .='.php';
  // current theme
  if(file_exists(     RenderHTML::getLocalThemePath().$file))
    $output = include(RenderHTML::getLocalThemePath().$file);
  // default theme
  elseif(file_exists( RenderHTML::getLocalThemePath('default').$file))
    $output = include(RenderHTML::getLocalThemePath('default').$file);
  // website root
  elseif(file_exists( $file))
    $output = include($file);
  else{echo '<p>Failed to load html file: '.$file."</p>\n"; exit();}
  // remove comments
  if(is_array($output))
    foreach($output as $v1=>$v2)
      $output[$v1] = preg_replace('/\/\*(.*?)\*\//s','',$v2);
  else
    $output = preg_replace('/\/\*(.*?)\*\//s','',$output);
  return($output);
}


// load a .css file
public static function LoadCss($file){global $config,$paths;
//  $file=SanFilename($file);
  $output = '';
  if(substr($file,-4) != '.css') $file .= '.css';
  // current theme
  if(file_exists(               RenderHTML::getLocalThemePath().$file))
    $output = file_get_contents(RenderHTML::getLocalThemePath().$file);
  // default theme
  elseif(file_exists(           RenderHTML::getLocalThemePath('default').$file))
    $output = file_get_contents(RenderHTML::getLocalThemePath('default').$file);
  // website root
  elseif(file_exists(           $file))
    $output = file_get_contents($file);
  if(empty($output)){echo '<p>Failed to load css file: '.$file."</p>\n"; return;}
  // remove comments
  $output = preg_replace('/\/\*(.*?)\*\//s','',$output);
  $config['html']->outputs['css'] .= "\n".$output."\n";
}


}
?>
<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}


// this can be a home page with last 10 new auctions, notices, who's online...

include('auctions.php');
function RenderPage_home(){
return RenderPage_auctions();
}


?>
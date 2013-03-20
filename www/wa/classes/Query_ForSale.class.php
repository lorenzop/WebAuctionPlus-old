<?php namespace wa;
if(!defined('psm\INDEX_FILE') || \psm\INDEX_FILE!==TRUE) {if(headers_sent()) {echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}
	else {header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
global $ClassCount; $ClassCount++;
class Query_ForSale extends \psm\DataTables\Table {


	public function __construct() {
	}


//	// ajax requests
//	protected function ajax() {
//		header('Content-Type: text/plain');
//		$tables = new datatables();
//	}


}
?>

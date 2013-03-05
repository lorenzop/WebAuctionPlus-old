<?php namespace psm\ItemDefines;
if(!defined('psm\INDEX_FILE') || \psm\INDEX_FILE!==TRUE) {if(headers_sent()) {echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}
	else {header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
class DefinesLoader_default_crafting extends DefinesLoader {


	protected function LoadCategories() {
		$this->LoadCategory('Brewing');
		$this->LoadCategory('Building');
		$this->LoadCategory('Combat');
		$this->LoadCategory('Decoration');
		$this->LoadCategory('Foodstuffs');
		$this->LoadCategory('Materials');
		$this->LoadCategory('Misc');
		$this->LoadCategory('Redstone');
		$this->LoadCategory('Special');
		$this->LoadCategory('Transport');
	}


	protected function getPath() {
		return 'ItemPacks/default/';
	}
	protected function getType() {
		return 'crafting';
	}

}
?>
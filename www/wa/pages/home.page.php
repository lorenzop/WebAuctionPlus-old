<?php namespace wa\Pages;
if(!defined('psm\INDEX_FILE') || \psm\INDEX_FILE!==TRUE) {if(headers_sent()) {echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}
	else {header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
class page_home extends \psm\Portal\Page {

	const dbName = 'main';


	public function Render() {
		$headings = array(
			'Item',
			'Seller',
			'Expires',
			'Price (Each)',
			'Price (Total)',
			'Market Value',
			'Qty',
			'Buy',
		);
		$table = new \psm\Widgets\DataTables\Table(
			$headings,
			new home_Query(),
			FALSE
		);
		return 'HOME'.$table->Render();
	}


	protected function Action($action) {
	}


}
class home_Query extends \psm\Widgets\DataTables\Query {

	private $st = NULL;


	public function runQuery() {
		$db = \psm\dbPool\dbPool::getDB(page_home::dbName);
		$query = 'SELECT ';
		$params = array();
		$query .= "`sale_id`, `username`, `itemId`, `qty`, `itemTitle` FROM `WA_ForSale` LIMIT 3";
//		$params[':limit'] = 1;
		$db->
		$this->st = $pdo->prepare($query);
		$this->st->execute($params);
		return TRUE;
	}


	public function getRow() {
		$row = $this->st->fetch(\PDO::FETCH_ASSOC);
		if($row === FALSE) return NULL;
		return array(
			$row['itemTitle'],
			$row['playerName'],
			'expires',
			'price each',
			'price total',
			'market',
			$row['qty'],
			$row['id']
		);
	}


}
?>
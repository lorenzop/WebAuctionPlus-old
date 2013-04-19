<?php namespace wa\Pages;
global $ClassCount; $ClassCount++;
class page_home extends \psm\Portal\Page {

	const dbName = 'main';


	public function Render() {
		return 'home page';
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
		$table = \psm\Widgets\Widget_DataTables::factory(
			$headings,
			new home_Query(),
			FALSE
		);
		return $table->Render();
	}


	public function Action($action) {
	}


}
//class home_Query extends \psm\Widgets\DataTables\Query {
class home_Query {

	private $db = NULL;


	public function runQuery() {
		$this->db = \psm\pxdb\dbPool::getDB(page_home::dbName);
		$sql = 'SELECT ';
		$sql .= "`selling_id` AS `id`, `username`, `item`, `qty`, `cached_title` ".
			"FROM `wa_Selling` LIMIT 0, 2";
//echo $sql;
//		$params[':limit'] = 1;
		$this->db->Prepare($sql);
		$this->db->Exec();
//echo $this->db->getRowCount();
		return TRUE;
	}


	public function getRow() {
		if(!$this->db->hasNext())
			return NULL;
		return array(
			$this->db->getString('cached_title'),
			$this->db->getString('username'),
			'expires',
			'price each',
			'price total',
			'market',
			$this->db->getInt('qty'),
			$this->db->getInt('id')
		);
	}


}
?>
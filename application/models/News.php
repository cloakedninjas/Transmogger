<?php
class Model_News extends Model_Base {
	
	protected $_dbTableName = 'news';

	public function getHeadlines() {
		$db = Zend_Registry::get('db');
		return $db->fetchAll("SELECT * FROM news ORDER BY date DESC LIMIT 3");
	}

}	
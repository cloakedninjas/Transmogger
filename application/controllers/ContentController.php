<?php

class ContentController extends Zend_Controller_Action {

    public function rulesAction() {
    }

	public function newsAction() {
		$db = Zend_Registry::get('db');
		
		$this->view->news = $db->fetchAll("SELECT * FROM news ORDER BY date DESC");
		
    }
}
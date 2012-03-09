<?php

class ListsController extends Zend_Controller_Action {

	public function init() {
		$this->_db = Zend_Registry::get("db");

		$stmt = $this->_db->query('SELECT COUNT(id) AS count FROM loadouts');
		$result = $stmt->fetch();

		$paginator = Zend_Paginator::factory(intval($result->count));
		$paginator->setDefaultItemCountPerPage(20);
    	$paginator->setCurrentPageNumber($this->_getParam('page'));

    	$limit_start = ($paginator->getCurrentPageNumber() * $paginator->getItemCountPerPage()) - $paginator->getItemCountPerPage();

    	// determine ORDER BY

    	switch ($this->_getParam('action')) {
    		case 'newest' :
    			$orderby = 'created DESC';
    			break;
    		case 'rating' :
    			$orderby = 'rating_score DESC, rating_votes DESC';
    			break;
    		default :
    			$orderby = 'normalized DESC';
    			break;
    	}

    	$query = "
    	SELECT id, ref, label, created, rating_score, rating_votes, (rating_sum * rating_score) AS normalized
		FROM loadouts
		ORDER BY $orderby
		LIMIT $limit_start, " . $paginator->getItemCountPerPage();

		$this->view->rows = $this->_db->fetchAll($query);

		$this->view->paginator = $paginator;
    	$this->view->index_start = $limit_start;
    	$this->view->sort = $this->_getParam('action');
	}

    public function popularAction() {
    	if ($this->_getParam('slide')) {
    		$this->_helper->layout->disableLayout();
    		$this->render('partial');
    	}
    	else {
	    	$this->render('index');
    	}
    }

	public function newestAction() {
		if ($this->_getParam('slide')) {
    		$this->_helper->layout->disableLayout();
    		$this->render('partial');
    	}
    	else {
	    	$this->render('index');
    	}
	}

	public function ratingAction() {
		if ($this->_getParam('slide')) {
    		$this->_helper->layout->disableLayout();
    		$this->render('partial');
    	}
    	else {
	    	$this->render('index');
    	}
	}
}
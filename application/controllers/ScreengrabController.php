<?php

class ScreengrabController extends Zend_Controller_Action {

    public function init() {
    	//$this->_helper->layout->disableLayout();
    	//$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout()->setLayout('screengrab');
    }

    public function indexAction() {
    	$config = Zend_Registry::get('config');
    	$loadout = Model_Loadout::loadViaRef($this->_getParam('ref'));
    	$items = $loadout->generateItemArray();

		$this->view->swf_url = $config->app->swf_url;
		$this->view->width = 275;
		$this->view->height = 345;

		$this->view->flash_vars = "model=" . $loadout->race . $loadout->gender . "&modelType=16&mode=3&sk=0&ha=0&hc=0&fa=0&fh=0&fc=0&mode=3&contentPath=" . $config->app->content_url . "&equipList=";



		foreach($items as $i) {
			if (isset($i['id']) && $i['id'] != 0) {
				$this->view->flash_vars .= $i['slot'] . "," . $i['display_id'] . ",";
			}
		}

		$this->view->flash_vars = substr($this->view->flash_vars, 0, -1);
    }
}

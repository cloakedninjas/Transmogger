<?php

class IndexController extends Zend_Controller_Action {

    public function indexAction() {

		$config = Zend_Registry::get('config');
		$this->view->http_url = $config->app->url;

		$this->view->swf_url = $config->app->swf_url;
		$this->view->swf_content_path = $config->app->content_url;

		$this->view->race = "human";
		$this->view->gender = "male";

		$this->view->model_type = 16;

		$this->view->item_array = Model_Loadout::generateDefaultItemArray();
		$this->view->render = false;
		$this->view->loadout = new Model_Loadout();

		//defaults
		$this->view->loadout->race = 'bloodelf';
		$this->view->loadout->gender = 'female';

		if ($this->_getParam('ref') !== null) {
			$loadout = Model_Loadout::loadViaRef($this->_getParam('ref'));

			if ($loadout !== false) {
				$loadout->views++;
				$loadout->save();

				$this->view->loadout = $loadout;
				$this->view->item_array = $loadout->generateItemArray();
				$this->view->render = true;
			}
			else {
				$this->view->item_array = Model_Loadout::generateDefaultItemArray();
				$this->view->render = false;
			}
		}

		$db = Zend_Registry::get('db');

		$query = 'SELECT id FROM items
		WHERE name_de IS NULL
		ORDER BY display_id DESC
		LIMIT 20';

		$rows = $db->fetchAll($query);

		$this->view->locale_lookups = array();
		$this->view->locale_lang = "de_DE";

		foreach ($rows as $r) {
			$this->view->locale_lookups[] = $r->id;
		}

		$this->view->locale_lookups = json_encode($this->view->locale_lookups);
    }
}
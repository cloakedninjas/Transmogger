<?php

class AjaxController extends Zend_Controller_Action {

    public function init() {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    }

    public function lookupItemAction() {
    	$item_id = intval($this->_getParam('item'));

    	if ($item_id > 0) {
    		// do we have cache?

    		$item = new Model_Item($item_id);

    		$return = array("display_id"=>0);

    		if ($item->id == 0) {
	    		// create entry

    			$item->create($item_id);

	    		$item->getDisplayId();
	    		$item->save();
    		}
    		elseif ($item->display_id == 0) {
				$item->getDisplayId();
	    		$item->save();
    		}

    		$return['display_id'] = $item->display_id;

    		echo json_encode($return);
    	}
    }

    public function saveLoadoutAction() {
    	$return = array('status'=>0);

    	if ($this->_getParam('items') !== null) {
			if (count($this->_getParam('items')) > 0) {
				$loadout = new Model_Loadout();


				foreach ($this->_getParam('items') as $item) {
					$loadout->addItem($item);
				}

				$loadout->race = $this->_getParam('race');
				$loadout->gender = $this->_getParam('gender');
				$loadout->label = trim(ucfirst($this->_getParam('label')));

				if (get_magic_quotes_gpc()) {
					$loadout->label = stripslashes($loadout->label);
				}
				$loadout->label = substr($loadout->label, 0, 32);
				$loadout->create();

				$return['status'] = 1;
				$return['ref'] = $loadout->ref;
			}
			else {
				$return['error'] = 'Missing items';
			}
    	}
    	else {
    		$return['error'] = 'Missing items';
    	}

		echo json_encode($return);
    }

    public function searchItemAction() {
		$search = $this->_getParam('item');
		$search = str_replace('%', '', $search);
		$search .= '%';

		$search2 = '% ' . $search;

		$slot = $this->_getParam('slot');
		$inv_id = Model_Item::slotToInv($slot);

		$restrict = intval($this->_getParam('restrict'));
		
		$lang = $this->_getParam('lang');
		
		if ($lang === null) {
			$lang = 'en';
		}
		
		switch ($lang) {
			case 'de':
				$name_col = 'name_de';
				break;
				
			default:
				$name_col = 'name';
				break;
		}
		
		$inv_filter = '';

		if (is_array($inv_id)) {
			$inv_filter = '(`inv_id` = ' . implode(' OR `inv_id` = ', $inv_id) . ')';
		}
		else {
			$inv_filter = "`inv_id` = $inv_id";
		}

		$db = Zend_Registry::get('db');
		$query = '
		SELECT id, ' . $name_col . ' AS name, icon, quality, i_level, display_id
		FROM items
		WHERE ' . $inv_filter . '
		AND (' . $name_col . ' LIKE ' . $db->quote($search) . ' OR ' . $name_col . ' LIKE ' . $db->quote($search2) . ') ';

		if ($restrict != 0 &&
			$slot != Model_Item::SLOT_MAIN_HAND &&
			$slot != Model_Item::SLOT_OFF_HAND &&
			$slot != Model_Item::SLOT_RANGED &&
			$slot != Model_Item::SLOT_BACK &&
			$slot != Model_Item::SLOT_SHIRT) {
			$query .= "AND sub_type = $restrict ";
		}

		$query .= '
		AND status = 1
		AND (';

		if ($slot == Model_Item::SLOT_SHIRT) {
			$query .= '
			quality = ' . Model_Item::QUALITY_COMMON . ' OR';
		}

		$query .= '
			quality = ' . Model_Item::QUALITY_UNCOMMON . ' OR
			quality = ' . Model_Item::QUALITY_RARE . ' OR
			quality = ' . Model_Item::QUALITY_EPIC . ' OR
			quality = ' . Model_Item::QUALITY_HEIRLOOM . '
		)
		ORDER BY name
		LIMIT 15';

		//echo $query;
		//exit;

    	$rows = $db->fetchAll($query);
    	//var_dump($rows);

    	echo json_encode($rows);
    }

    public function voteAction() {
    	$db = Zend_Registry::get("db");

    	$result = $db->fetchRow("SELECT id FROM loadouts WHERE ref = " . $db->quote($this->_getParam('ref')));

    	if ($result === false) {
    		echo 0;
    		exit;
    	}

    	$loadout = new Model_Loadout($result->id);
    	$loadout->addRating($this->_getParam('score'));
    	$loadout->save();

    	echo $loadout->rating_score;
    }

    public function lookupLocaleAction() {
    	$db = Zend_Registry::get("db");

    	$return = array('next'=>0);

    	switch ($this->_getParam("lang")) {
    		case "de_DE" :
    			$col = 'de';
    			break;

    		default:
    			$col = null;
    	}

    	if ($col !== null) {
    		$id = intval($this->_getParam('id'));
    		$failed = $this->_getParam('failed');

    		if ($failed === null) {
	    		$val = $db->quote($this->_getParam('name'));

		    	$query = "UPDATE items
		    	SET name_$col = $val
		    	WHERE id = $id";

		    	$result = $db->query($query);

		    	if ($result !== false) {
		    		$query = 'SELECT id FROM items
					WHERE name_de IS NULL
					AND status = 1
					AND locale_skip IS NULL
					ORDER BY display_id DESC
					LIMIT 1';

					$rows = $db->fetchAll($query);

					if (count($rows) == 1) {
						$return['next'] = $rows[0]->id;
					}
		    	}
    		}
    		else {
    			$query = "UPDATE items
		    	SET locale_skip = 2
		    	WHERE id = $id";

		    	$db->query($query);

    			$query = 'SELECT id FROM items
				WHERE name_de IS NULL
				AND status = 1
				AND locale_skip IS NULL
				ORDER BY display_id DESC
				LIMIT 1';

				$rows = $db->fetchAll($query);

				if (count($rows) == 1) {
					$return['next'] = $rows[0]->id;
				}
    		}
	    }

	    echo json_encode($return);
    }
}
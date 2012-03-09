<?php
class Model_Loadout extends Model_Base {

	protected $_dbTableName = 'loadouts';

	public function addItem($item) {
		$this->items .= intval($item['slot']) . ':' . intval($item['id']) . ',';
	}

	public function create() {
		$this->items = substr($this->items, 0, -1);
		$this->created = $this->now();
		$this->ref = $this->createRef();

		parent::save();
	}

	protected function createRef() {
		return uniqid();
	}

	public function generateItemArray() {
		$array = self::generateDefaultItemArray();

		$items = explode(',', $this->items);

		//var_dump($items);
		//exit;

		foreach ($items as $item) {
			$parts = explode(':', $item);

			$item = new Model_Item($parts[1]);

			foreach ($array as $i=>$arr) {
				if ($arr['slot'] == $parts[0]) {
					$array[$i]['id'] = $item->id;
					$array[$i]['name'] = $item->name;
					$array[$i]['quality'] = $item->quality;
					$array[$i]['icon'] = $item->icon;
					$array[$i]['display_id'] = $item->display_id;
					break;
				}
			}
		}
		return $array;
	}

	public static function loadViaRef($ref) {
		$db = Zend_Registry::get('db');
		$row = $db->fetchRow("SELECT * FROM loadouts WHERE ref = " . $db->quote($ref));

		if ($row !== false) {
			$object = new self;
			$object->bindFromRow($row);

			return $object;
		}
		return false;
	}

	public static function generateDefaultItemArray() {
		$array = array(
			array('slot'=>Model_Item::SLOT_HEAD, 'id'=>0, 'display_id'=>0),
			array('slot'=>Model_Item::SLOT_SHOULDERS, 'id'=>0, 'display_id'=>0),
			array('slot'=>Model_Item::SLOT_BACK, 'id'=>0, 'display_id'=>0),
			array('slot'=>Model_Item::SLOT_CHEST, 'id'=>0, 'display_id'=>0),
			array('slot'=>Model_Item::SLOT_SHIRT, 'id'=>0, 'display_id'=>0),
			array('slot'=>Model_Item::SLOT_TABARD, 'id'=>0, 'display_id'=>0),
			array('slot'=>Model_Item::SLOT_WRIST, 'id'=>0, 'display_id'=>0),

			array('slot'=>Model_Item::SLOT_HAND, 'id'=>0, 'display_id'=>0),
			array('slot'=>Model_Item::SLOT_WAIST, 'id'=>0, 'display_id'=>0),
			array('slot'=>Model_Item::SLOT_LEGS, 'id'=>0, 'display_id'=>0),
			array('slot'=>Model_Item::SLOT_FEET, 'id'=>0, 'display_id'=>0),

			array('slot'=>Model_Item::SLOT_MAIN_HAND, 'id'=>0, 'display_id'=>0),
			array('slot'=>Model_Item::SLOT_OFF_HAND, 'id'=>0, 'display_id'=>0),
			array('slot'=>Model_Item::SLOT_RANGED, 'id'=>0, 'display_id'=>0),
		);

		return $array;
	}

	public function addRating($score) {
		$votes = (isset($_COOKIES["votes"])) ? json_decode($_COOKIES["votes"]) : array();

		if (in_array($this->ref, $votes)) {
			return false;
		}

		$this->rating_sum += $score;
		$this->rating_votes++;
		$this->rating_score = $this->rating_sum / $this->rating_votes;


		$votes[] = $this->ref;

		setcookie("votes", json_encode($votes), strtotime("+1year"), '/');
		return true;
	}
}
<?php
class Model_Item extends Model_Base {

	protected $_dbTableName = 'items';

	const INV_HEAD = 1;
	const INV_SHOULDERS = 3;
	const INV_BACK = 16;

	const INV_CHEST = 5;
	const INV_ROBES = 20;

	const INV_SHIRT = 4;
	const INV_TABARD = 19;
	const INV_WRIST = 9;

	const INV_HAND = 10;
	const INV_WAIST = 6;
	const INV_LEGS = 7;
	const INV_FEET = 8;

	const INV_MAIN_HAND = 21;
	const INV_ONE_HAND = 13;
	const INV_TWO_HAND = 17;
	const INV_OFF_HAND = 22;
	const INV_HELD_IN_OFF_HAND = 23;
	const INV_SHIELD = 14;

	const INV_RANGED = 15;
	const INV_THROWN = 25;
	const INV_RANGED_RIGHT = 26;

	const TYPE_ARMOUR = 4;
	const TYPE_WEAPON = 2;

	const SUB_TYPE_CLOTH = 1;
	const SUB_TYPE_LEATHER = 2;
	const SUB_TYPE_MAIL = 3;
	const SUB_TYPE_PLATE = 4;

	const SLOT_HEAD = 1;
	const SLOT_SHOULDERS = 3;
	const SLOT_BACK = 16;
	const SLOT_CHEST = 5;
	const SLOT_SHIRT = 4;
	const SLOT_TABARD = 19;
	const SLOT_WRIST = 9;

	const SLOT_HAND = 10;
	const SLOT_WAIST = 6;
	const SLOT_LEGS = 7;
	const SLOT_FEET = 8;

	const SLOT_MAIN_HAND = 21;
	const SLOT_OFF_HAND = 13;
	const SLOT_RANGED = 15;

	const QUALITY_POOR = 0;
	const QUALITY_COMMON = 1;
	const QUALITY_UNCOMMON = 2;
	const QUALITY_RARE = 3;
	const QUALITY_EPIC = 4;
	const QUALITY_LEGENDARY = 5;
	const QUALITY_HEIRLOOM = 7;

	public static function slotToInv($slot) {

		switch ($slot) {
			case self::SLOT_HEAD:
				return self::INV_HEAD;

			case self::SLOT_SHOULDERS:
				return self::INV_SHOULDERS;

			case self::SLOT_BACK:
				return self::INV_BACK;

			case self::SLOT_CHEST:
				return array(self::INV_CHEST, self::INV_ROBES);

			case self::SLOT_SHIRT:
				return self::INV_SHIRT;

			case self::SLOT_WRIST:
				return self::INV_WRIST;


			case self::SLOT_HAND:
				return self::INV_HAND;

			case self::SLOT_WAIST:
				return self::INV_WAIST;

			case self::SLOT_LEGS:
				return self::INV_LEGS;

			case self::SLOT_FEET:
				return self::INV_FEET;
		}

		if ($slot == self::SLOT_MAIN_HAND) {
			return array(self::INV_MAIN_HAND, self::INV_ONE_HAND, self::INV_TWO_HAND);
		}
		elseif($slot == self::SLOT_OFF_HAND) {
			return array(self::INV_OFF_HAND, self::INV_ONE_HAND, self::INV_TWO_HAND, self::INV_SHIELD, self::INV_HELD_IN_OFF_HAND);
		}
		elseif($slot == self::SLOT_RANGED) {
			return array(self::INV_RANGED, self::INV_THROWN, self::INV_RANGED_RIGHT);
		}

	}

	public function createViaFile($json) {

		if ($json) {
			if (!isset($json->itemClass) || ($json->itemClass != self::TYPE_ARMOUR && $json->itemClass != self::TYPE_WEAPON)) {
				return true;
			}

			$this->id = $json->id;
			$this->name = $json->name;
			$this->icon = $json->icon;
			$this->i_level = $json->itemLevel;
			$this->quality = $json->quality;
			$this->inv_id = $json->inventoryType;
			$this->type = $json->itemClass;
			$this->sub_type = $json->itemSubClass;
			$this->status = 1;

			$this->save(true);
			return true;
		}
	}

	public function createViaWowhead($id) {
		$url = "ptr.wowhead.com/item=$id&xml";

		$curl = curl_init();
    	curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

		$data = curl_exec($curl);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if ($http_code != 200) {
			return false;
		}

		$xml = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);

		$json = "{" . $xml->item->json . "}";
		$json = json_decode($json);


		if ($json) {
			if (!isset($json->classs) || ($json->classs != 2 && $json->classs != 4)) {
				return true;
			}

			$this->id = $id;
			$this->name = substr($json->name, 1); //trim off the digit
			$this->icon = strtolower(strval($xml->item->icon));
			$this->i_level = $json->level;
			$this->quality = intval($xml->item->quality['id']);
			$this->inv_id = intval($xml->item->inventorySlot['id']);
			$this->type = $json->classs;
			$this->sub_type = $json->subclass;
			$this->display_id = $json->displayid;
			$this->status = 1;

			//var_dump($this->name);
			$this->save(true);
			return true;
			//exit;
		}
	}

	public function create($id) {
		$url = "http://eu.battle.net/api/wow/item/$id";
        //echo $url . "<br />";

		$curl = curl_init();
    	curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

		$data = curl_exec($curl);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if ($http_code != 200) {
			return false;
		}

		//$json = file_get_contents($url);

		$json = json_decode($data);

		if ($json) {

			if ($json->itemClass != 2 && $json->itemClass != 4) {
				return true;
			}

			$this->id = $json->id;
			$this->name = $json->name;
			$this->icon = $json->icon;
			$this->i_level = $json->itemLevel;
			$this->quality = $json->quality;
			$this->inv_id = $json->inventoryType;
			$this->type = $json->itemClass;
			$this->sub_type = $json->itemSubClass;


			$this->save(true);
			return true;
		}
		return false;
	}

	public function getDisplayId() {
		$url = "http://www.wowhead.com/item=$this->id?xml";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'transmogger.info');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $xml = curl_exec($ch);

    	preg_match("/displayId=\"(\d+)\"/", $xml, $matches);

    	if (is_array($matches) && isset($matches[1])) {
    		$this->display_id = intval($matches[1]);
    	}
	}

}
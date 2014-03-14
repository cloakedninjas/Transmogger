<?php
class Model_ItemFetch extends Model_Base {

	protected $_dbTableName = 'item_fetch';

	public function create($id, $locale) {
		$url = "http://eu.battle.net/api/wow/item/$id";

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

            $this->id = $json->id;
            $this->locale = $locale;

            // only save details if it's an item we care about
			if ($json->itemClass == 2 || $json->itemClass == 4) {
                $this->name = $json->name;
                $this->icon = $json->icon;
                $this->i_level = $json->itemLevel;
                $this->quality = $json->quality;
                $this->inv_id = $json->inventoryType;
                $this->type = $json->itemClass;
                $this->sub_type = $json->itemSubClass;
			}

            $this->save(true);

			return true;
		}
		return false;
	}
}
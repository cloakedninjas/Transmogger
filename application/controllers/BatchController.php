<?php

class BatchController extends Zend_Controller_Action {

    public function init() {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    }

    public function screengrabAction() {
		$file = 'D:/www/transmogger/cache/test4.png';

		if (!file_exists($file)) {
		    die("no file");
		}

		$img = imagecreatefrompng($file);

		// src image size
		$img_width  = imagesx($img);
		$img_height = imagesy($img);

		// New image size
		$width  = 214;
		$height = 354;

		// Starting point of crop
		$tlx = 75;
		$tly = 179;

		$im = imagecreatetruecolor($width, $height);
		imagecopy($im, $img, 0, 0, $tlx, $tly, $width, $height);

		// cover the arrows
		$bg = imagecolorallocate($im, 13, 51, 79);
		imagefilledrectangle($im, 189, 311, 212, 332, $bg);

		// add text
		$colour = imagecolorallocate($im, 242, 242, 242);
		imagettftext($im, 12, 0, 50, 342, $colour, 'arial.ttf', 'http://transmogger.info');

		header('Content-Type: image/png');

		imagepng($im);
		imagedestroy($im);
    }

    public function itemSyncAction() {
    	exit;
    	set_time_limit(0);
    	$db = Zend_Registry::get('db');

    	// get last item ID
    	//$last = $db->fetchRow("SELECT MAX(id) AS id FROM items");
    	//$last = $last->id;
    	$last = 71798;

    	$finished = false;
    	$i = 1;
    	$fail_count = 10;

    	while (!$finished) {
    		$id = $i + $last;

    		file_put_contents("C:/www2/transmogger/http.log", "trying $id\n", FILE_APPEND);

    		$item = new Model_Item();
			$return = $item->create($id);

			file_put_contents("C:/www2/transmogger/http.log", "$id was $return\n", FILE_APPEND);

			if ($return == false) {
				$fail_count--;
			}
			else {
				$fail_count = 10;
			}

			if ($fail_count == 0) {
				$finished = true;
				file_put_contents("C:/www2/transmogger/http.log", "stopping at $id\n", FILE_APPEND);
				break;
			}

			$i++;
			usleep(2000);
    	}
    }
}
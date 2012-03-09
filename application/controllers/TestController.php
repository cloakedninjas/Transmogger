<?php

class TestController extends Zend_Controller_Action {

    public function indexAction() {
    	$char = new stdClass();
		$char->achievements_by_day = array(
			1000000 => '6,7,8',
			1004000 => '25'
		);


    	echo 1;
    	exit;
   }
}
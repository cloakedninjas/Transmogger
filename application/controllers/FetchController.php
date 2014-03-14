<?php

class FetchController extends Zend_Controller_Action {

    protected $absMaxId = 102340; // once this is met, do not run a request

    public function init() {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    }

    public function getNewJobAction() {
        $locale = 'en_GB';

        // get last item ID
        $db = Zend_Registry::get("db");
        $last = $db->fetchRow("SELECT MAX(id) AS id FROM item_fetch WHERE locale = '" . $locale . "'");

        if ($last->id < $this->absMaxId) {
            $id = intval($last->id) + 1;
            echo "{\"id\": $id, \"locale\": \"$locale\"}";
        }
    }

    public function postResponseAction() {
        $failed = $this->_getParam('failed');
        $id = intval($this->_getParam('id'));
        $locale = $this->_getParam("lang");

        if ($failed) {
            // no item but record it so we dont fetch it again
            $item = new Model_ItemFetch();
            $item->id = $id;
            $item->locale = $locale;
            $item->save(true);
        }
        else {
            $item = new Model_ItemFetch();
            $status = $item->create($id, $locale);
        }

        $this->getNewJobAction();
    }
}
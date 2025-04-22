<?php

$store_id = $this->getRequest()->getParam('store_id');

$QStore = new Application_Model_Store();
$QDistributor = new Application_Model_Distributor();
$QSubarea = new Application_Model_SubArea();
$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$where = $QStore->getAdapter()->quoteInto('id =?',$store_id);
$store = $QStore->fetchRow($where);

$this->view->store = $store;
$this->view->distributor_cache = $QDistributor->get_cache();
$this->view->subarea_cache = $QSubarea->get_cache();

if($store['lat'] == '' && $store['lng'] == ''){
	$this->_helper->viewRenderer->setRender('/store-visit/set-location');
}else{
	if($userStorage->group_id == ADMINISTRATOR_ID) {
		$this->_helper->viewRenderer->setRender('/store-visit/store-visit-check-in');
	}else{
		$this->_redirect(HOST.'report/store-visit');
	}
}

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

?>
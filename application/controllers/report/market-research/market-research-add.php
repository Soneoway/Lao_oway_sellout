<?php

$store_id = $this->getRequest()->getParam('store_id');

$QStore = new Application_Model_Store();
$QDistributor = new Application_Model_Distributor();
$QSubarea = new Application_Model_SubArea();

$where = $QStore->getAdapter()->quoteInto('id =?',$store_id);
$store = $QStore->fetchRow($where);

$this->view->store = $store;
$this->view->distributor_cache = $QDistributor->get_cache();
$this->view->subarea_cache = $QSubarea->get_cache();

$this->_helper->viewRenderer->setRender('/market-research/market-research-add');

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

?>
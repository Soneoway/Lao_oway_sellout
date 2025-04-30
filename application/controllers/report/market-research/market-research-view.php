<?php
$store_id = $this->getRequest()->getParam('store_code');

$QMkr = new Application_Model_MarketResearch();
$QDistributor = new Application_Model_Distributor();
$QSubarea = new Application_Model_SubArea();
$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$where = $QMkr->getAdapter()->quoteInto('store_code =?',$store_id);
$store = $QMkr->fetchRow($where);

$this->view->store = $store;
$this->view->distributor_cache = $QDistributor->get_cache();
$this->view->subarea_cache = $QSubarea->get_cache();

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

$this->_helper->viewRenderer->setRender('/market-research/market-research-view');
?>
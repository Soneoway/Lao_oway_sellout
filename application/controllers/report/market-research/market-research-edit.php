<?php
$store_id = $this->getRequest()->getParam('store_code');

$QMkr = new Application_Model_MarketResearch();
$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$where = $QMkr->getAdapter()->quoteInto('store_code =?',$store_id);
$store = $QMkr->fetchRow($where);

$this->view->store = $store;

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

$this->_helper->viewRenderer->setRender('/market-research/market-research-edit');
?>
<?php

$store_id = $this->getRequest()->getParam('store_id');

$params = array(
    'id'        => $store_id
);
$QStore = new Application_Model_Store();
$stores = $QStore->fetchStoreData($params);
$this->view->stores = $stores;

$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setRender('store/partials/store-log-view');

?>
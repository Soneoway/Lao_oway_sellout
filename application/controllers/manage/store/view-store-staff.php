<?php
$email = $this->getRequest()->getParam('email');

$QStoreStaffLog = new Application_Model_StoreStaffLog();

$params = array('email'=>$email);

if ($email){
    $total = $limit = 0;
    $this->view->histories = $QStoreStaffLog->fetchPagination(1, $limit, $total, $params);

    $QStore = new Application_Model_Store();
    $this->view->stores_cached = $QStore->get_cache();

}

$this->view->params = $params;

$this->_helper->viewRenderer->setRender('store/view-store-staff');
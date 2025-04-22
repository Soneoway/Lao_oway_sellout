<?php

$store_control_id = $this->getRequest()->getParam('store_control_id');

$params = array(
    'store_control_id' => $store_control_id
);


$QStoreControlLog = new Application_Model_StoreControlLog();
$sc_log = $QStoreControlLog->short_report_store_control_log($params);
$this->view->sc_log = $sc_log;

$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setRender('store-control/partials/store-control-short-report');

?>
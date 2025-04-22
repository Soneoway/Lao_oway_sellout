<?php

$store_id = $this->getRequest()->getParam('store_id');

$params = array(
    'store_id'        => $store_id
);


$QStoreStaffLog = new Application_Model_StoreStaffLog();
$ss_log = $QStoreStaffLog->short_report_storestafflog($params);
$this->view->ss_log = $ss_log;

$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setRender('store/partials/storestaff_log_short_report');

?>
<?php
$store_control_id = $this->getRequest()->getParam('id');

$QStoreControl = new Application_Model_StoreControl();
$QStoreControlLog = new Application_Model_StoreControlLog();

$Qscm = new Application_Model_StoreControlMap();
$QscmLog = new Application_Model_StoreControlMapLog();

// Remove Store Control Map Log
$where = $QscmLog->getAdapter()->quoteInto('store_control_id = ?', $store_control_id);
$QscmLog->delete($where);

// Remove Store Control Map 
$where = $Qscm->getAdapter()->quoteInto('store_control_id = ?', $store_control_id);
$Qscm->delete($where);

// Remove Store Control Log
$where = $QStoreControlLog->getAdapter()->quoteInto('store_control_id = ?', $store_control_id);
$QStoreControlLog->delete($where);

// Remove Store Control  
$where = $QStoreControl->getAdapter()->quoteInto('id = ?', $store_control_id);
$QStoreControl->delete($where);

$this->_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : HOST.'manage/store-control');
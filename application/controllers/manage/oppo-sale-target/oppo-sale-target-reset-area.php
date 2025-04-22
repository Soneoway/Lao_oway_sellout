<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$area_id = $this->getRequest()->getParam('area_id');
$area_name = $this->getRequest()->getParam('area_name');
$from = $this->getRequest()->getParam('from_date');
$to = $this->getRequest()->getParam('to_date');

$month_year = date('Y-m', strtotime($from));

// Get All Store ID of this Area
$QStore = new Application_Model_Store();
$store_tmp = $QStore->getAllStoreByArea($area_id);

$store_list = array();
foreach ($store_tmp as $value) { $store_list[] = $value['st_id']; }

//print_r($store_list); die;

// Delete
$QOppoPcTarget = new Application_Model_OppoPcTarget();

$where = array();
$where[] = $QOppoPcTarget->getAdapter()->quoteInto('from_date = ?', $from);
$where[] = $QOppoPcTarget->getAdapter()->quoteInto('to_date = ?', $to);
$where[] = $QOppoPcTarget->getAdapter()->quoteInto('store_id IN (?)', $store_list);

$QOppoPcTarget->delete($where);

$flashMessenger = $this->_helper->flashMessenger;
$flashMessenger->setNamespace('success')->addMessage("Reset All Store Target of Area : ".$area_name." Successful!");

$back_url = $this->getRequest()->getParam('back_url');
echo '<script>parent.location.href="'. ( !empty( $back_url ) ? ($back_url) : (HOST.'manage/sales-achievement/sales-achievement?month_year='.$month_year.'&area_id='.$area_id) ).'"</script>';
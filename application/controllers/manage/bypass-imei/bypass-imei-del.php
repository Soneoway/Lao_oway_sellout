<?php
$imei = $this->getRequest()->getParam('imei');

$QBypassImei = new Application_Model_BypassImei();
$QTimingSale = new Application_Model_TimingSale();

// Check Already Timing 
$where_ts = $QTimingSale->getAdapter()->quoteInto('imei = ?', $imei);
$result_timing = $QTimingSale->fetchAll($where_ts)->ToArray();

if ( !$result_timing ) {
	$where = $QBypassImei->getAdapter()->quoteInto('imei = ?', $imei);
	$QBypassImei->delete($where);
} else {
	$flashMessenger = $this->_helper->flashMessenger;
	$flashMessenger->setNamespace('success')->addMessage("ไม่สามารถลบ IMEI : ".$imei." นี้ได้ เนื่องจากถูกรายงานยอดไปแล้วค่ะ!");
	$this->view->messages = $messages;
}

$this->_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : HOST.'manage/bypass-imei');
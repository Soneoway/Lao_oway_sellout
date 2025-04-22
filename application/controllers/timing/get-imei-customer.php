<?php
$this->_helper->layout->disableLayout();

$imei = trim($this->getRequest()->getParam('value', ''));
$imei = explode("\n", $imei);
$imei = array_filter(array_unique($imei));
$imei = is_array($imei) ? $imei : array();

$result = array();

foreach ($imei as $key => $value) {
	$customer = null;
    $result[$value] = $this->getCustomerInfo($value, $customer);
}

// ghi log
$QLog = new Application_Model_CheckImeiToolLog();
$QLog->log($imei, CheckImeiLogType::Customer);

$this->view->result = $result;
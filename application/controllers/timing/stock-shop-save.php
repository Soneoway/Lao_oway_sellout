<?php
$this->_helper->layout->disableLayout();

$is_check = $this->getRequest()->getParam('is_check', 0);
$store_id = $this->getRequest()->getParam('store_id');

//echo "store : ".$store_id;

if ($is_check) {
    $imei = trim($this->getRequest()->getParam('value', ''));
    $imei = explode("\n", $imei);
} else {
    $imei = $this->getRequest()->getParam('value');
    
}

$imei = array_unique($imei);
$all_imei = count($imei);

if ($all_imei == 1 && $imei[0] == '') { $is_check = 0; $all_imei = 0; }

$QTiming = new Application_Model_Timing();
$QImei = new Application_Model_WebImei();

$cnt = 0;
$result = array();
$now = date('Y-m-d H:i:s');

if ($is_check != 0) {
	foreach ($imei as $key => $value) {
		$value = trim($value);

		$flag = 0;
		$info = "";
		$result_info = "";

		// check imei exist in wms
		$where_imei = $QImei->getAdapter()->quoteInto('imei_sn = ?', $value);
		$imei_result = $QImei->fetchRow($where_imei);

		if (!$imei_result) {
			$result_info = 'ไม่มี imei นี้ในระบบค่ะ!';
		} else {
			//$checkImei = $QTiming->checkImeiDealer($value, $store_id);

			if ($imei_result['stock_shop_status'] == 2) {

				$result_info = 'Imei Expired - Last timing date : '.date('d/m/Y H:i:s', strtotime($imei_result['stock_shop_date']));

			} 
/*
			elseif ($checkImei == false) { 

				$result_info = 'imei ไม่ถูกช่องทาง';

			} 
*/
			else {
				
				$data = array(
					'stock_shop_id'		=>	$store_id,
					'stock_shop_status'	=>	1,
					'stock_shop_date'	=>	$now,
					'stock_shop_scan'	=>	$now
				);

				$where = $QImei->getAdapter()->quoteInto('imei_sn = ?', $value);
				$QImei->update($data, $where);
				$cnt = $cnt + 1;
				$flag = 1;

			}

		} 

		// show only False Imei
		if ($flag == 0) {
			$result[$value] = array(
				'sales_info' => $info,
				'result' => array(
					'code' => $return,
					'info' => $result_info,
				),
			);
		}

	}

	$userStorage = Zend_Auth::getInstance()->getStorage()->read();

	// Save Log 
	$QLog = new Application_Model_StockShopLogs();
	$data_log = array(
		'store_id'		=>	$store_id,
		'total_imei'	=>	$all_imei,
		'success_imei'	=>	$cnt,
		'created_by'	=>	$userStorage->id,
		'created_at'	=>	date('Y-m-d H:i:s')
	);
	$QLog->insert($data_log);
}

//print_r($result);
$this->view->all_imei = $all_imei;
$this->view->cnt_imei = $cnt;
$this->view->result = $result;

?>
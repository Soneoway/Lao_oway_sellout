<?php
$id = $this->getRequest()->getParam('id');

$QModel = new Application_Model_Store();
$QStoreStaff = new Application_Model_StoreStaff();
$QStoreStaffLog = new Application_Model_StoreStaffLog();


// Update Store Status
$data = array(
    'del' => 1,
	//'d_id' => null,
);

$where = $QModel->getAdapter()->quoteInto('id = ?', $id);
$QModel->update($data, $where);

//$QWS = new Application_Model_WS();    
$data['id'] = $id;
// print_r($data);
//$QWS->_deleteStoreToTrade($data);

foreach($data as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
$data2 = rtrim($fields_string, '&');
/*
$ch = curl_init();

//curl_setopt($ch, CURLOPT_URL,"http://trade.oppo.in.th/trade/wsdeletestoretotrade");
curl_setopt($ch, CURLOPT_URL,"http://tmk.oppo.in.th/api/delete-store-to-trade");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data2);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$server_output = curl_exec($ch);
curl_close ($ch);

if ($server_output != 1) { throw new Exception("Failed on TMS! => ". $server_output); }
*/
// Update Store Staff Log
$where = array();
$where[] = $QStoreStaffLog->getAdapter()->quoteInto('store_id = ?', $id);
$where[] = $QStoreStaffLog->getAdapter()->quoteInto('released_at IS NULL', 1);

$data = array( 'released_at' => time() );

$QStoreStaffLog->update($data, $where);

// Remove Store Staff 
$where = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);
$QStoreStaff->delete($where);

//remove cache
$cache = Zend_Registry::get('cache');
$cache->remove('store_cache');
$cache->remove('store_staff_log_cache');
$cache->remove('store_assigned_cache');

$this->_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : HOST.'manage/store');
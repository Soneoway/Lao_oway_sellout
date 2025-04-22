<?php
$id = $this->getRequest()->getParam('id');

$QModel = new Application_Model_Store();
$QAreaControl = new Application_Model_AreaControl();
$QSubArea = new Application_Model_SubArea();
$QStoreStaff = new Application_Model_StoreStaff();
$QStoreStaffLog = new Application_Model_StoreStaffLog();

$store = $QModel->find($id);
$store = $store->current();

$data = array(
    'del' => null,
);

$where = $QModel->getAdapter()->quoteInto('id = ?', $id);
$QModel->update($data, $where);
//$QWS = new Application_Model_WS();    
$data['id'] = $id;
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
// Check Current Area Control
$ac_where = $QAreaControl->getAdapter()->quoteInto('sub_district = ?', $store->sub_district);
$ac_result = $QAreaControl->fetchRow($ac_where);

$sa_where = $QSubArea->getAdapter()->quoteInto('id = ?', $ac_result['sub_area_id']);
$sa_result = $QSubArea->fetchRow($sa_where);

if ( !empty($sa_result) ) {
	$staff_id = $sa_result['staff_id'];

	// Insert Store Staff 
	$data = array(
	    'staff_id' => $staff_id,
	    'store_id' => $id,
	    'is_leader' => 1,
	);

	$QStoreStaff->insert($data);

	// Insert Store Staff Log
	$data = array(
	    'staff_id' => $staff_id,
	    'store_id' => $id,
	    'is_leader' => 1,
	    'joined_at' => time(),
	);

	$QStoreStaffLog->insert($data);
}


//remove cache
$cache = Zend_Registry::get('cache');
$cache->remove('store_cache');
$cache->remove('store_assigned_cache');

$this->_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : HOST.'manage/store');
<?php
set_time_limit(0);

$page            = $this->getRequest()->getParam('page', 1);
$sort            = $this->getRequest()->getParam('sort', 'product_count');
$desc            = $this->getRequest()->getParam('desc', 1);
$from            = $this->getRequest()->getParam('from', date('01/m/Y'));
$to              = $this->getRequest()->getParam('to', date('d/m/Y'));
$name            = $this->getRequest()->getParam('name');//store name
$area_id         = $this->getRequest()->getParam('area_id');
$goods           = $this->getRequest()->getParam('goods');
$color_id        = $this->getRequest()->getParam('color_id');
$regional_market = $this->getRequest()->getParam('regional_market');
$export          = $this->getRequest()->getParam('export');
$staff_name      = $this->getRequest()->getParam('staff_name');
$staff_code      = $this->getRequest()->getParam('staff_code');

$params = array(
	'page'            => $page,
	'sort'            => $sort,
	'desc'            => $desc,
	'from'            => $from,
	'to'              => $to,
	'name'            => $name,
	'area_id'         => $area_id,
	'regional_market' => $regional_market,
	'export'          => $export,
	'staff_name'      => $staff_name,
	'staff_code'      => $staff_code,
	'color_id'        => $color_id,
	'goods'           => $goods,

);
$QGood = new Application_Model_Good();

$this->view->goods = $QGood->get_cache2();

$QColor = new Application_Model_GoodColor();

$this->view->color = $QColor->get_cache();

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
if (!$userStorage || !isset($userStorage->id)) {$this->_redirect(HOST);
}

$group_id = $userStorage->group_id;

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id)) {
    $params['asm'] = $userStorage->id;
} elseif ($userStorage->group_id == SALES_ID) {
    $params['sale_id'] = $userStorage->id;
} elseif ($userStorage->group_id == LEADER_ID) {
    $params['leader_id'] = $userStorage->id;
}

$QArea = new Application_Model_Area();
//$areas = $QArea->get_cache();
$areas = $QArea->fetchAll(null, 'name');

$QRegionalMarket = new Application_Model_RegionalMarket();

// if (in_array($group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id)) {
//     // lấy khu vực của asm
//     $QAsm = new Application_Model_Asm();
//     $asm_cache = $QAsm->get_cache();
//     $params['area_list'] = isset($asm_cache[ $userStorage->id ]['area']) ? $asm_cache[ $userStorage->id ]['area'] : array();

// } elseif ($group_id == My_Staff_Group::SALES) {
//     // lấy cửa hàng của sale
//     $QStoreStaffLog = new Application_Model_StoreStaffLog();
//     $store_cache = $QStoreStaffLog->get_stores_cache($userStorage->id, date_create_from_format("d/m/Y", $from)->format("Y-m-d"), date_create_from_format("d/m/Y", $to)->format("Y-m-d"));

//     $params['store_list'] = $store_cache;
//     $params['sale_id'] = $userStorage->id;
// } elseif ($group_id == My_Staff_Group::LEADER) {
//     // lấy cửa hàng của sale
//     $QStoreLeaderLog = new Application_Model_StoreLeaderLog();
//     $store_cache = $QStoreLeaderLog->get_stores_cache($userStorage->id, date_create_from_format("d/m/Y", $from)->format("Y-m-d"), date_create_from_format("d/m/Y", $to)->format("Y-m-d"));

//     $params['store_list'] = $store_cache;
//     $params['leader_id'] = $userStorage->id;
// }

if ($area_id) {
	if (is_array($area_id) && count($area_id)) {
		$where = $QRegionalMarket->getAdapter()->quoteInto('area_id IN (?)', $area_id);
	} else {

		$where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id);
	}

	$this->view->regional_markets = $QRegionalMarket->fetchAll($where, 'name');
}

if ($regional_market) {
	if (is_array($regional_market) && count($regional_market)) {
		$where = $QRegionalMarket->getAdapter()->quoteInto('parent IN (?)', $regional_market);
	} else {

		$where = $QRegionalMarket->getAdapter()->quoteInto('parent = ?', $regional_market);
	}

	$this->view->districts = $QRegionalMarket->fetchAll($where, 'name');
}

if ($district) {
	$QStore = new Application_Model_Store();

	if (is_array($regional_market) && count($regional_market)) {
		$where = $QStore->getAdapter()->quoteInto('district IN (?)', $district);
	} else {

		$where = $QStore->getAdapter()->quoteInto('district = ?', $district);
	}

	$this->view->stores = $QStore->fetchAll($where, 'name');
}

$limit = LIMITATION;
$total = $total2 = 0;

$QShopInventoryLogs = new Application_Model_StockShopLogs();
$Performance        = $QShopInventoryLogs->salesScanPerformance($page, $limit, $total, $params);

$report = $QShopInventoryLogs->salesScanReport($params);

$ScanImei = $QShopInventoryLogs->salesScanImei($params);



$this->view->report = $report;
$this->view->ScanImei = $ScanImei;

$this->view->performance = $Performance;

if (isset($export) && $export) {
	if ($export == 1) {
		// // export sell all brand
		$storeBrand = $QShopInventoryLogs->salesScanPerformance(null, null, $total2, $params);
        
		$this->_exportExcelAll($storeBrand, $params);
	}
	// if ($export == 2) {
	//     // export org dealer
	//     //print_r ($sales);
	//     // $this->_exportExcelOrgDealer($sales,$params);
	// }
}

//print_r ($sales);

unset($params['get_total_sales']);
unset($params['asm']);
unset($params['store_list']);
unset($params['area_list']);
unset($params['sale_id']);
unset($params['leader_id']);

//$this->view->total_quantity  = $total_sales['total_quantity'];
//$this->view->total_activated = $total_sales['total_activated'];
$this->view->sales       = $sales;
$this->view->offset      = $limit*($page-1);
$this->view->total       = $total;
$this->view->limit       = $limit;
$this->view->url         = HOST.'timing/sales-scan-performance'.($params?'?'.http_build_query($params).'&':'?');
$this->view->desc        = $desc;
$this->view->current_col = $sort;
$this->view->to          = $to;
$this->view->from        = $from;
$this->view->areas       = $areas;
$this->view->params      = $params;
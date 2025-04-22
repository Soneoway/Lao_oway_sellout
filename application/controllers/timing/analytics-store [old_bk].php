<?php
set_time_limit(0);

$page       = $this->getRequest()->getParam('page', 1);
$sort       = $this->getRequest()->getParam('sort', 'product_count');
$desc       = $this->getRequest()->getParam('desc', 1);
$from       = $this->getRequest()->getParam('from', date('01/m/Y') );
$to         = $this->getRequest()->getParam('to', date('d/m/Y'));
$id       = $this->getRequest()->getParam('id');
$name       = $this->getRequest()->getParam('name');
$area_id    = $this->getRequest()->getParam('area_id');
$regional_market   = $this->getRequest()->getParam('regional_market');
$district   = $this->getRequest()->getParam('district');
$store      = $this->getRequest()->getParam('store');
$has_pg     = $this->getRequest()->getParam('has_pg');
$sales_from = $this->getRequest()->getParam('sales_from');
$sales_to   = $this->getRequest()->getParam('sales_to');
$org        = $this->getRequest()->getParam('org');
$market_type= $this->getRequest()->getParam('market_type');
$market_name= $this->getRequest()->getParam('market_name');
$export     = $this->getRequest()->getParam('export');

$staff_name = $this->getRequest()->getParam('staff_name');
$staff_code = $this->getRequest()->getParam('staff_code');

$params = array(
    'page'       => $page,
    'sort'       => $sort,
    'desc'       => $desc,
    'from'       => $from,
    'to'         => $to,
    'id'         => $id,
    'name'       => $name,
    'area_id'    => $area_id,
    'regional_market'   => $regional_market,
    'district'   => $district,
    'store'      => $store,
    'has_pg'     => $has_pg,
    'sales_from' => $sales_from,
    'sales_to'   => $sales_to,
    'org'        => $org,
    'market_type'=> $market_type,
    'market_name'=> $market_name,
    'export'     => $export,
    'staff_name' => $staff_name,
    'staff_code' => $staff_code,
);


$userStorage = Zend_Auth::getInstance()->getStorage()->read();
if (!$userStorage || !isset($userStorage->id)) $this->_redirect(HOST);

$group_id = $userStorage->group_id;

$QArea = new Application_Model_Area();
//$areas = $QArea->get_cache();
$areas = $QArea->fetchAll(null, 'name');

$QRegionalMarket = new Application_Model_RegionalMarket();

$QMarketType = new Application_Model_MarketType();
$market_type = $QMarketType->fetchAll(null, 'name');

if (in_array($group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id)) {
    // lấy khu vực của asm
    $QAsm = new Application_Model_Asm();
    $asm_cache = $QAsm->get_cache();
    $params['area_list'] = isset($asm_cache[ $userStorage->id ]['area']) ? $asm_cache[ $userStorage->id ]['area'] : array();

} elseif ($group_id == My_Staff_Group::SALES || $group_id == PCM_ID) {
    // lấy cửa hàng của sale
    $QStoreStaffLog = new Application_Model_StoreStaffLog();
    $store_cache = $QStoreStaffLog->get_stores_cache($userStorage->id, date_create_from_format("d/m/Y", $from)->format("Y-m-d"), date_create_from_format("d/m/Y", $to)->format("Y-m-d"));

    $params['store_list'] = $store_cache;
    if($group_id == My_Staff_Group::SALES) { $params['sale_id'] = $userStorage->id; } 
} elseif ($group_id == My_Staff_Group::LEADER) {
    // lấy cửa hàng của sale
    $QStoreLeaderLog = new Application_Model_StoreLeaderLog();
    $store_cache = $QStoreLeaderLog->get_stores_cache($userStorage->id, date_create_from_format("d/m/Y", $from)->format("Y-m-d"), date_create_from_format("d/m/Y", $to)->format("Y-m-d"));

    $params['store_list'] = $store_cache;
    $params['leader_id'] = $userStorage->id;
}

if ($userStorage->group_id == AM_ID)
    $params['am'] = $userStorage->id;
/*

if ($area) {
    $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area);
    $this->view->regional_markets = $QRegionalMarket->fetchAll($where, 'name');
}

if ($province) {
    $where = $QRegionalMarket->getAdapter()->quoteInto('parent = ?', $province);
    $this->view->districts = $QRegionalMarket->fetchAll($where, 'name');
}

if ($district) {
    //get store
    $QStore = new Application_Model_Store();
    $where = $QStore->getAdapter()->quoteInto('district = ?', $district);
    $this->view->stores = $QStore->fetchAll($where, 'name');
}*/

if ($area_id) {
    if (is_array($area_id) && count($area_id))
        $where = $QRegionalMarket->getAdapter()->quoteInto('area_id IN (?)', $area_id);
    else
        $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id);

    $this->view->regional_markets = $QRegionalMarket->fetchAll($where, 'name');
}

if ($regional_market) {
    if (is_array($regional_market) && count($regional_market))
        $where = $QRegionalMarket->getAdapter()->quoteInto('parent IN (?)', $regional_market);
    else
        $where = $QRegionalMarket->getAdapter()->quoteInto('parent = ?', $regional_market);

    $this->view->districts = $QRegionalMarket->fetchAll($where, 'name');
}

if ($district) {
    $QStore = new Application_Model_Store();

    if (is_array($regional_market) && count($regional_market))
        $where = $QStore->getAdapter()->quoteInto('district IN (?)', $district);
    else
        $where = $QStore->getAdapter()->quoteInto('district = ?', $district);

    $this->view->stores = $QStore->fetchAll($where, 'name');
}


$limit = LIMITATION;
$total = $total2 = 0;

$QImeiKpi = new Application_Model_ImeiKpi();
$sales = $QImeiKpi->fetchStore($page, $limit, $total, $params);

$QOrg = new Application_Model_ImeiKpi();
    
if (is_array($org) && count($org))
    $where = $QOrg->getAdapter()->quoteInto('o.org_id IN (?)', $org);
else
    $where = $QOrg->getAdapter()->quoteInto('o.org_id = ?', $org);

$tmp = $QOrg->fetchORG(null, null, $total2, $org);
//print_r($tmp); die;
$this->view->org = $tmp;

//$storeBrand = $QImeiKpi->fetchStorebrand($params);
//var_dump($storeBrand);die;

if ( isset($export) && $export ) {
    if ($export == 1) { 
        // export sell all brand
        $storeBrand = $QImeiKpi->fetchStorebrand(null, null, $total2, $params);
        $this->_exportExcelbrand($storeBrand,$params);
    }
    if ($export == 2) {
        // export org dealer
        //print_r ($sales); 
        $this->_exportExcelOrgDealer($sales,$params);
    }
    if ($export == 3) {
        // export by Market Type
        $this->_exportExcelByMarketType($params);
    }
} else {
    $params['get_total_sales'] = true;
    $total_sales = $QImeiKpi->fetchStore(null, null, $total2, $params);

    $total_q = 0;
    $total_a = 0;
    for($i=0;$i<count($total_sales);$i++) { 
        $total_q = $total_q + $total_sales[$i]['total_quantity'];
        $total_a = $total_a + $total_sales[$i]['total_activated'];
    }

    $this->view->total_quantity  = $total_q;
    $this->view->total_activated = $total_a;
}

//print_r ($sales);

unset($params['get_total_sales']);
unset($params['asm']);
unset($params['store_list']);
unset($params['area_list']);
unset($params['sale_id']);
unset($params['leader_id']);
unset($params['am']);

//$this->view->total_quantity  = $total_sales['total_quantity'];
//$this->view->total_activated = $total_sales['total_activated'];
$this->view->sales           = $sales;
$this->view->offset          = $limit*($page-1);
$this->view->total           = $total;
$this->view->limit           = $limit;
$this->view->url             = HOST.'timing/analytics-store'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->desc            = $desc;
$this->view->current_col     = $sort;
$this->view->to              = $to;
$this->view->from            = $from;
$this->view->areas           = $areas;
$this->view->market_type     = $market_type;
$this->view->params          = $params;
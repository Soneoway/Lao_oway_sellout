<?php
set_time_limit(0);

$page            = $this->getRequest()->getParam('page', 1);
$sort            = $this->getRequest()->getParam('sort', 'product_count');
$desc            = $this->getRequest()->getParam('desc', 1);
$export          = $this->getRequest()->getParam('export', 0);
$from            = $this->getRequest()->getParam('from', date('01/m/Y') );
$to              = $this->getRequest()->getParam('to', date('d/m/Y'));
$name            = $this->getRequest()->getParam('name');
$area            = $this->getRequest()->getParam('area');
$regional_market = $this->getRequest()->getParam('regional_market');
$district        = $this->getRequest()->getParam('district');
$store           = $this->getRequest()->getParam('store');
$has_pg          = $this->getRequest()->getParam('has_pg');
$sales_from      = $this->getRequest()->getParam('sales_from');
$sales_to        = $this->getRequest()->getParam('sales_to');

$params = array(
    'page'            => $page,
    'sort'            => $sort,
    'desc'            => $desc,
    'from'            => $from,
    'to'              => $to,
    'name'            => $name,
    'area'            => $area,
    'regional_market' => $regional_market,
    'district'        => $district,
    'store'           => $store,
    'has_pg'          => $has_pg,
    'sales_from'      => $sales_from,
    'sales_to'        => $sales_to,
    'export'          => $export,
);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$group_id = $userStorage->group_id;

$QArea            = new Application_Model_Area();
$areas            = $QArea->get_cache();

$QRegionalMarket  = new Application_Model_RegionalMarket();

if (in_array($group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id)) {
    $params['asm'] = $userStorage->id;
} elseif ($group_id == SALES_ID) {
    $params['sales_store'] = $userStorage->id;
} elseif ($group_id == LEADER_ID) {
    $params['leader_province'] = $userStorage->id;
}

if ($area) {

    $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area);
    $this->view->regional_markets = $QRegionalMarket->fetchAll($where, 'name');
}

if ($regional_market) {
    $where = $QRegionalMarket->getAdapter()->quoteInto('parent = ?', $regional_market);
    $this->view->districts = $QRegionalMarket->fetchAll($where, 'name');
}

if ($district) {
    $QStore  = new Application_Model_Store();

    //get store
    $where = $QStore->getAdapter()->quoteInto('district = ?', $district);
    $this->view->stores = $QStore->fetchAll($where, 'name');
}

$limit = LIMITATION;
$total = $total2 = 0;

$QTiming          = new Application_Model_Timing();



if (isset($export) && $export) {
    // $sales            = $QTiming->report_by_store($page, $limit, $total, $params);
    $this->_exportExcelByStore($params);
    exit;
}

$sales            = $QTiming->report_by_store($page, $limit, $total, $params);

$params['get_total_sales'] = true;

$total_sales      = $QTiming->report_by_store(null, null, $total2, $params);

unset($params['get_total_sales']);

unset($params['area_asm']);



$this->view->total_sales      = $total_sales;
$this->view->sales            = $sales;
$this->view->offset           = $limit*($page-1);
$this->view->total            = $total;
$this->view->limit            = $limit;
$this->view->url              = HOST.'timing/analytics-store'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->desc             = $desc;
$this->view->current_col      = $sort;
$this->view->to               = $to;
$this->view->from             = $from;
$this->view->areas            = $areas;
$this->view->params           = $params;
<?php
$page            = $this->getRequest()->getParam('page', 1);
$sort            = $this->getRequest()->getParam('sort', 'product_count');
$desc            = $this->getRequest()->getParam('desc', 1);
$export          = $this->getRequest()->getParam('export', 0);
$export2         = $this->getRequest()->getParam('export2', 0);
$export_pg       = $this->getRequest()->getParam('export_pg', 0);
$export_sales    = $this->getRequest()->getParam('export_sales', 0);
$export_leader   = $this->getRequest()->getParam('export_leader', 0);
$export_timing   = $this->getRequest()->getParam('export_timing', 0);
$from            = $this->getRequest()->getParam('from', date('01/m/Y'));
$to              = $this->getRequest()->getParam('to', date('d/m/Y'));
$phone_number    = $this->getRequest()->getParam('phone_number');
$name            = $this->getRequest()->getParam('name');
$area            = $this->getRequest()->getParam('area');
$regional_market = $this->getRequest()->getParam('regional_market');
$district        = $this->getRequest()->getParam('district');
$store           = $this->getRequest()->getParam('store');

$params = array(
    'page'            => $page,
    'sort'            => $sort,
    'desc'            => $desc,
    'from'            => $from,
    'to'              => $to,
    'phone_number'    => $phone_number,
    'name'            => $name,
    'area'            => $area,
    'regional_market' => $regional_market,
    'district'        => $district,
    'store'           => $store,
    'export'          => $export,
    'export2'         => $export2,
    'group_id'        => 4
);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$group_id = $userStorage->group_id;

if (isset($export_timing) && $export_timing == 1) {
    if (!in_array($group_id, array(HR_ID, ADMINISTRATOR_ID, BOARD_ID, SALES_EXT_ID)))
        $this->_redirect(HOST.'timing/analytics');

    $this->_exportExcel_timing_by_month($params);
    exit;
}

$QArea            = new Application_Model_Area();
$areas            = $QArea->get_cache();

$QRegionalMarket  = new Application_Model_RegionalMarket();

// My_Staff_Permission_Area::view_all // kiểm tra xem người này có toàn quyền xem tất cả các khu vực hay không
// viết xong rồi giờ nhìn vô éo nhớ nó là cái gì, phải comment lại cho chắc
if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

if ($area) {
    $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area);
    $this->view->regional_markets = $QRegionalMarket->fetchAll($where, 'name');
}

if ($regional_market) {
    //get district
    $where = $QRegionalMarket->getAdapter()->quoteInto('parent = ?', $regional_market);

    $this->view->districts = $QRegionalMarket->fetchAll($where, 'name');
}

if ($district) {

    $QStore  = new Application_Model_Store();

    //get store
    $where = $QStore->getAdapter()->quoteInto('district = ?', $district);

    $this->view->stores = $QStore->fetchAll($where, 'name');
}

if ( (isset($export) && $export) or (isset($export2) && $export2) )
    $limit = null;
else
    $limit = LIMITATION;

$total = $total2 = 0;


$QTiming          = new Application_Model_Timing();

if (isset($export_pg) && $export_pg) {
    if (!in_array($group_id, array(HR_ID, ADMINISTRATOR_ID, BOARD_ID, SALES_EXT_ID)) && !in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view))
        $this->_redirect(HOST.'timing/analytics');

    $this->_exportExcel_pg($params);
    exit;
}

if (isset($export_sales) && $export_sales) {
    if (!in_array($group_id, array(HR_ID, ADMINISTRATOR_ID, BOARD_ID, SALES_EXT_ID)) && !in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view))
        $this->_redirect(HOST.'timing/analytics');

    $this->_exportExcel_sales($params);
    exit;
}

if (isset($export_leader) && $export_leader) {
    if (!in_array($group_id, array(HR_ID, ADMINISTRATOR_ID, BOARD_ID, SALES_EXT_ID)) && !in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view))
        $this->_redirect(HOST.'timing/analytics');

    $this->_exportExcel_leader($params);
    exit;
}

$sales = $QTiming->report($page, $limit, $total, $params);

if (isset($export) && $export) {
    // if (!in_array($group_id, array(HR_ID, ADMINISTRATOR_ID, BOARD_ID, SALES_EXT_ID)))
    //     $this->_redirect(HOST.'timing/analytics');

    $this->_exportExcel($sales);
    exit;
}

if (isset($export2) && $export2) {
    // if (!in_array($group_id, array(HR_ID, ADMINISTRATOR_ID, BOARD_ID, SALES_EXT_ID, ASM_ID, ASMSTANDBY_ID)))
    //     $this->_redirect(HOST.'timing/analytics');
    
    $this->_exportExcel2($sales);
    exit;
}

$params['get_total_sales'] = true;

$total_sales      = $QTiming->report(null, null, $total2, $params);

unset($params['get_total_sales']);

$this->view->total_sales      = $total_sales;
$this->view->sales            = $sales;
$this->view->offset           = $limit*($page-1);
$this->view->total            = $total;
$this->view->limit            = $limit;
$this->view->url              = HOST.'timing/analytics'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->desc             = $desc;
$this->view->current_col      = $sort;
$this->view->to               = $to;
$this->view->from             = $from;
$this->view->areas            = $areas;
$this->view->params           = $params;
<?php
$name            = $this->getRequest()->getParam('name');
$from            = $this->getRequest()->getParam('from', date('01/m/Y'));
$to              = $this->getRequest()->getParam('to', date('d/m/Y'));
$area_id         = $this->getRequest()->getParam('area_id');
$regional_market = $this->getRequest()->getParam('regional_market');
$district        = $this->getRequest()->getParam('district');
$store           = $this->getRequest()->getParam('store');
$sales_from      = $this->getRequest()->getParam('sales_from');
$sales_to        = $this->getRequest()->getParam('sales_to');
$export          = $this->getRequest()->getParam('export');
$brand_id        = $this->getRequest()->getParam('brand_id');
$d_tag           = $this->getRequest()->getParam('d_tag');

$page = $this->getRequest()->getParam('page', 1);
$sort = $this->getRequest()->getParam('sort', 'total');
$desc = $this->getRequest()->getParam('desc', 1);

$limit = LIMITATION;
$total = 0;

$params = array(
    'name'            => $name,
    'from'            => $from,
    'to'              => $to,
    'area_id'         => $area_id,
    'regional_market' => $regional_market,
    'district'        => $district,
    'store'           => $store,
    'page'            => $page,
    'sort'            => $sort,
    'desc'            => $desc,
    'sales_from'      => $sales_from,
    'sales_to'        => $sales_to,
    'export'          => $export,
    'brand'           => $brand_id,
    'd_tag'           => $d_tag
);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$QGood = new Application_Model_Good();
$this->view->goods = $QGood->getProduct($params);

$brands = $QGood->get_brand();
$this->view->brands = $brands;

$QArea = new Application_Model_Area();
//$this->view->areas = $QArea->fetchAll(null, 'name');

if ( in_array($userStorage->group_id, array(RM_ID, RMSTANDBY_ID)) ) {
    $areas = $QArea->getAreaByAsmTable($userStorage->id);

    $data = array();
    foreach($areas as $item) {
        $data[] = $item['id'];
    }

    $params['rgm_area'] = implode(',', $data);
    
} else {
    $areas = $QArea->fetchAll(null, 'name');
}

$this->view->areas = $areas;

$QRegionalMarket = new Application_Model_RegionalMarket();

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

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id)) {
    $params['asm'] = $userStorage->id;

} elseif ($userStorage->group_id == SALES_ID) {
    $params['sales_store'] = $userStorage->id;

} elseif ($userStorage->group_id == LEADER_ID) {
    $params['leader_id'] = $userStorage->id;
}

if ($userStorage->group_id == AM_ID)
    $params['am'] = $userStorage->id;

//check brandshop manager permission
if ($userStorage->group_id == BM_ID)
    $params['bm_id'] = $userStorage->id;

//check admin brandshop permission
if ($userStorage->group_id == 39)
    $params['admin_bs'] = $userStorage->id;

$QTiming = new Application_Model_Timing();


if (!empty($_GET)) {
    $sales = $QTiming->report_by_product($page, $limit, $total, $params);

    $this->view->sales = $sales;
    $this->view->total = $total;
    
    $params['get_total_sales'] = true;
    $this->view->total_sales = $QTiming->report_by_product(null, null, $total, $params);
}

unset($params['asm']);
unset($params['sales_store']);
unset($params['leader_province']);
unset($params['get_total_sales']);

if ( isset($export) && $export ) {
    if ($export == 1) { 
        // export Product by reporter
        //echo "<pre>".print_r($sales)."</pre>";die;
        $this->_exportExcelProduct($sales);
    }
    
    if ($export == 2) {
        $this->_exportExcelModel($sales);
    }
}

$this->view->limit       = $limit;
$this->view->desc        = $desc;
$this->view->offset      = $limit*($page-1);
$this->view->url         = HOST.'timing/analytics-product'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->current_col = $sort;
$this->view->params      = $params;
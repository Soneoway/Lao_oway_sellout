<?php
$sort                     = $this->getRequest()->getParam('sort');
$desc                     = $this->getRequest()->getParam('desc', 1);
$export                   = $this->getRequest()->getParam('export', 0);
$activate_from            = $this->getRequest()->getParam('activate_from', date('01/m/Y') );
$activate_to              = $this->getRequest()->getParam('activate_to', date('d/m/Y') );
$timing_from              = $this->getRequest()->getParam('timing_from');
$timing_to                = $this->getRequest()->getParam('timing_to');
$area                     = $this->getRequest()->getParam('area');
$tmp_area_id              = $this->getRequest()->getParam('tmp_area_id');
$warehouse_id             = $this->getRequest()->getParam('warehouse_id');
$brand_id                 = $this->getRequest()->getParam('brand_id');


$good_id = $this->getRequest()->getParam('good_id'); // Search by product name

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$this->view->userStorage = $userStorage;

$params = array(
    'sort'            => $sort,
    'desc'            => $desc,
    'activate_from'   => $activate_from,
    'activate_to'     => $activate_to,
    'timing_from'     => $timing_from,
    'timing_to'       => $timing_to,
    'area'            => $area,
    'export'          => $export,
    'good_id'         => $good_id,
    'warehouse_id'            => $warehouse_id,
    'brand_id'        => $brand_id
);


$QWarehouse = new Application_Model_Warehouse();
$QGood = new Application_Model_Good();
$warehouse = $QWarehouse->get_cache();
$goods_list = $QGood->get_cache2();
$this->view->goods = $goods_list;

$sales = array();
$staff_list = array();

if ( in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id)) {
    $area_list    = array();
    $QAsm         = new Application_Model_Asm();
    $list_regions = $QAsm->get_cache($userStorage->id);
    $list_regions = isset($list_regions['area']) && is_array($list_regions['area']) ? $list_regions['area'] : array();
    $this->view->viewed_area_id = $list_regions;

    $params['list_regions'] = $list_regions;
}

if ($userStorage->group_id == AM_ID)
    $params['am'] = $userStorage->id;

if ($userStorage->group_id == 39)
    $params['admin_bs'] = $userStorage->id;

$QArea = new Application_Model_Area();

if ( in_array($userStorage->group_id, array(RM_ID, RMSTANDBY_ID)) ) {
    $areas = $QArea->getAreaByAsmTable($userStorage->id);
} else {
    $areas = $QArea->fetchAll(null, 'name');
}

$this->view->areas = $areas;

$QTiming = new Application_Model_Timing();
$QStoreStaff = new Application_Model_StoreStaff();

if ( isset($export) && $export ) {
    if ($export == 1) { 
        $imei = $QTiming->report_by_imei($params);
        $this->_exportExcelImei($imei);
    }
    if ($export == 2) { 
        $product_list = $QTiming->report_by_imei_product($params);
        $this->_exportExcelImeiProduct($product_list);
    }
    if($export == 3){
        $product_list = $QTiming->report_by_imei_distributor($params);
        $this->_exportExcelImeiDistridutor($product_list);
    }
    if($export == 4){
        $product_list = $QTiming->report_by_imei_warehouse($params);
        $this->_exportExcelImeiWarehouse($product_list);
    }

} else {
    // Check First Time Not Show Data
    if (!empty($_GET)) {
        $sales = $QTiming->report_by_imei($params);

        $params['total_sales'] = 1;
        $total_sales = $QTiming->report_by_imei($params);
    }
}

$this->view->total_sales    = $total_sales;
$this->view->sales          = $sales;
$this->view->url            = HOST.'timing/analytics-imei'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->desc           = $desc;
$this->view->current_col    = $sort;
$this->view->to             = $to;
$this->view->from           = $from;
$this->view->params         = $params;
$this->view->warehouses_cached = $warehouse;
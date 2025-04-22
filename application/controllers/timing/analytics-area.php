<?php
$sort            = $this->getRequest()->getParam('sort');
$desc            = $this->getRequest()->getParam('desc', 1);
$export          = $this->getRequest()->getParam('export', 0);
$from            = $this->getRequest()->getParam('from', date('01/m/Y') );
$to              = $this->getRequest()->getParam('to', date('d/m/Y') );
$area            = $this->getRequest()->getParam('area');
$tmp_area_id     = $this->getRequest()->getParam('tmp_area_id');
$good_id         = $this->getRequest()->getParam('good');
$brand_id        = $this->getRequest()->getParam('brand_id');

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$this->view->userStorage = $userStorage;

$params = array(
    'sort'   => $sort,
    'desc'   => $desc,
    'from'   => $from,
    'to'     => $to,
    'area'   => $area,
    'export' => $export,
    'good'   => $good_id,
    'brand'  => $brand_id
);

$QGood = new Application_Model_Good();

$goods_list = $QGood->getProduct($params);
$this->view->goods = $goods_list;

$brands = $QGood->get_brand();
$this->view->brands = $brands;



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
        $sales = $QTiming->analytic_area($params);
        $this->_exportExcelArea($sales);
    }
    if ($export == 2) { 
        $staff_list = $QStoreStaff->getSaleByArea($tmp_area_id);
        $this->_exportExcelSaleByArea($staff_list);
    }
    if ($export == 3) {
        $staff_list = $QStoreStaff->getPCByArea($tmp_area_id);
        $this->_exportExcelPCByArea($staff_list);
    }
    if ($export == 4) {
        $staff_list = $QStoreStaff->getPCByArea($tmp_area_id, $params, 0);
        $this->_exportExcelPCByArea($staff_list);
    }

    if ($export == 5) {
        $staff_list = $QStoreStaff->getPCByArea($tmp_area_id, $params, 1);
        $this->_exportExcelPCByArea($staff_list);
    }

} else {
    // Check First Time Not Show Data
    if (!empty($_GET)) {
        $sales = $QTiming->analytic_area($params);

        $params['total_sales'] = 1;
        $total_sales = $QTiming->analytic_area($params);
    }
}

$this->view->total_sales    = $total_sales;
$this->view->sales          = $sales;
$this->view->url            = HOST.'timing/analytics-area'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->desc           = $desc;
$this->view->current_col    = $sort;
$this->view->to             = $to;
$this->view->from           = $from;
$this->view->params         = $params;
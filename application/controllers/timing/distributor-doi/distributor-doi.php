<?php

$page           = $this->getRequest()->getParam('page', 1);
$from           = $this->getRequest()->getParam('from', date('01/m/Y'));
$to             = $this->getRequest()->getParam('to', date('d/m/Y'));
$good_id        = $this->getRequest()->getParam('good_id');
$color_id       = $this->getRequest()->getParam('color_id');
$grand_area_id  = $this->getRequest()->getParam('grand_area_id');
$area_id        = $this->getRequest()->getParam('area_id');
$s_code         = $this->getRequest()->getParam('s_code');
$s_name         = $this->getRequest()->getParam('s_name');
$export         = $this->getRequest()->getParam('export');
$warehouse_id   = $this->getRequest()->getParam('warehouse_id');
$status         = $this->getRequest()->getParam('status');
$cat_id         = $this->getRequest()->getParam('cat_id');
$stock_date     = $this->getRequest()->getParam('stock_date');
$distributor_status  = $this->getRequest()->getParam('distributor_status',1);
$brand_id       = $this->getRequest()->getParam('brand_id');


$limit = LIMITATION;
$total = $total2 = $total_count = 0;
$doi_rate = 14;

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$params = array( 
    'doi_rate'      => $doi_rate,
    'from'          => $from,
    'to'            => $to,
    'good_id'       => $good_id,
    'color_id'      => $color_id,
    'grand_area_id' => $grand_area_id,
    'area_id'       => $area_id,
    's_code'        => $s_code,
    's_name'        => $s_name,
    'export'        => $export,
    'warehouse_id'  => $warehouse_id,
    'status'        => $status,
    'cat_id'        => $cat_id,
    'stock_date'    => $stock_date,
    'distributor_status'    => $distributor_status,
    'brand'         => $brand_id
);

if ( in_array($userStorage->group_id, array(RM_ID, RMSTANDBY_ID, ASM_ID, ASMSTANDBY_ID)) ){
    $params['asm'] = $userStorage->id;
    $params['asm_group'] = $userStorage->group_id;
}

if ( in_array($userStorage->group_id, array(AM_ID)) ){
    $params['am'] = $userStorage->id;
}

if ( in_array($userStorage->group_id, array(39)) ){
    $params['admin_bs'] = $userStorage->id;
}

if ( in_array($userStorage->group_id, array(SALES_ID)) ){
    $params['sales_id'] = $userStorage->id;
}

if ( in_array($userStorage->group_id, array(PGPB_ID,PCDB_ID)) ){
    $params['pc_id'] = $userStorage->id;
}

//print_r($params);

$QTiming    = new Application_Model_Timing();
$QGood      = new Application_Model_Good();
$QArea      = new Application_Model_Area();
$QGrandArea = new Application_Model_GrandArea();
$GoodColorCombined = new Application_Model_GoodColorCombined();
$QWarehouse = new Application_Model_Warehouse();
$QBrand     = new Application_Model_Brand();

$good_list = $QGood->getProduct($params);

if ( in_array($userStorage->group_id, array(RM_ID, RMSTANDBY_ID, ASM_ID, ASMSTANDBY_ID)) ) {

    $areas = $QArea->getAreaByAsmTable($userStorage->id);

    if ( in_array($userStorage->group_id, array(RM_ID, RMSTANDBY_ID)) ) {
        $grand_areas = $QGrandArea->getGrandAreaByAsmTable($userStorage->id);
    } else {
        $grand_areas = $QGrandArea->fetchAll(null, 'name');
    }
} else {
    $areas = $QArea->fetchAll(null, 'name');
    $grand_areas = $QGrandArea->fetchAll(null, 'name');
}

if (isset($grand_area_id) && $grand_area_id) {
    $areas = $QArea->getAreaByGrandArea($grand_area_id);
}

if (isset($good_id) && $good_id) {
    $color_list = $GoodColorCombined->getColorByModel($good_id);
}

if ( in_array($userStorage->group_id, array(WAREHOUSE_GROUP)) ) {
    $params['warehouse_group'] = $userStorage->id;
    $warehouses = $QWarehouse->get_warehouse_staff($userStorage->id);
}else{
    $warehouses = $QWarehouse->get_warehouse();
}

$this->view->brands = $QBrand->allbrand();
$this->view->warehouses = $warehouses;
$this->view->goods = $good_list;
$this->view->colors = $color_list;
$this->view->areas = $areas;
$this->view->grand_areas = $grand_areas;

if ( isset($export) && $export ) {

    if ($export == 1) { 
        $distributor_list = $QTiming->fetchPagination_DOI_ExportList(null, null, $total2, $params);
        $this->_exportDOIList($distributor_list); 
    }
    //export Imei DOi
    if($export == 2){
        $params['list_imei'] = ture;
        $distributor_list = $QTiming->fetchPagination_DOI_ExportImeiList(null, null, $total2, $params);
        $this->_exportDOIImeiList($distributor_list);
    }

    if($export == 3) {
        $params['good_model'] = ture;
        $distributor_list = $QTiming->fetchPagination_DOI_ExportModelList(null, null, $total2, $params);
        $this->_exportDOIModelList($distributor_list);

    }

} 

// Check First Time Not Show Data
if (!empty($_GET)) {
    $distributor_list = $QTiming->fetchPagination_DOI($page, $limit, $total, $params);

    $params['get_total_count'] = 0;
    $total_count = $QTiming->fetchPagination_DOI(null, null, $total2, $params);
} 

$this->view->params = $params;
$this->view->lists = $distributor_list;
$this->view->total_count = $total_count;

$this->view->offset      = $limit*($page-1);
$this->view->total       = $total;
$this->view->limit       = $limit;
$this->view->url         = HOST.'timing/distributor-doi'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->test       = ( $params ? '?'.http_build_query($params).'&' : '?' );

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('distributor-doi/partials/list');
} else
$this->_helper->viewRenderer->setRender('distributor-doi/index');
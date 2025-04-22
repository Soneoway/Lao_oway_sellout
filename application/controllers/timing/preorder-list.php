<?php

$page            = $this->getRequest()->getParam('page', 1);
$from            = $this->getRequest()->getParam('from', date('01/m/Y'));
$to              = $this->getRequest()->getParam('to', date('d/m/Y'));
$good_id         = $this->getRequest()->getParam('good_id');
$color_id        = $this->getRequest()->getParam('color_id');
$area_id         = $this->getRequest()->getParam('area_id');
$store_id        = $this->getRequest()->getParam('store_id');
$store_name      = $this->getRequest()->getParam('store_name');
$export		     = $this->getRequest()->getParam('export');


$limit = LIMITATION;
$total = $total2 = $total_count = 0;
$doi_rate = 14;

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$params = array( 
    'from'          => $from,
    'to'            => $to,
    'good_id'       => $good_id,
    'color_id'      => $color_id,
    'area_id'       => $area_id,
    'store_id'		=> $store_id,
    'store_name'	=> $store_name,
);

$QTiming    = new Application_Model_Timing();
$QGood      = new Application_Model_Good();
$QArea      = new Application_Model_Area();
$QGrandArea = new Application_Model_GrandArea();
$GoodColorCombined = new Application_Model_GoodColorCombined();
$QWarehouse = new Application_Model_Warehouse();
$QCustromerPreOrder = new Application_Model_CustromerPreOrder();

$where = array();
$where[] = $QGood->getAdapter()->quoteInto('cat_id = ?', PHONE_CAT_ID);
$where[] = $QGood->getAdapter()->quoteInto('pre_order_product = ?',1);

$good_list = $QGood->fetchAll($where, 'desc');

if ( in_array($userStorage->group_id, array(PGPB_ID)) ){
	$params['pc'] = $userStorage->id;
}

if(in_array($userStorage->group_id, array(RM_ID, RMSTANDBY_ID, ASM_ID, ASMSTANDBY_ID))){
	$params['rd'] = $userStorage->id;
}

if(in_array($userStorage->group_id, array(SALES_ID))){
	$params['salse'] = $userStorage->id;
}

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

if ( isset($export) && $export ) {
	 if ($export == 1) {
	 	$data = $QCustromerPreOrder->fetchPagination(null, null, $total, $params);
	 	$this->_exportPreOrder($data);
	 }
}

//search only
// if (!empty($_GET)) {
	$preorder_list = $QCustromerPreOrder->fetchPagination($page, $limit, $total, $params);

// }

$this->view->preorder_list = $preorder_list;
$this->view->warehouses = $warehouses;
$this->view->goods = $good_list;
$this->view->colors = $color_list;
$this->view->areas = $areas;
$this->view->grand_areas = $grand_areas;
$this->view->params = $params;

$this->view->offset      = $limit*($page-1);
$this->view->total       = $total;
$this->view->limit       = $limit;
$this->view->url         = HOST.'timing/preorder-list'.( $params ? '?'.http_build_query($params).'&' : '?' );


if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('preorder/partials/list');
} else
$this->_helper->viewRenderer->setRender('preorder/index');
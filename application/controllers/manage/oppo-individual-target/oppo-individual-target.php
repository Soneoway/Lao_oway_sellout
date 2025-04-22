<?php

$date = $this->getRequest()->getParam('month_year', date('Y-m-d'));
$area_id = $this->getRequest()->getParam('area_id');
$sale_id = $this->getRequest()->getParam('sale_id');

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$last_03 = date('M-Y', strtotime("-3 Month", strtotime($date)));
$last_02 = date('M-Y', strtotime("-2 Month", strtotime($date)));
$last_01 = date('M-Y', strtotime("-1 Month", strtotime($date)));

$params = array( 
    'sale_id'   => $sale_id,
    'area_id'   => $area_id,
    'group_id'  => $userStorage->group_id,
    'from' 		=> date('Y-m-01', strtotime($date)),
    'to' 		=> date('Y-m-t', strtotime($date)),
 	'last_03'	=> $last_03,
	'last_02'	=> $last_02,
	'last_01'	=> $last_01,
);

// print_r($params);

if ( in_array($userStorage->group_id, array(RM_ID, ASM_ID, ASMSTANDBY_ID)) ){
	$params['asm'] = $userStorage->id;
}

if ( $userStorage->group_id == SALES_ID ){
    $params['sale'] = $userStorage->id;
}

$QOIT = new Application_Model_OppoIndividualTarget();
$QSPT = new Application_Model_StorePriceTarget();

$QOppoAreaTarget = new Application_Model_OppoAreaTarget();
$hero = $QOppoAreaTarget->getheroproduct();

// Get Area
$QArea = new Application_Model_Area();

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id)) {

    $QAsm = new Application_Model_Asm();
    $result_area = $QAsm->get_cache($userStorage->id);

    if ( !empty($result_area['area']) ) {
        $where_area = $QArea->getAdapter()->quoteInto('id IN (?)', $result_area['area']);
        $this->view->areas = $QArea->fetchAll($where_area, 'name');
    } else {
        // ASM Reponsible Level Province 

        if ( !empty($result_area['province']) ) {

            $params['province_id'] = $result_area['province'];

            $QRegionalMarket = new Application_Model_RegionalMarket();
            $this->view->areas = $QRegionalMarket->getAreaByProvince($result_area['province']);

        } else {
            $this->view->areas = array();
        }
        
    }

} else {

    if ( isset($params['sale']) && $params['sale'] ) {

        // Get Sale Info 
        $result_sale = $QSPT->getSaleInfo($userStorage->id);
        // print_r($result_sale);

        $where_area = $QArea->getAdapter()->quoteInto('id = ?', $result_sale['area_id']);
        $this->view->areas = $QArea->fetchAll($where_area, 'name');
        
        $params2 = array('area_id' => $result_sale['area_id']);
        $sale_list = $QSPT->getSaleByArea($params2);
        $this->view->sale_list = $sale_list;

        $params['area_id'] = array(0 => $result_sale['area_id']);
        $params['sale_id'] = array(0 => $result_sale['staff_id']);

    } else {
        $this->view->areas = $QArea->fetchAll(null, 'name');
    }
}  

if ($area_id) {
    
    $params2 = array('area_id' => $area_id);
    $sale_list = $QSPT->getSaleByArea($params2);
    // print_r($sale_list);

    $this->view->sale_list = $sale_list;
}

// Get List
if (!empty($_GET)) { 

    // Get PC List 
    $list = $QOIT->getListByPcStore($params);
    // echo "<pre>"; print_r($list);
} 

$this->view->params = $params;
$this->view->lists = $list;
$this->view->getheroproduct = $hero;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

$messages_error = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages_error = $messages_error;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('oppo-individual-target/partials/list');
} else
    $this->_helper->viewRenderer->setRender('oppo-individual-target/index');
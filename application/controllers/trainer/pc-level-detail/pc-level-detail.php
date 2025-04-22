<?php

function orderbydate( $a, $b ) {
    return strtotime($a["flag_date"]) - strtotime($b["flag_date"]);
}

$staff_id = $this->getRequest()->getParam('staff_id');
// $export = $this->getRequest()->getParam('export', 0);

$params = array(
    'staff_id' => $staff_id,
    //'export' => $export,
);

//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;


$QStaff = new Application_Model_Staff();

if ($export && $export == 1) {
    // $pc_level_list = $QStaff->getPcLevelByArea(null, null, $total, $params);
    // $this->_exportTimingIssue($pc_level_list,$params);
}

$pc_info = array();

$pc_info_01 = $QStaff->getPcLevelInfo($params);
$pc_info_02 = $QStaff->getShopBindingByPC($params);
$pc_info_03 = $QStaff->getShopCheckByPC($params);
$pc_info_04 = $QStaff->getETestByPC($params);

// echo "<pre>"; print_r($pc_info_01);
// echo "<pre>"; print_r($pc_info_02);
// echo "<pre>"; print_r($pc_info_03);
// echo "<pre>"; print_r($pc_info_04);

$pc_info = array_merge($pc_info_01, $pc_info_02, $pc_info_03, $pc_info_04);

usort($pc_info, "orderbydate");

// echo "<pre>"; print_r($pc_info);

$this->view->params 		= $params;
$this->view->pc_info_list 	= $pc_info;

$this->view->pc_info 		= $pc_info_01;
$this->view->shop_binding 	= $pc_info_02;
$this->view->shop_check 	= $pc_info_03;
$this->view->pc_e_test 		= $pc_info_04;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('pc-level-detail/partials/list');
} else
    $this->_helper->viewRenderer->setRender('pc-level-detail/index');
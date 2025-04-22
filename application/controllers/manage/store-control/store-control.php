<?php

$store_id        = $this->getRequest()->getParam('store_id');
$store_name      = $this->getRequest()->getParam('store_name');
$staff_code      = $this->getRequest()->getParam('staff_code');
$staff_name      = $this->getRequest()->getParam('staff_name');
$area_id         = $this->getRequest()->getParam('area_id');

$params = array(
    'store_id'        => $store_id,
    'store_name'      => $store_name,
    'staff_code'      => $staff_code,
    'staff_name'      => $staff_name,
    'area_id'         => $area_id,
);

$QStoreControl = new Application_Model_StoreControl();

//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;


$QArea = new Application_Model_Area();
$this->view->areas = $QArea->fetchAll(null, 'name');


if ($export && $export == 1) {
    // $timing_issue = $QTimingIssue->fetchPagination(null, null, $total, $params);
    // $this->_exportTimingIssue($timing_issue,$params);
}

// Check First Time Not Show Data
if (!empty($_GET)) { 
    $store_control = $QStoreControl->store_control_list($params);
}

$this->view->params = $params;
$this->view->store_control = $store_control;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('store-control/partials/list');
} else
    $this->_helper->viewRenderer->setRender('store-control/index');
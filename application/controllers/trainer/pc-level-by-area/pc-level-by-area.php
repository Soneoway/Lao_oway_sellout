<?php

$area_id         = $this->getRequest()->getParam('area_id');
$export          = $this->getRequest()->getParam('export', 0);

$params = array(
    'area_id'         => $area_id,
    'export'          => $export,
);

//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

$QArea = new Application_Model_Area();
$this->view->areas = $QArea->fetchAll(null, 'name');

$QStaff = new Application_Model_Staff();

if ($export && $export == 1) {
    // $pc_level_list = $QStaff->getPcLevelByArea(null, null, $total, $params);
    // $this->_exportTimingIssue($pc_level_list,$params);
}

$pc_level_list = $QStaff->getPcLevelByArea($params);

$this->view->params = $params;
$this->view->pc_level_list = $pc_level_list;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('pc-level-by-area/partials/list');
} else
    $this->_helper->viewRenderer->setRender('pc-level-by-area/index');
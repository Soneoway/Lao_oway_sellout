<?php

$staff_code  = $this->getRequest()->getParam('staff_code');
$staff_name  = $this->getRequest()->getParam('staff_name');
$area_id     = $this->getRequest()->getParam('area_id');
$tmp_my      = $this->getRequest()->getParam('month_year');
$unpunish_id = $this->getRequest()->getParam('unpunish_id');
$export      = $this->getRequest()->getParam('export');

$month_year = date('Y-m', strtotime($tmp_my));

$params = array(
    'staff_code'    => $staff_code,
    'staff_name'    => $staff_name,
    'area_id'       => $area_id, 
    'unpunish_id'   => $unpunish_id,
    'from'          => date('Y-m-01', strtotime($month_year)),
    'to'            => date('Y-m-t', strtotime($month_year)),
    'export'        => $export,
);

// print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

// Get Area
$QArea = new Application_Model_Area();

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id)) {

    $QAsm = new Application_Model_Asm();
    $result_area = $QAsm->get_cache($userStorage->id);

    $where_area = $QArea->getAdapter()->quoteInto('id IN (?)', $result_area['area']);
    $this->view->areas = $QArea->fetchAll($where_area, 'name');

} else {
    $this->view->areas = $QArea->fetchAll(null, 'name');
}

$QUnPunishList = new Application_Model_UnPunishList();
$this->view->unpunish_type = $QUnPunishList->fetchAll(null, 'id');

$QStaffUnPunish = new Application_Model_StaffUnPunish();

if ($export && $export == 1) {
    $unpunish_list = $QStaffUnPunish->getList($params);
    $this->_exportPcUnpunishList($unpunish_list,$params);
}

// Check First Time Not Show Data
if (!empty($_GET)) { 
    $unpunish_list = $QStaffUnPunish->getList($params);
    //print_r($penalty_list);
}

$this->view->params = $params;
$this->view->unpunish_list = $unpunish_list;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages_success = $messages;

$messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    //$this->_helper->viewRenderer->setRender('penalty-charge/partials/list2');
} else
    $this->_helper->viewRenderer->setRender('pc-unpunish/index');
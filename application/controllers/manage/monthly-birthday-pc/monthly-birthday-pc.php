<?php

$area_id    = $this->getRequest()->getParam('area_id');
$month_year = $this->getRequest()->getParam('month_year');
$export     = $this->getRequest()->getParam('export');

$params = array(
    'area_id'    => $area_id,
    'month_year' => $month_year,
    'export'     => $export 
);
// print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

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

// Get Data 
$QMBP = new Application_Model_MonthlyBirthdayPc();

if ($export) {

    if ($export == 1) {
        $mbp_list = $QMBP->getMbpByArea($params);
        $this->_exportBirthdayPcList($mbp_list,$params);
    }

    if ($export == 2) {
        $mbp_list = $QMBP->getMonthlyBirthdayPcDetails($params);
        $this->_exportBirthdayPcDetails($mbp_list,$params);
    }
    
    if ($export == 3) {
        $mbp_list = $QMBP->getMbpByArea($params);
        $this->_exportBirthdayPcListByArea($mbp_list,$params);
    }

}

// Check First Time Not Show Data
if (!empty($_GET)) { 
    $mbp_list = $QMBP->getMbpByArea($params);
    // print_r($mbp_list);
}


$this->view->params = $params;
$this->view->mbp_list = $mbp_list;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('monthly-birthday-pc/partials/list');
} else
    $this->_helper->viewRenderer->setRender('monthly-birthday-pc/index');
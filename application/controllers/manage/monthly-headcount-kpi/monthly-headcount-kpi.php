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

if ($userStorage->id == 2094)
    $params['staff_id'] = $userStorage->id;

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
$QMKHL = new Application_Model_MonthlyKpiHeadcountList();

if ($export) {

    if ($export == 1) {
        $mkhl_list = $QMKHL->getMkhlByArea($params);
        $this->_exportKpiHeadcountList($mkhl_list,$params);
    }

    if ($export == 2) {
        $mkhl_list = $QMKHL->getMonthlyKpiHeadcountDetails($params);
        $this->_exportKpiHeadcountDetails($mkhl_list,$params);
    }

}

// Check First Time Not Show Data
if (!empty($_GET)) { 
    $mkhl_list = $QMKHL->getMkhlByArea($params);
    // print_r($mhl_list);
}


$this->view->params = $params;
$this->view->mkhl_list = $mkhl_list;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('monthly-headcount-kpi/partials/list');
} else
    $this->_helper->viewRenderer->setRender('monthly-headcount-kpi/index');
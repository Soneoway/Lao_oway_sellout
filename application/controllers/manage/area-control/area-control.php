<?php

$store_id        = $this->getRequest()->getParam('store_id');
$store_name      = $this->getRequest()->getParam('store_name');
$staff_code      = $this->getRequest()->getParam('staff_code');
$staff_name      = $this->getRequest()->getParam('staff_name');
$area_id         = $this->getRequest()->getParam('area_id');
$regional_market = $this->getRequest()->getParam('regional_market');
$district        = $this->getRequest()->getParam('district');
$sub_district    = $this->getRequest()->getParam('sub_district');

$params = array(
    'store_id'        => $store_id,
    'store_name'      => $store_name,
    'staff_code'      => $staff_code,
    'staff_name'      => $staff_name,
    'area_id'         => $area_id,
    'regional_market' => $regional_market,
    'district'        => $district,
    'sub_district'    => $sub_district,
);

$QAreaControl = new Application_Model_AreaControl();


//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;


$QArea = new Application_Model_Area();
$this->view->areas = $QArea->fetchAll(null, 'name');

$QRegionalMarket = new Application_Model_RegionalMarket();

if ($area_id) {
    if (is_array($area_id) && count($area_id))
        $where = $QRegionalMarket->getAdapter()->quoteInto('area_id IN (?)', $area_id);
    else
        $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id);

    $this->view->regional_markets = $QRegionalMarket->fetchAll($where, 'name');
}

if ($regional_market) {
    if (is_array($regional_market) && count($regional_market))
        $where = $QRegionalMarket->getAdapter()->quoteInto('parent IN (?)', $regional_market);
    else
        $where = $QRegionalMarket->getAdapter()->quoteInto('parent = ?', $regional_market);

    $this->view->districts = $QRegionalMarket->fetchAll($where, 'name');
}

if ($district) {
    $QSubDistrict = new Application_Model_SubDistrict();

    if (is_array($district) && count($district))
        $where = $QSubDistrict->getAdapter()->quoteInto('district IN (?)', $district);
    else
        $where = $QSubDistrict->getAdapter()->quoteInto('district = ?', $district);

    
    $this->view->sub_districts = $QSubDistrict->fetchAll($where, 'name');
}

if ($export && $export == 1) {
    // $timing_issue = $QTimingIssue->fetchPagination(null, null, $total, $params);
    // $this->_exportTimingIssue($timing_issue,$params);
}

// Check First Time Not Show Data
if (!empty($_GET)) { 
    $area_control = $QAreaControl->APDS_List($params);
}

$this->view->params = $params;
$this->view->area_control = $area_control;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('area-control/partials/list');
} else
    $this->_helper->viewRenderer->setRender('area-control/index');
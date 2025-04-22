<?php
$page            = $this->getRequest()->getParam('page', 1);
$area_id         = $this->getRequest()->getParam('area_id');
$regional_market = $this->getRequest()->getParam('regional_market');
$public_id       = $this->getRequest()->getParam('public_id');
$staff_name      = $this->getRequest()->getParam('staff_name');
$from            = $this->getRequest()->getParam('from', date('01/m/Y'));
$to              = $this->getRequest()->getParam('to', date('d/m/Y'));
$export          = $this->getRequest()->getParam('export', 0);
$chk_bkk         = $this->getRequest()->getParam('chk_bkk');

$limit = LIMITATION;
$total = 0;

$params = array(
    'public_id'       => $public_id,
    'staff_name'      => $staff_name,
    'area_id'         => $area_id,
    'regional_market' => $regional_market,
    'from'            => $from,
    'to'              => $to, 
    'export'          => $export,
    'chk_bkk'         => $chk_bkk,
);

$QPcFirstTraining = new Application_Model_PcFirstTraining();

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

if ($export) {
    if ($export == 1) {
        $staff_list = $QPcFirstTraining->fetchPagination(null, null, $total, $params);
        $this->_exportPcFirstTrainingExcel($staff_list, $params);
    }
    if ($export == 2) {
        $staff_list = $QPcFirstTraining->fetchPagination(null, null, $total, $params);
        $this->_exportPcFirstTrainingList($staff_list, $params);
    }
    if ($export == 3) {
        $staff_list = $QPcFirstTraining->fetchPagination(null, null, $total, $params);
        $this->_exportPcFirstTrainingSso($staff_list, $params);
    }
    if ($export == 4) {
        $staff_list = $QPcFirstTraining->fetchPagination(null, null, $total, $params);
        $this->_exportDataHr($staff_list, $params);
    }
} 

$staff_list = $QPcFirstTraining->fetchPagination($page, $limit, $total, $params);

$this->view->params = $params;
$this->view->staff_list = $staff_list;

$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'trainer/pc-first-training/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('pc-first-training/partials/list');
} else
    $this->_helper->viewRenderer->setRender('pc-first-training/index');
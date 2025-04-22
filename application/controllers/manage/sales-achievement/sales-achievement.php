<?php

$date = $this->getRequest()->getParam('month_year', date('Y-m-d'));
$area_id = $this->getRequest()->getParam('area_id');

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$last_03 = date('M-Y', strtotime("-3 Month", strtotime($date)));
$last_02 = date('M-Y', strtotime("-2 Month", strtotime($date)));
$last_01 = date('M-Y', strtotime("-1 Month", strtotime($date)));

$params = array( 
    'date'      => $date,
    'from' 		=> date('Y-m-01', strtotime($date)),
    'to' 		=> date('Y-m-t', strtotime($date)),
    'area_id'	=> $area_id,
    'group_id' 	=> $userStorage->group_id,
    'last_03'	=> $last_03,
	'last_02'	=> $last_02,
	'last_01'	=> $last_01,
);

if ( in_array($userStorage->group_id, array(RM_ID, ASM_ID, ASMSTANDBY_ID, TRAINING_TEAM_ID, 36)) ){
	$params['asm'] = $userStorage->id;
}

$QArea = new Application_Model_Area();
//$this->view->areas = $QArea->get_cache();

if ( in_array($userStorage->group_id, array(RM_ID, RMSTANDBY_ID)) ) {
    $areas = $QArea->getAreaByAsmTable($userStorage->id);
} else {
    $areas = $QArea->fetchAll(null, 'name');
}

$this->view->areas = $areas;

$QOppoSaleTarget = new Application_Model_OppoSaleTarget();

// Check First Time Not Show Data
if (!empty($_GET)) {
	$list = $QOppoSaleTarget->getListBySaleAchieve($params);
} 

$this->view->params = $params;
$this->view->lists = $list;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('sales-achievement/partials/list');
} else
    $this->_helper->viewRenderer->setRender('sales-achievement/index');
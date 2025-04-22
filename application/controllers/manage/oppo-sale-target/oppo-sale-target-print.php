<?php

$this->_helper->layout->disableLayout();

$area_id = $this->getRequest()->getParam('area_id');
$area_name = $this->getRequest()->getParam('area_name');
$from = $this->getRequest()->getParam('from_date');
$to = $this->getRequest()->getParam('to_date');

$params = array(
	'area_id' 	=> $area_id,
	'area_name'	=> $area_name,
	'from'		=> $from,
	'to'		=> $to,
);

// $userStorage = Zend_Auth::getInstance()->getStorage()->read();
// $group_id = $userStorage->group_id;

$QOppoSaleTarget = new Application_Model_OppoSaleTarget();

// Get Current Sale Target
$sale_target = $QOppoSaleTarget->getSaleTarget($params);
$asm_target = $QOppoSaleTarget->getLeaderTarget($params, 0);
$rm_target = $QOppoSaleTarget->getLeaderTarget($params, 1);

$this->view->params = $params;
$this->view->sales_target = $sale_target;
$this->view->asms_target = $asm_target;
$this->view->rms_target = $rm_target;

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
$this->_helper->viewRenderer->setRender('/oppo-sale-target/print');
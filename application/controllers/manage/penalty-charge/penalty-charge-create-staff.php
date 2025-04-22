<?php

$id = $this->getRequest()->getParam('id');

if (isset($id) && $id) {
	$QPunishMemo = new Application_Model_PunishMemo();
	$pm_detail = $QPunishMemo->getDetail($id);
	//print_r($pm_detail);

	$this->view->pm_detail = $pm_detail;
} 

$QArea = new Application_Model_Area();
$this->view->areas = $QArea->fetchAll(null, 'name');

$QPunishList = new Application_Model_PunishList();
$this->view->punish_list = $QPunishList->fetchAll(null, 'name');

$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
$this->_helper->viewRenderer->setRender('penalty-charge/create-staff');

<?php

$id = $this->getRequest()->getParam('id');

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

if (isset($id) && $id) {
	$QStaffUnPunish = new Application_Model_StaffUnPunish();
	$unpunish_detail = $QStaffUnPunish->getDetail($id);
	//print_r($unpunish_detail);

	$this->view->unpunish_detail = $unpunish_detail;
} 

$QUnPunishList = new Application_Model_UnPunishList();
$this->view->unpunish_list = $QUnPunishList->fetchAll(null, 'id');

$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
$this->_helper->viewRenderer->setRender('pc-unpunish/create');

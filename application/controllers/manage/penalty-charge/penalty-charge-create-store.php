<?php

$id = $this->getRequest()->getParam('id');

if (isset($id) && $id) {
	$QPms = new Application_Model_PunishMemoStore();

    $where = $QPms->getAdapter()->quoteInto('id = ? ' , $id);
    $pms_detail = $QPms->fetchRow($where)->ToArray();
    //print_r($pms_detail);

    if (isset($pms_detail['store']) && $pms_detail['store']) {

    	$store_list = $QPms->store_list($pms_detail['store']);
    	//print_r($store_list);

    	$this->view->store_list = $store_list;
    }

	$this->view->pms_detail = $pms_detail;
} 

$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
$this->_helper->viewRenderer->setRender('penalty-charge/create-store');

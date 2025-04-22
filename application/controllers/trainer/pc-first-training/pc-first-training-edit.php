<?php
$pft_id = $this->getRequest()->getParam('id');

$QPcFirstTraining = new Application_Model_PcFirstTraining();
$staff_list = $QPcFirstTraining->getStaffDetails($pft_id);
//print_r($staff_list);

$this->view->staff_list = $staff_list;

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$this->view->group_id = $userStorage->group_id;

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
//$this->view->back_url = HOST."timing/timing-issue/";
//$this->view->back_url = HOST.'timing/timing-issue/'.( $params ? '?'.http_build_query($params).'&' : '?' );

$this->_helper->viewRenderer->setRender('/pc-first-training/create');
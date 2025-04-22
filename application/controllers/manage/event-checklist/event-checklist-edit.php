<?php
$id = $this->getRequest()->getParam('id');

$PcCheckInLog = new Application_Model_PcCheckInLog();
$staff_list = $PcCheckInLog->getEventCheckListDetails($id);

//echo '<pre>';
//print_r($staff_list);

$this->view->staff_list = $staff_list;

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$this->view->group_id = $userStorage->group_id;

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
//$this->view->back_url = HOST."timing/timing-issue/";
//$this->view->back_url = HOST.'timing/timing-issue/'.( $params ? '?'.http_build_query($params).'&' : '?' );

$this->_helper->viewRenderer->setRender('/event-checklist/create');
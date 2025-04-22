<?php
$ti_id = $this->getRequest()->getParam('id');

$QTimingIssue = new Application_Model_TimingIssue();
$timing_issue_detail = $QTimingIssue->getDetails($ti_id);
//print_r($timing_issue_detail);

$this->view->timing_issue_detail = $timing_issue_detail;

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$this->view->group_id = $userStorage->group_id;

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
//$this->view->back_url = HOST."timing/timing-issue/";
//$this->view->back_url = HOST.'timing/timing-issue/'.( $params ? '?'.http_build_query($params).'&' : '?' );

$this->_helper->viewRenderer->setRender('/timing-issue/create');
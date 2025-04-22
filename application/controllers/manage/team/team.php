<?php
$page = $this->getRequest()->getParam('page', 1);
$limit = LIMITATION;
$total = 0;

$params = array();

$QModel = new Application_Model_Team();
$teams = $QModel->fetchPagination($page, $limit, $total, $params);

$QDepartment = new Application_Model_Department();
$this->view->departments = $QDepartment->get_cache();

$this->view->teams = $teams;

$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'manage/team/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

$this->_helper->viewRenderer->setRender('team/index');
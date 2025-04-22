<?php
$page = $this->getRequest()->getParam('page', 1);
$limit = LIMITATION;
$total = 0;

$params = array();

$QModel = new Application_Model_Title();
$titles = $QModel->fetchPagination($page, $limit, $total, $params);

$QTeam = new Application_Model_Team();
$this->view->teams = $QTeam->get_cache();

$this->view->titles = $titles;

$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'manage/title/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

$this->_helper->viewRenderer->setRender('title/index');
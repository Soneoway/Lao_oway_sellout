<?php
$page = $this->getRequest()->getParam('page', 1);
$limit = LIMITATION;
$total = 0;

$params = array();

$QModel = new Application_Model_Area();
$areas = $QModel->fetchPagination($page, $limit, $total, $params);

$this->view->areas = $areas;

$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'manage/area/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

$this->_helper->viewRenderer->setRender('area/index');
<?php
$page = $this->getRequest()->getParam('page', 1);
$name = $this->getRequest()->getParam('name');
$limit = LIMITATION;
$total = 0;

$params = array(
    'name' => $name,
    );

$QCompany = new Application_Model_Company();
$this->view->companies = $QCompany->fetchPagination($page, $limit, $total, $params);
$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'manage/company/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$this->view->messages_success = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $flashMessenger->setNamespace('error')->getMessages();

$this->_helper->viewRenderer->setRender('company/list');
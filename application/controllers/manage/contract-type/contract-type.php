<?php
$page = $this->getRequest()->getParam('page', 1);
$limit = LIMITATION;
$total = 0;

$params = array();

$QModel = new Application_Model_ContractType();
$contract_types = $QModel->fetchPagination($page, $limit, $total, $params);

$this->view->contract_types = $contract_types;

$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'manage/contract-type/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

$this->_helper->viewRenderer->setRender('contract-type/index');
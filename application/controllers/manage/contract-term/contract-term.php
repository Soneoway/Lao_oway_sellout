<?php
$page = $this->getRequest()->getParam('page', 1);
$limit = LIMITATION;
$total = 0;

$params = array();

$QModel = new Application_Model_ContractTerm();
$contract_terms = $QModel->fetchPagination($page, $limit, $total, $params);

$this->view->contract_terms = $contract_terms;

$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'manage/contract-term/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

$this->_helper->viewRenderer->setRender('contract-term/index');
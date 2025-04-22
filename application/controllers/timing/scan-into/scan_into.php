<?php
$flashMessenger = $this->_helper->flashMessenger;

$desc           = $this->getRequest()->getParam('desc', 1);
$page           = $this->getRequest()->getParam('page', 1);

$befor_transfer            = $this->getRequest()->getParam('befor_transfer');
$after_transfer            = $this->getRequest()->getParam('after_transfer');
$good_id            	   = $this->getRequest()->getParam('good_id');
$from            	   	   = $this->getRequest()->getParam('from',date('01/m/Y'));
$to            	   	   	   = $this->getRequest()->getParam('to',date('d/m/Y'));
$office_befor              = $this->getRequest()->getParam('office_befor');
$office_after			   = $this->getRequest()->getParam('office_after');
$imei_sn				   = $this->getRequest()->getParam('imei_sn');

$limit = LIMITATION;
$total = 0;

$QStore = new Application_Model_Store();
$QGood = new Application_Model_Good();
$QStaff = new Application_Model_Staff();
$QGoodColor = new Application_Model_GoodColor();
$QSubArea = new Application_Model_SubArea();
$QImeiScanInto = new Application_Model_ImeiScanInto();

$params = array(
	'befor_transfer'	=> $befor_transfer,
	'after_transfer'	=> $after_transfer,
	'good_id'			=> $good_id,
	'from'				=> $from,
	'to'				=> $to,
	'office_befor'		=> $office_befor,
	'office_after'		=> $office_after,
	'imei_sn'			=> $imei_sn
);

$scanInto = $QImeiScanInto->fetchPagination($page, $limit, $total, $params);

$this->view->store = $QStore->get_cache();
$this->view->good = $QGood->get_cache();
$this->view->color = $QGoodColor->get_cache();
$this->view->subarea = $QSubArea->get_cache();
$this->view->staff = $QStaff->get_all_cache();
$this->view->scanInto = $scanInto;
$this->view->params = $params;
$this->view->limit  = $limit;
$this->view->total  = $total;
$this->view->url    = HOST.'timing/scan-into/'.( $params ? '?'.http_build_query($params).'&' : '?' );

$this->view->offset = $limit*($page-1);

$messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages = $messages;

$messages_success = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages_success = $messages_success;


$this->_helper->viewRenderer->setRender('/scan-into/index');
?>
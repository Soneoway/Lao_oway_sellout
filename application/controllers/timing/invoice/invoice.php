<?php 

	$userStorage = Zend_Auth::getInstance()->getStorage()->read();
	$flashMessenger = $this->_helper->flashMessenger;

	$total = 0;
	$limit = LIMITATION;

	$page   			= $this->getRequest()->getParam('page', 1);
	$from   			= $this->getRequest()->getParam('from', date('d/m/Y'));
	$to     			= $this->getRequest()->getParam('to', date('d/m/Y'));
	$sn         		= $this->getRequest()->getParam('sn');
	$store         		= $this->getRequest()->getParam('store');
	$imeis				= $this->getRequest()->getParam('imei');
	$id         = $this->getRequest()->getParam('id');
    $name       = $this->getRequest()->getParam('name');
	


	$params = array(
    	'from'            => $from,
    	'to'              => $to,
    	'sn'         	  => $sn,
    	'store'			  => $store,
    	'imei'			  => $imeis,
    	'id'         => $id,
        'name'       => $name,
	);

	$imei = explode("\r\n", $imeis);


	$QPrint = new Application_Model_Print();
	$QStore = new Application_Model_Store();


	$this->view->stores = $QStore->get_cache();

	$this->view->list = $QPrint->fetchPagination($page, $limit, $total, $params);
	$this->view->url     = HOST.'timing/invoice/invoice'.( $params ? '?'.str_replace('&', '&amp;', http_build_query($params)).'&' : '?' );
	$this->view->offset  = $limit*($page-1);
	$this->view->limit   = $limit;
	$this->view->total   = $total;
	$this->view->type    = $type;
	$this->view->params  = $params;
	
	$flashMessenger = $this->_helper->flashMessenger;
    $this->view->messages = $flashMessenger->setNamespace('success')->getMessages();
    $this->view->messages_error = $flashMessenger->setNamespace('error')->getMessages();

?>
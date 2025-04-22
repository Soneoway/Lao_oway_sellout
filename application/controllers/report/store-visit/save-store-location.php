<?php

$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$store_id 			= $this->getRequest()->getParam('store_id');
$lat 				= $this->getRequest()->getParam('lat');
$lng 				= $this->getRequest()->getParam('lng');

$QStore = new Application_Model_Store();

if(!$store_id) {
	$flashMessenger->setNamespace('error')->addMessage('Something went wrong. Please check and try again.');
	$this->_redirect(HOST . '/report/store-visit');
}

$where = $QStore->getAdapter()->quoteInto('id =?',$store_id);
$store = $QStore->fetchRow($where);

// Error Case Not in the system
if(!$store) {
	$flashMessenger->setNamespace('error')->addMessage('The store is not in the system. Please check and try again.');
	$this->_redirect(HOST . '/report/store-visit');
}


$data = array(
	'lat' 	=> $lat,
	'lng'	=> $lng
);


$where = $QStore->getAdapter()->quoteInto('id = ?',$store_id);
$QStore->update($data,$where);

$flashMessenger->setNamespace('success')->addMessage('Done!');

$this->_redirect(HOST.'report/store-visit');

?>
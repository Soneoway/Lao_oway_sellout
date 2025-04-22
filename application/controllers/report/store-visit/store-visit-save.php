<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);
$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$QStoreVisit = new Application_Model_StoreVisit();

if ($this->getRequest()->getMethod() == 'POST'){
	$date      = $this->getRequest()->getParam('date');
	$store      = $this->getRequest()->getParam('store');
	$store_code      = $this->getRequest()->getParam('store_code');
	$store_name      = $this->getRequest()->getParam('store_name');
	$store_address      = $this->getRequest()->getParam('store_address');

	$stock_oppo 			= $this->getRequest()->getParam('stock_oppo');
	$sales_oppo 			= $this->getRequest()->getParam('sales_oppo');
	$pc_oppo 			= $this->getRequest()->getParam('pc_oppo');
	$signboard 			= $this->getRequest()->getParam('signboard');
	$banner 			= $this->getRequest()->getParam('banner');
	$advertising 			= $this->getRequest()->getParam('advertising');
	$xstand_oppo 			= $this->getRequest()->getParam('xstand_oppo');
	$table_oppo 			= $this->getRequest()->getParam('table_oppo');
	$counter_oppo 			= $this->getRequest()->getParam('counter_oppo');

	$data = array(
		'store'         		=> $store,
		'store_code'         	=> $store_code,
		'store_name'         	=> $store_name,
		'store_address'         => $store_address,

		'stock_oppo'			=> $stock_oppo,
		'sales_oppo'			=> $sales_oppo,
		'pc_oppo'				=> $pc_oppo,
		'signboard'				=> $signboard,
		'banner'				=> $banner,
		'advertising'			=> $advertising,
		'xstand_oppo'			=> $xstand_oppo,
		'table_oppo'			=> $table_oppo,
		'counter_oppo'			=> $counter_oppo,

		'num'      				=> 1,
		'created_by'       		=> $userStorage->id,
		'created_at'      		=> date('Y-m-d H-i-s'),
	);
	$resualt = $QStoreVisit->insert($data);
	
$flashMessenger = $this->_helper->flashMessenger;
$flashMessenger->setNamespace('success')->addMessage('ລົງຢ້ຽມຢາມສຳເລັດ');
}
$this->_redirect(HOST.'report/store-visit');

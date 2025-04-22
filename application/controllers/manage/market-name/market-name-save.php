<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$mn_id = $this->getRequest()->getParam('mn_id');
$mn_name = $this->getRequest()->getParam('market_name');
$area_id = $this->getRequest()->getParam('area_id');
$market_type = $this->getRequest()->getParam('market_type');

$QMarketName = new Application_Model_MarketName();

if ($mn_id) {

	$where = $QMarketName->getAdapter()->quoteInto('id = ?', $mn_id);

	$data = array(
	    'name'    		 => $mn_name,
	    'area_id'    	 => $area_id,
	    'market_type_id' => $market_type,
	    'updated_by'	 => $userStorage->id,
	    'updated_at'	 => date('Y-m-d H:i:s'),
	);

	$result = $QMarketName->update($data,$where);

} else {

	$data = array(
	    'name'    		 => $mn_name,
	    'area_id'    	 => $area_id,
	    'market_type_id' => $market_type,
	    'created_by'	 => $userStorage->id,
	    'created_at'	 => date('Y-m-d H:i:s'),
	);

	$result = $QMarketName->insert($data);
}

$flashMessenger = $this->_helper->flashMessenger;

if ($result) {
    $flashMessenger->setNamespace('success')->addMessage('Done!');
} else {
    $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
}

$back_url = $this->getRequest()->getParam('back_url');
echo '<script>parent.location.href="'. ( !empty( $back_url ) ? ($back_url) : (HOST.'manage/market-name') ).'"</script>';
<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$store = $this->getRequest()->getParam('store');

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$now = date('Y-m-d H:i:s');

$db = Zend_Registry::get('db');
$db->beginTransaction();

$QStoreFocus = new Application_Model_StoreFocus();

try { 

	$data = array();
	for ($i=0;$i<count($store);$i++) {

		// Add New Store Control Map
		$data = array('store_id' => $store[$i]);
		$QStoreFocus->insert($data);

	}

	$db->commit();

} catch (Exception $e) {
    $db->rollBack();
    echo "Fail! : ".$e;
    exit;
}

$flashMessenger = $this->_helper->flashMessenger;
$back_url = $this->getRequest()->getParam('back_url');

echo '<script>parent.location.href="'. ( !empty( $back_url ) ? ($back_url) : (HOST.'manage/store-focus-list') ).'"</script>';
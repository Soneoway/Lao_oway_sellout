<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$cp_id 			= $this->getRequest()->getParam('cp_id');
$brand_id 		= $this->getRequest()->getParam('brand_id');
$product_name 	= $this->getRequest()->getParam('product_name');
$product_code 	= $this->getRequest()->getParam('product_code');
$product_status = $this->getRequest()->getParam('product_status');
$product_order  = $this->getRequest()->getParam('product_order');

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$now = date('Y-m-d H:i:s');

$db = Zend_Registry::get('db');
$db->beginTransaction();

$QCompetitorProduct = new Application_Model_CompetitorProduct();

try { 

	if ($product_status == 1) { $product_order = 9999; }

	$data = array(
		'brand_id' 	=> $brand_id,
		'name'		=> $product_name,
		'code'		=> $product_code,
		'status'	=> $product_status,
		'position'	=> $product_order,
	);


	if ( isset($cp_id) && $cp_id ) {

		$where = $QCompetitorProduct->getAdapter()->quoteInto('id = ?', $cp_id);
		$QCompetitorProduct->update($data, $where);

	} else {

		$QCompetitorProduct->insert($data);

	}

	$db->commit();

} catch (Exception $e) {
    $db->rollBack();
    echo "Fail! : ".$e;
    exit;
}

$flashMessenger = $this->_helper->flashMessenger;
$back_url = $this->getRequest()->getParam('back_url');

echo '<script>parent.location.href="'. ( !empty( $back_url ) ? ($back_url) : (HOST.'manage/competitor-product') ).'"</script>';
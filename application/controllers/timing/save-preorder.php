<?php
$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$QCustromPreOrder = new Application_Model_CustromerPreOrder();

if ($this->getRequest()->getMethod() == 'POST'){
	$date      = $this->getRequest()->getParam('date');
	$store      = $this->getRequest()->getParam('store');
	$product      = $this->getRequest()->getParam('product');
	$model      = $this->getRequest()->getParam('model');
	$deposit_money      = $this->getRequest()->getParam('deposit_money');
	$customer_names   = $this->getRequest()->getParam('customer_name');
	$phone_numbers    = $this->getRequest()->getParam('phone_number');
	$gender			 = $this->getRequest()->getParam('gender');
	$age 			= $this->getRequest()->getParam('age');

	$data = array(
		'store'         		=> $store,
		'deposit_money'         => $deposit_money,
		'good'     				=> $product,
		'color'  				=> $model,
		'customers_name'		=> $customer_names,
		'gender'				=> $gender,
		'age'					=> $age,
		'customers_phone'		=> $phone_numbers,
		'num'      				=> 1,
		'created_by'       		=> $userStorage->id,
		'created_at'      		=> date('Y-m-d H-i-s'),
	);
	$resualt = $QCustromPreOrder->insert($data);
}

$flashMessenger = $this->_helper->flashMessenger;
$flashMessenger->setNamespace('success')->addMessage('ສ້າງບິນສຳເລັດ');

echo '<script>parent.location.href="'.('/timing/preorder-list' ).'"</script>';
exit;
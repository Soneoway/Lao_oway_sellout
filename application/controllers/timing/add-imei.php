<?php
$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$id       = $this->getRequest()->getParam('pre_id');

$params = array(
	'pre_id' => $id
);

$QCustromerPreOrder = new Application_Model_CustromerPreOrder();
$QStore = new Application_Model_Store();
$QGood = new Application_Model_Good();
$QGoodColor = new Application_Model_GoodColor();

$where = $QCustromerPreOrder->getAdapter()->quoteInto('id =?',$id);
$pre_detail = $QCustromerPreOrder->fetchRow($where);
$staff_create = $pre_detail['created_by'];

if($staff_create != $userStorage->id){
	echo '<script>
		parent.alert("Cat`n Action !!.");
		parent.location.href="'.('/timing/preorder-list' ).'"
        </script>';
        exit;
}

if(!$id)
{
	$flashMessenger->setNamespace('error')->addMessage('Worng Action');
    $this->_redirect('/timing/preorder-list');
}

$where = $QCustromerPreOrder->getAdapter()->quoteInto('id =?', $id);
$preorder = $QCustromerPreOrder->fetchRow($where);

$store = $QStore->get_cache();
$good = $QGood->get_cache();
$color = $QGoodColor->get_cache();

$this->view->preorder = $preorder;
$this->view->store = $store;
$this->view->params = $params;
$this->view->good = $good;
$this->view->color = $color;


$QStaff = new Application_Model_Staff();
$where = $QStaff->getAdapter()->quoteInto('id = ?', $userStorage->id);
$this->view->staff = $QStaff->fetchRow($where);

$this->_helper->viewRenderer->setRender('add-imei');
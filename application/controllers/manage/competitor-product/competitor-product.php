<?php

$brand_id = $this->getRequest()->getParam('brand_id');
$export = $this->getRequest()->getParam('export');

$params = array(
    'brand_id'  => $brand_id,
    'export'    => $export,
);

//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

$QCompetitorBrand = new Application_Model_CompetitorBrand();
$this->view->brand_list = $QCompetitorBrand->fetchAll(null, 'name');

$QCompetitorProduct = new Application_Model_CompetitorProduct();

if ($export && $export == 1) {
    // $product_list = $QCompetitorProduct->getProductList($params);
    // $this->_exportCompetitorProductList($product_list,$params);
}

// Check First Time Not Show Data
// if (!empty($_GET)) { 
    $product_list = $QCompetitorProduct->getProductList($params);
// }

// echo "<pre>"; print_r($product_list);

$this->view->params = $params;
$this->view->product_list = $product_list;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('competitor-product/partials/list');
} else
    $this->_helper->viewRenderer->setRender('competitor-product/index');
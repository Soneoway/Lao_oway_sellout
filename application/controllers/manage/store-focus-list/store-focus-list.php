<?php

$store_id        = $this->getRequest()->getParam('store_id');
$store_name      = $this->getRequest()->getParam('store_name');
$area_id         = $this->getRequest()->getParam('area_id');
$export         = $this->getRequest()->getParam('export');

$params = array(
    'store_id'      => $store_id,
    'store_name'    => $store_name,
    'area_id'       => $area_id,
    'export'        => $export,
);

$QStoreFocus = new Application_Model_StoreFocus();

//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

$QArea = new Application_Model_Area();
$this->view->areas = $QArea->fetchAll(null, 'name');

if ($export && $export == 1) {
    $store_list = $QStoreFocus->store_focus_list($params);
    $this->_exportStoreFocusList($store_list,$params);
}


// Check First Time Not Show Data
// if (!empty($_GET)) { 
    $store_list = $QStoreFocus->store_focus_list($params);
// }

$this->view->params = $params;
$this->view->store_list = $store_list;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('store-focus-list/partials/list');
} else
    $this->_helper->viewRenderer->setRender('store-focus-list/index');
<?php
$page       = $this->getRequest()->getParam('page', 1);
$area_id    = $this->getRequest()->getParam('area_id');
$st_id      = $this->getRequest()->getParam('st_id');
$st_name    = $this->getRequest()->getParam('st_name');
$good_id    = $this->getRequest()->getParam('good_id');
$from       = $this->getRequest()->getParam('from', date('01/m/Y'));
$to         = $this->getRequest()->getParam('to', date('d/m/Y'));
$export     = $this->getRequest()->getParam('export', 0);

$limit = 20;
$total = 0;

$params = array(
    'area_id'   => $area_id,
    'st_id'     => $st_id,
    'st_name'   => $st_name,
    'good_id'   => $good_id,
    'from'      => $from,
    'to'        => $to,
    'export'    => $export,
);

//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;


$QStorePreOrder = new Application_Model_StorePreOrder();
$QArea = new Application_Model_Area();
$QGood = new Application_Model_Good();

$this->view->goods = $QGood->get_cache2();
$this->view->areas = $QArea->getAllAreaGrandBKK($params);

if ($export) {

    if ($export == 1) {
        $data_list = $QStorePreOrder->fetchPagination(null, null, $total, $params);
        $this->_exportStorePreOrderList($data_list,$params);
    }

}

// Check First Time Not Show Data
if (!empty($_GET)) { 
    $data_list = $QStorePreOrder->fetchPagination($page, $limit, $total, $params);
    // echo "<pre>"; print_r($data_list);

    $params['get_total_count'] = 0;
    $total_count = $QStorePreOrder->fetchPagination(null, null, $total, $params);

}

$this->view->params = $params;
$this->view->data_list = $data_list;
$this->view->total_count = $total_count;

$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'timing/store-pre-order/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('store-pre-order/partials/list');
} else
    $this->_helper->viewRenderer->setRender('store-pre-order/index');
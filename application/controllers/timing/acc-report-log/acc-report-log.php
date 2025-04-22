<?php

$store_id = $this->getRequest()->getParam('store_id');
$good_id  = $this->getRequest()->getParam('good_id');
$color_id = $this->getRequest()->getParam('color_id');
$from     = $this->getRequest()->getParam('from', date('01/m/Y'));
$to       = $this->getRequest()->getParam('to', date('d/m/Y'));
$export   = $this->getRequest()->getParam('export', 0);

$params = array(
    'store_id'  => $store_id,
    'good_id'   => $good_id,
    'color_id'  => $color_id,
    'from'      => $from,
    'to'        => $to,
    'export'    => $export,
);

//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

// if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
//     $params['asm'] = $userStorage->id;

if ( $userStorage->group_id == BM_ID ) { $params['bm_id'] = $userStorage->id; }

$QAccStockLog = new Application_Model_AccStockLog();
$QGood = new Application_Model_Good();
$GoodColorCombined = new Application_Model_GoodColorCombined();

$good_list = $QGood->get_acc_cache();

if (isset($good_id) && $good_id) {
    $color_list = $GoodColorCombined->getColorByModel($good_id);
}

if ($export) {

    if ($export == 1) {
        $store_acc_list = $QAccStockLog->getStockLogList($params);
        $this->_exportStockLogList($store_acc_list,$params);
    }

}

// Check First Time Not Show Data
if (!empty($_GET)) { 
    $store_acc_list = $QAccStockLog->getStockLogList($params);
    // echo "<pre>"; print_r($store_acc_list);
}

$this->view->params = $params;
$this->view->goods = $good_list;
$this->view->colors = $color_list;
$this->view->store_acc_list = $store_acc_list;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('acc-report-log/partials/list');
} else
    $this->_helper->viewRenderer->setRender('acc-report-log/index');
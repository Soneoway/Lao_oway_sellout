<?php

$d_id       = $this->getRequest()->getParam('d_id');
$d_name     = $this->getRequest()->getParam('d_name');
$good_id    = $this->getRequest()->getParam('good_id');
$color_id   = $this->getRequest()->getParam('color_id');
$from       = $this->getRequest()->getParam('from', date('01/m/Y'));
$to         = $this->getRequest()->getParam('to', date('d/m/Y'));
$export     = $this->getRequest()->getParam('export', 0);

$params = array(
    'd_id'      => $d_id,
    'd_name'    => $d_name,
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

if ($userStorage->group_id == AM_ID) 
    $params['am'] = $userStorage->id;

$this->view->status_list = $status_list;

$QTiming = new Application_Model_Timing();
$QGood   = new Application_Model_Good();
$GoodColorCombined = new Application_Model_GoodColorCombined();

$good_list = $QGood->get_cache2();

if (isset($good_id) && $good_id) {
    $color_list = $GoodColorCombined->getColorByModel($good_id);
}

if ($export) {

    if ($export == 1) {
        $kr_sellout = $QTiming->getKrSellout($params);
        $this->_exportKrSellout($kr_sellout,$params);
    }

    if ($export == 2) {
        $kr_sellout = $QTiming->getKrProductReport($params);
        $this->_exportKrProductReport($kr_sellout,$params);
    }

}

// Check First Time Not Show Data
if (!empty($_GET)) { 
    $kr_sellout = $QTiming->getKrSellout($params);
    // echo "<pre>"; print_r($kr_sellout);
}

$this->view->params = $params;
$this->view->goods = $good_list;
$this->view->colors = $color_list;
$this->view->kr_sellout = $kr_sellout;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('kr-sellout/partials/list');
} else
    $this->_helper->viewRenderer->setRender('kr-sellout/index');
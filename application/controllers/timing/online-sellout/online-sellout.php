<?php

$good_id    = $this->getRequest()->getParam('good_id');
$color_id   = $this->getRequest()->getParam('color_id');
$from       = $this->getRequest()->getParam('from', date('01/m/Y'));
$to         = $this->getRequest()->getParam('to', date('d/m/Y'));
$export     = $this->getRequest()->getParam('export', 0);

$params = array(
    'good_id'   => $good_id,
    'color_id'  => $color_id,
    'from'      => $from,
    'to'        => $to, 
    'export'    => $export,
);

//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

//check brandshop manager permission
if ($userStorage->group_id == AM_ID)
    $params['am_id'] = $userStorage->id;

$QGood = new Application_Model_Good();
$GoodColorCombined = new Application_Model_GoodColorCombined();

$good_list = $QGood->get_cache2();

if (isset($good_id) && $good_id) {
    $color_list = $GoodColorCombined->getColorByModel($good_id);
}

$QImei = new Application_Model_WebImei();

if ($export) {

    switch ($export) {
        case 1:
            $data_list = $QImei->getOnlineSelloutByChannel($params);
            $this->_exportOnlineSelloutReport($data_list,$params);
            break;
        default: 
            break;
    }

    
}

// Check First Time Not Show Data
if (!empty($_GET)) {
    $data_list = $QImei->getOnlineSelloutByChannel($params);
    // echo "<pre>"; print_r($data_list);
}



$this->view->params = $params;
$this->view->data_list = $data_list;
$this->view->goods = $good_list;
$this->view->colors = $color_list;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('online-sellout/partials/list');
} else
    $this->_helper->viewRenderer->setRender('online-sellout/index');
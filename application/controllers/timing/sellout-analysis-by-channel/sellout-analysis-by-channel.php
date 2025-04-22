<?php

$from           = $this->getRequest()->getParam('from', date('01/m/Y'));
$to             = $this->getRequest()->getParam('to', date('d/m/Y'));
$report_type    = $this->getRequest()->getParam('report_type', 1);
$area_id        = $this->getRequest()->getParam('area_id');
$product_id     = $this->getRequest()->getParam('product_id');
$export         = $this->getRequest()->getParam('export', 0);

$params = array(
    'from'          => $from,
    'to'            => $to, 
    'report_type'   => $report_type,
    'area_id'       => $area_id,
    'product_id'    => $product_id,
    'export'        => $export,
);

// print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

$QTiming = new Application_Model_Timing();
$QGood = new Application_Model_Good();

// $QArea = new Application_Model_Area(); 
// $this->view->areas = $QArea->getAllAreaGrandBKK();

$goods = $QGood->get_cache2();

$this->view->goods = $goods;

$report_type_list = array(
    '0'  => array('id' => '1', 'name' => 'By Day'),
    '1'  => array('id' => '2', 'name' => 'By Month'),
);

$this->view->report_type = $report_type_list;


if ( isset($params['product_id']) && $params['product_id'] ) {

    $params['model_list'] = "";

    for ($i=0;$i<count($params['product_id']);$i++) {
        $params['model_list'] .=  $goods[ $params['product_id'][$i] ]->name.", ";
    }

    $params['model_list'] = rtrim($params['model_list'], ", ");

} 

$sellout = $sellout_all = array();

if (!empty($_GET)) { 

    switch ($report_type) {
        case 1: $sellout = $QTiming->analysisByChannelDays($params); break;
        case 2: $sellout = $QTiming->analysisByChannelMonths($params); break;
        default: break;
    }

}

// echo "<pre>"; print_r($sellout); echo "<br/>";

$this->view->params = $params;
$this->view->sellout = $sellout;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('sellout-analysis-by-channel/partials/list');
} else
    $this->_helper->viewRenderer->setRender('sellout-analysis-by-channel/index');
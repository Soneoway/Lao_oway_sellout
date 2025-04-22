<?php

$product_id_01  = $this->getRequest()->getParam('product_id_01');
$from_01        = $this->getRequest()->getParam('from_01', date('01/m/Y'));

$product_id_02  = $this->getRequest()->getParam('product_id_02');
$from_02        = $this->getRequest()->getParam('from_02', date('01/m/Y'));

$week_no = $this->getRequest()->getParam('week_no');
$export  = $this->getRequest()->getParam('export', 0);

$params = array(
    'product_id_01' => $product_id_01,
    'from_01'       => $from_01,
    'product_id_02' => $product_id_02,
    'from_02'       => $from_02,
    'week_no'       => $week_no,
    'export'        => $export,
);

// print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

if ($userStorage->group_id == AM_ID) 
    $params['am'] = $userStorage->id;

$QGood = new Application_Model_Good(); 
$QTiming = new Application_Model_Timing(); 

$this->view->product_list = $QGood->get_cache2();

if ($export) {

    $sellout = array();

    if ($export == 1) {

        $params['product_id'] = $product_id_01;
        $params['from'] = $from_01;
        $sellout_01 = $QTiming->getSelloutByStoreType($params);

        $params['product_id'] = $product_id_02;
        $params['from'] = $from_02;
        $sellout_02 = $QTiming->getSelloutByStoreType($params);

        $sellout = array_merge($sellout_01,$sellout_02);

        $this->_exportSelloutByStoreType($sellout,$params);
    }

}

$sellout_01 = $sellout_02 = $sellout_by_channel = array();
$product_01 = $product_02 = $sellout_by_product = array();

if (!empty($_GET)) { 

    // Get Product 01 Info 
    $params['product_id'] = $product_id_01;
    $params['from'] = $from_01;
    $params['product_text'] = 'Product #01';
    $sellout_01 = $QTiming->getSelloutByChannel($params); 
    // echo "<pre>"; print_r($sellout_01);

    // Get Product 02 Info 
    $params['product_id'] = $product_id_02;
    $params['from'] = $from_02;
    $params['product_text'] = 'Product #02';
    $sellout_02 = $QTiming->getSelloutByChannel($params); 
    // echo "<pre>"; print_r($sellout_02);

    $sellout_by_channel = array_merge($sellout_01,$sellout_02);
    // echo "<pre>"; print_r($sellout_by_channel);

    for ($i=0;$i<count($sellout_by_channel);$i++) {

        // $sellout_by_product[ $sellout_by_channel[$i]['product_id'] ]['product_id']   = $sellout_by_channel[$i]['product_id'];
        // $sellout_by_product[ $sellout_by_channel[$i]['product_id'] ]['product_code'] = $sellout_by_channel[$i]['product_code'];
        $sellout_by_product[ $sellout_by_channel[$i]['product_name'] ]['product_name'] = $sellout_by_channel[$i]['product_name'];
        $sellout_by_product[ $sellout_by_channel[$i]['product_name'] ]['sellout']      += $sellout_by_channel[$i]['sellout'];

        for ($j=0;$j<$week_no;$j++) {
            $week_txt = $j + 1;
            $sellout_by_product[ $sellout_by_channel[$i]['product_name'] ]['W'.$week_txt] += $sellout_by_channel[$i]['W'.$week_txt];
        }
        
    }

    // echo "<pre>"; print_r($sellout_by_product);

}

$this->view->params = $params;
$this->view->sellout_by_channel = $sellout_by_channel;
$this->view->sellout_by_product = array_values($sellout_by_product);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('factory-report-by-week/partials/list');
} else
    $this->_helper->viewRenderer->setRender('factory-report-by-week/index');
<?php

$date    = $this->getRequest()->getParam('month_year', date('Y-m-d'));
$sale_id = $this->getRequest()->getParam('sale_id');

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$last_03 = date('M-Y', strtotime("-3 Month", strtotime($date)));
$last_02 = date('M-Y', strtotime("-2 Month", strtotime($date)));
$last_01 = date('M-Y', strtotime("-1 Month", strtotime($date)));

$params = array( 
    'sale_id'   => $sale_id,

    'from'      => date('Y-m-01', strtotime($date)),
    'to'        => date('Y-m-t', strtotime($date)),
    'target_from'      => date('Y-m-01', strtotime($date)),
    'target_to'        => date('Y-m-t', strtotime($date)),
    'last_03'   => $last_03,
    'last_02'   => $last_02,
    'last_01'   => $last_01,
);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();


// Get Area
$QArea = new Application_Model_Area();
$QOppoMktTarget = new Application_Model_OppoMktTarget();

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id)) {

    $params['asm'] = $userStorage->id;

        $this->view->areas = $QOppoMktTarget->getmktbyasmarea($params);

} else {
        $this->view->areas = $QOppoMktTarget->getmktByArea($params);

}

// // Get Sellout Last 3 Month
// $pcm = $QOppoPcmTarget->getLast3MonthByArea($params);

// Get Current Area Target
$where_target[] = $QOppoMktTarget->getAdapter()->quoteInto('from_date >= ?', $params['target_from']." 00:00:00");
$where_target[] = $QOppoMktTarget->getAdapter()->quoteInto('to_date <= ?', $params['target_to']." 23:59:59");
$mkt_target = $QOppoMktTarget->fetchAll($where_target);

$result_target = array();
for ($i=0;$i<count($mkt_target);$i++) {
    $result_target[ $mkt_target[$i]['staff_id'] ]['target_hero'] = $mkt_target[$i]['target_hero'];
    $result_target[ $mkt_target[$i]['staff_id'] ]['target'] = $mkt_target[$i]['target'];
}

$this->view->params = $params;
$this->view->mkt_target = $result_target;
// $this->view->pcm = $pcm;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('mkt-target/partials/list');
} else
    $this->_helper->viewRenderer->setRender('mkt-target/index');
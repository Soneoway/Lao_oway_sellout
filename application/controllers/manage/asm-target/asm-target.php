<?php

$date    = $this->getRequest()->getParam('month_year', date('Y-m-d'));

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$last_03 = date('M-Y', strtotime("-3 Month", strtotime($date)));
$last_02 = date('M-Y', strtotime("-2 Month", strtotime($date)));
$last_01 = date('M-Y', strtotime("-1 Month", strtotime($date)));

$params = array( 
    'from'              => date('Y-m-01', strtotime($date)),
    'to'                => date('Y-m-t', strtotime($date)),
    'target_from'       => date('Y-m-01', strtotime($date)),
    'target_to'         => date('Y-m-t', strtotime($date)),
    'last_03'           => $last_03,
    'last_02'           => $last_02,
    'last_01'           => $last_01,
);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();


// Get Area
$QArea = new Application_Model_Area();
$QOppoAsmTarget = new Application_Model_OppoAsmTarget();

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id)) {

        if($userStorage->group_id == RM_ID){
            $params['rgm'] = $userStorage->id;
        }

        if($userStorage->group_id == ASM_ID){
            $params['asm'] = $userStorage->id;
        }

        $this->view->areas = $QOppoAsmTarget->gobal_asm_function($params);

} else {
        $this->view->areas = $QOppoAsmTarget->getasmByArea($params);

}

// Get Current Area Target
$where_target[] = $QOppoAsmTarget->getAdapter()->quoteInto('from_date >= ?', $params['target_from']." 00:00:00");
$where_target[] = $QOppoAsmTarget->getAdapter()->quoteInto('to_date <= ?', $params['target_to']." 23:59:59");
$asm_target = $QOppoAsmTarget->fetchAll($where_target);

$result_target = array();
for ($i=0;$i<count($asm_target);$i++) {
    $result_target[ $asm_target[$i]['area_id'] ]['id'] = $asm_target[$i]['id'];
    $result_target[ $asm_target[$i]['area_id'] ]['target_hero'] = $asm_target[$i]['target_hero'];
    $result_target[ $asm_target[$i]['area_id'] ]['target'] = $asm_target[$i]['target'];
}

$this->view->params = $params;
$this->view->asm_target = $result_target;


if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('asm-target/partials/list');
} else
    $this->_helper->viewRenderer->setRender('asm-target/index');
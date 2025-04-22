<?php

$QArea = new Application_Model_Area();
$QStaff = new Application_Model_Staff();
$QAsm = new Application_Model_Asm();
$QRegion = new Application_Model_RegionalMarket();

$this->view->areas = $QArea->get_cache();
$this->view->regions = $QRegion->get_cache();

$params = array();
$page = $this->getRequest()->getParam('page', 1);
$limit = LIMITATION;
$total = 0;

$asm = $QAsm->fetchPagination($page, $limit, $total, $params);

$list = array();

foreach ($asm as $key => $value) {
    if (! isset($list[ $value['group_id'] ]) )
        $list[ $value['group_id'] ] = array();

    if (! isset($list[ $value['group_id'] ][ $value['id'] ]) )
        $list[ $value['group_id'] ]
                [ $value['id'] ] = array(
                                    'firstname' => $value['firstname'],
                                    'lastname'  => $value['lastname'],
                                    'email'     => $value['email'],
                                    'area'      => array(),
                                );

    $list[ $value['group_id'] ][ $value['id'] ]['area'][] = $value['area_id'];
    $list[ $value['group_id'] ][ $value['id'] ]['partner'][] = $value['partner'];
}

$this->view->list = $list;

$flashMessenger = $this->_helper->flashMessenger;
$this->view->messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages_success = $flashMessenger->setNamespace('success')->getMessages();

$this->_helper->viewRenderer->setRender('asm/asm');
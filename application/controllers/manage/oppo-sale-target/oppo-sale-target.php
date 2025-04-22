<?php

$date = $this->getRequest()->getParam('month_year', date('Y-m-d'));

$params = array( 
    'from' => date('Y-m-01', strtotime($date)),
    'to' => date('Y-m-t', strtotime($date)),
);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

$QOppoSaleTarget = new Application_Model_OppoSaleTarget();

$list = $QOppoSaleTarget->getListByArea($params);

$this->view->params = $params;
$this->view->lists = $list;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('oppo-sale-target/partials/list');
} else
    $this->_helper->viewRenderer->setRender('oppo-sale-target/index');
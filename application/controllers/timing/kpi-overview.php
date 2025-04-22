<?php

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
if (!$userStorage || !isset($userStorage->id)) $this->_redirect(HOST);

$staff_id = $userStorage->id;
$from     = $this->getRequest()->getParam('from', date('01/m/Y'));
$to       = $this->getRequest()->getParam('to', date('d/m/Y'));

$QStaff = new Application_Model_Staff();
$this->view->staffs = $QStaff->get_all_cache();
$this->view->params = array(
    'from'     => $from,
    'to'       => $to,
    'staff_id' => $staff_id,
);

$where = $QStaff->getAdapter()->quoteInto('id = ?', intval($staff_id));
$staff  = $QStaff->fetchRow($where);

if (!$staff) return;

$this->view->sellout = My_Kpi::fetchGrid(
    $staff_id,
    date_create_from_format("d/m/Y", $from)->format("Y-m-d"),
    date_create_from_format("d/m/Y", $to)->format("Y-m-d")
);

$QGood = new Application_Model_Good();
$this->view->goods = $QGood->get_cache();
$QGoodColor = new Application_Model_GoodColor();
$this->view->good_colors = $QGoodColor->get_cache();
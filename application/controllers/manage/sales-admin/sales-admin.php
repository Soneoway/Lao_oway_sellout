<?php
$QArea = new Application_Model_Area();
$this->view->areas = $QArea->get_cache();

$QStaff = new Application_Model_Staff();
$where = array();
$where[] = $QStaff->getAdapter()->quoteInto('group_id = ?', SALES_ADMIN_ID);
$where[] = $QStaff->getAdapter()->quoteInto('status = ?', 1);
$where[] = $QStaff->getAdapter()->quoteInto('off_date IS NULL', 1);
$this->view->asm_list = $QStaff->fetchAll($where, 'lastname');

$QSalesAdmin = new Application_Model_SalesAdmin();
$asms = $QSalesAdmin->fetchAll();

$asm_arr = array();

foreach ($asms as $asm) {
    if ( ! isset( $asm_arr[ $asm['staff_id'] ] ) )
        $asm_arr[ $asm['staff_id'] ] = array();

    $asm_arr[ $asm['staff_id'] ][] = $asm['area_id'];
}

$this->view->asms = $asm_arr;

$flashMessenger = $this->_helper->flashMessenger;
$this->view->messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages_success = $flashMessenger->setNamespace('success')->getMessages();

$this->_helper->viewRenderer->setRender('sales-admin/sales-admin');
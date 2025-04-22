<?php

$QArea = new Application_Model_Area();
$this->view->areas = $QArea->get_cache();
$QRegion = new Application_Model_RegionalMarket();
$this->view->regions = $QRegion->get_cache();

$QStaff = new Application_Model_Staff();
$where = array();
$where[] = $QStaff->getAdapter()->quoteInto('group_id = ?', ASMSTANDBY_ID);
$where[] = $QStaff->getAdapter()->quoteInto('status = ?', 1);
$where[] = $QStaff->getAdapter()->quoteInto('off_date IS NULL', 1);
$this->view->asm_list = $QStaff->fetchAll($where, 'lastname');

$QAsm = new Application_Model_AsmStandby();
$where = $QAsm->getAdapter()->quoteInto('type = ?', 1); // area
$asms = $QAsm->fetchAll($where);

$asm_arr = array();

foreach ($asms as $asm) {
    if ( ! isset( $asm_arr[ $asm['staff_id'] ] ) )
        $asm_arr[ $asm['staff_id'] ] = array();

    $asm_arr[ $asm['staff_id'] ][] = $asm['area_id'];
}

$where = $QAsm->getAdapter()->quoteInto('type = ?', 2); // region
$asms = $QAsm->fetchAll($where);

$asm_region_arr = array();

foreach ($asms as $asm) {
    if ( ! isset( $asm_region_arr[ $asm['staff_id'] ] ) )
        $asm_region_arr[ $asm['staff_id'] ] = array();

    $asm_region_arr[ $asm['staff_id'] ][] = $asm['area_id'];
}

$this->view->asms = $asm_arr;
$this->view->asm_regions = $asm_region_arr;

$flashMessenger = $this->_helper->flashMessenger;
$this->view->messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages_success = $flashMessenger->setNamespace('success')->getMessages();

$this->_helper->viewRenderer->setRender('asm/standby');
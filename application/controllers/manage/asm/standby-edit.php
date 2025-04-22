<?php

$id = $this->getRequest()->getParam('id');

if (!$id) {
    $flashMessenger = $this->_helper->flashMessenger;
    $this->view->messages = $flashMessenger->setNamespace('error')->addMessage('Invalid ID');

    $this->_redirect(HOST.'manage/asm-standby');

} else {
    $QStaff = new Application_Model_Staff();
    $staff = $QStaff->find($id);
    $staff = $staff->current();

    if (!$staff || $staff['group_id'] != ASMSTANDBY_ID || !is_null($staff['off_date']) || $staff['status'] == 0) {
        $flashMessenger = $this->_helper->flashMessenger;
        $this->view->messages = $flashMessenger->setNamespace('error')->addMessage('Not an ASM Standby');

        $this->_redirect(HOST.'manage/asm-standby');
    }
    
    $QAsm = new Application_Model_AsmStandby();
    $where = array();
    $where[] = $QAsm->getAdapter()->quoteInto('staff_id = ?', $id);
    $where[] = $QAsm->getAdapter()->quoteInto('type = ?', 1); // area
    $this->view->asm = $QAsm->fetchAll($where);

    $where = array();
    $where[] = $QAsm->getAdapter()->quoteInto('staff_id = ?', $id);
    $where[] = $QAsm->getAdapter()->quoteInto('type = ?', 2); // regional_market
    $this->view->asm_region = $tmp = $QAsm->fetchAll($where);

    $this->view->id = $id;

    $QArea = new Application_Model_Area();
    $this->view->areas = $QArea->get_cache();

    $QRegion = new Application_Model_RegionalMarket();
    $this->view->regions = $QRegion->get_cache_all();

    $this->view->staffs = $QStaff->get_cache();

    $this->_helper->viewRenderer->setRender('asm/standby-edit');
}
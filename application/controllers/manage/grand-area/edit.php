<?php

$id = $this->getRequest()->getParam('id');

if (!$id) {
    $flashMessenger = $this->_helper->flashMessenger;
    $this->view->messages = $flashMessenger->setNamespace('error')->addMessage('Invalid ID');

    $this->_redirect(HOST.'manage/asm');

} else {
    $QStaff = new Application_Model_Staff();
    $staff = $QStaff->find($id);
    $staff = $staff->current();

    if (!$staff 
            || !in_array($staff['group_id'], My_Staff_Group::$allow_in_area_view) 
            || !is_null($staff['off_date']) 
            || $staff['status'] != My_Staff_Status::On) {
        $flashMessenger = $this->_helper->flashMessenger;
        $this->view->messages = $flashMessenger->setNamespace('error')->addMessage('This staff cannot be assigned to Area views');

        $this->_redirect(HOST.'manage/asm');
    }
    
    $QAsm = new Application_Model_Asm();
    $where = array();
    $where[] = $QAsm->getAdapter()->quoteInto('staff_id = ?', $id);
    $where[] = $QAsm->getAdapter()->quoteInto('type = ?', My_Region::Area);
    $this->view->asm = $QAsm->fetchAll($where);

    $where = array();
    $where[] = $QAsm->getAdapter()->quoteInto('staff_id = ?', $id);
    $where[] = $QAsm->getAdapter()->quoteInto('type = ?', My_Region::Province);
    $this->view->asm_region = $QAsm->fetchAll($where);

    $this->view->id = $id;

    $QArea = new Application_Model_Area();
    $this->view->areas = $QArea->get_cache();

    $QRegion = new Application_Model_RegionalMarket();
    $this->view->regions = $QRegion->get_cache_all();

    $this->view->staff = $staff;

    $this->_helper->viewRenderer->setRender('asm/edit');
}
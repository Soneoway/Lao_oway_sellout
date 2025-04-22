<?php

$id = $this->getRequest()->getParam('id');

if (!$id) {
    $flashMessenger = $this->_helper->flashMessenger;
    $this->view->messages = $flashMessenger->setNamespace('error')->addMessage('Invalid ID');

    $this->_redirect(HOST.'manage/sales-admin');

} else {
    $QStaff = new Application_Model_Staff();
    $staff = $QStaff->find($id);
    $staff = $staff->current();

    if (!$staff || $staff['group_id'] != SALES_ADMIN_ID || !is_null($staff['off_date']) || $staff['status'] != My_Staff_Status::On) {
        $flashMessenger = $this->_helper->flashMessenger;
        $this->view->messages = $flashMessenger->setNamespace('error')->addMessage('Not a Sales Admin');

        $this->_redirect(HOST.'manage/sales-admin');
    }
    
    $QSalesAdmin = new Application_Model_SalesAdmin();
    $where = $QSalesAdmin->getAdapter()->quoteInto('staff_id = ?', $id);
    $this->view->asm = $QSalesAdmin->fetchAll($where);
    $this->view->id = $id;

    $QArea = new Application_Model_Area();
    $this->view->areas = $QArea->get_cache();

    $this->view->staffs = $QStaff->get_cache();

    $this->_helper->viewRenderer->setRender('sales-admin/sales-admin-edit');
}
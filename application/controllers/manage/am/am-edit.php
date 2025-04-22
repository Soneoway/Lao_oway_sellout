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

    
    $QAm = new Application_Model_Am();
    $where = array();
    $where[] = $QAm->getAdapter()->quoteInto('staff_id = ?', $id);
    $this->view->am = $QAm->fetchAll($where);

    $this->view->id = $id;

    $QOrg = new Application_Model_Org();
    $this->view->org = $QOrg->get_cache();

    $this->view->staff = $staff;
    $this->_helper->viewRenderer->setRender('am/edit');
}
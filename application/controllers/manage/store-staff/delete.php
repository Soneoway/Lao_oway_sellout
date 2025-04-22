<?php
$ti_id = $this->getRequest()->getParam('id');
$flashMessenger = $this->_helper->flashMessenger;

foreach ($ti_id as $id) {
    if ($id) {
    $QStoreStaff = new Application_Model_StoreStaff();
    $storestaffs = $QStoreStaff->find($id);
    $storestaff = $storestaffs->current();

    if (!$storestaff) throw new Exception("Invalid ID");
    
    $where = $QStoreStaff->getAdapter()->quoteInto('id = ?', $id);
    $QStoreStaff->delete($where);


    $flashMessenger->setNamespace('success')->addMessage('Done');
    }
}

$this->_redirect(HOST.'manage/store-staff');
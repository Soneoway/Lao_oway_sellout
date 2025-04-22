<?php
$ti_id = $this->getRequest()->getParam('id');
$flashMessenger = $this->_helper->flashMessenger;
$date = date('Y/m/d h:i:s');
$released_at = strtotime($date);
foreach ($ti_id as $id) {
    if ($id) {
    $QStoreStaff = new Application_Model_StoreStaffLog();
    $storestaffs = $QStoreStaff->find($id);
    $storestaff = $storestaffs->current();

    if (!$storestaff) throw new Exception("Invalid ID");
    
    $data = array(
            'released_at'   => $released_at,
            );
        $where = $QStoreStaff->getAdapter()->quoteInto('id = ?', $id);
        $QStoreStaff->update($data, $where);


    $flashMessenger->setNamespace('success')->addMessage('Done');
    }
}

$this->_redirect(HOST.'manage/store-staff-log');

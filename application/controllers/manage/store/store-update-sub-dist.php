<?php
$ti_id = $this->getRequest()->getParam('id');
$dist_id = $this->getRequest()->getParam('dist_id');
$status_id = $this->getRequest()->getParam('status_id');
if ($ti_id) {
    $QStore = new Application_Model_Store();
    $stores = $QStore->find($ti_id);
    $store = $stores->current();
    if (!$store) throw new Exception("Invalid ID");
}
$flashMessenger = $this->_helper->flashMessenger;
if ($status_id == 1) {
    foreach ($ti_id as $id) {
        $data = array(
            'district'   => $dist_id,
            );
        $where = $QStore->getAdapter()->quoteInto('id = ?', $id);
        $QStore->update($data, $where);
    }
}else if ($status_id == 2) {
    foreach ($ti_id as $id) {
        $data = array(
            'sub_district'   => $dist_id,
            );
        $where = $QStore->getAdapter()->quoteInto('id = ?', $id);
        $QStore->update($data, $where);
    }
}else if ($status_id == 3) {
    foreach ($ti_id as $id) {
        $data = array(
            'oppo_store'   => $dist_id,
            );
        $where = $QStore->getAdapter()->quoteInto('id = ?', $id);
        $QStore->update($data, $where);
    }
}else{
    $flashMessenger->setNamespace('error')->addMessage('Error');
}
$flashMessenger->setNamespace('success')->addMessage('Done');
$this->_redirect(HOST.'manage/store-staff-log');

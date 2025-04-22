<?php
$id = $this->getRequest()->getParam('id');

if (!$id) {
    $flashMessenger = $this->_helper->flashMessenger;
    $this->view->messages = $flashMessenger->setNamespace('error')->addMessage('Invalid ID');

    $this->_redirect(HOST.'manage/am');
} else {
    $QStaff = new Application_Model_Staff();

    $staff = $QStaff->find($id);
    $staff = $staff->current();

    $QAm = new Application_Model_Am();

    $store_type = $this->getRequest()->getParam('store_type');
    $store_type = is_array($store_type) ? array_unique( array_filter( $store_type ) ) : array();

    $where = $QAm->getAdapter()->quoteInto('staff_id = ?', $id);
    $QAm->delete($where);

    if (isset($store_type) && $store_type) {
        foreach ($store_type as $value) {
            $data = array(
                'staff_id' => $id,
                'org_id'  => $value
                );
            $QAm->insert($data);
        }
    }

    $cache = Zend_Registry::get('cache');
    $cache->remove('am_cache');

    $flashMessenger = $this->_helper->flashMessenger;
    $this->view->messages = $flashMessenger->setNamespace('success')->addMessage('Success');

/*
    // log
    $QLog = new Application_Model_Log();
    $userStorage = Zend_Auth::getInstance()->getStorage()->read();
    $ip = $this->getRequest()->getServer('REMOTE_ADDR');
    $info = 'AM - Update('.$id.') - Area('.implode(',', $area).') - Province (' . implode(',', $region) . ')' ;

    $QLog->insert( array (
        'info'       => $info,
        'user_id'    => $userStorage->id,
        'ip_address' => $ip,
        'time'       => date('Y-m-d H:i:s'),
    ) );
*/
    $this->_redirect(HOST.'manage/am');

}
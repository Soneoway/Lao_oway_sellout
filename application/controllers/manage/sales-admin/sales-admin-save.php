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

    if (!$staff || $staff['group_id'] != SALES_ADMIN_ID || !is_null($staff['off_date']) || $staff['status'] == 0) {
        $flashMessenger = $this->_helper->flashMessenger;
        $this->view->messages = $flashMessenger->setNamespace('error')->addMessage('Not an Sales Admin');

        $this->_redirect(HOST.'manage/sales-admin');
    }

    $QSalesAdmin = new Application_Model_SalesAdmin();

    $area = $this->getRequest()->getParam('area');
    $area = is_array($area) ? array_filter($area) : array();
    $area = is_array($area) ? array_unique($area) : array();

    $where = $QSalesAdmin->getAdapter()->quoteInto('staff_id = ?', $id);
    $QSalesAdmin->delete($where);

    if (isset($area) && $area) {
        foreach ($area as $value) {
            $data = array(
                'staff_id' => $id,
                'area_id' => $value,
                );

            $QSalesAdmin->insert($data);
        }
    }

    $cache = Zend_Registry::get('cache');
    $cache->remove('sales_admin_new_cache');
    $cache->remove('sales_admin_region_new_cache');

    $flashMessenger = $this->_helper->flashMessenger;
    $this->view->messages = $flashMessenger->setNamespace('success')->addMessage('Success');

    // log
    $QLog = new Application_Model_Log();
    $userStorage = Zend_Auth::getInstance()->getStorage()->read();
    $ip = $this->getRequest()->getServer('REMOTE_ADDR');
    $info = 'SALES ADMIN - Update('.$id.') - Area('.implode(',', $area).')';

    $QLog->insert( array (
        'info' => $info,
        'user_id' => $userStorage->id,
        'ip_address' => $ip,
        'time' => date('Y-m-d H:i:s'),
    ) );

    $this->_redirect(HOST.'manage/sales-admin');
    
}
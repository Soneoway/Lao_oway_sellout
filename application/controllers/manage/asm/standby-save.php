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

    $area = $this->getRequest()->getParam('area');
    $area = is_array($area) ? array_filter($area) : array();
    $area = is_array($area) ? array_unique($area) : array();

    $region = $this->getRequest()->getParam('region');
    $region = is_array($region) ? array_filter($region) : array();
    $region = is_array($region) ? array_unique($region) : array();

    $where = $QAsm->getAdapter()->quoteInto('staff_id = ?', $id);
    $QAsm->delete($where);

    if (isset($area) && $area) {
        foreach ($area as $value) {
            $data = array(
                'staff_id' => $id,
                'area_id'  => $value,
                'type'     => 1,
                );

            $QAsm->insert($data);
        }
    }

    if (isset($region) && $region) {
        foreach ($region as $value) {
            $data = array(
                'staff_id' => $id,
                'area_id'  => $value,
                'type'     => 2,
                );

            $QAsm->insert($data);
        }
    }

    $cache = Zend_Registry::get('cache');
    $cache->remove('asm_standby_region_new_cache');

    $flashMessenger = $this->_helper->flashMessenger;
    $this->view->messages = $flashMessenger->setNamespace('success')->addMessage('Success');

    // log
    $QLog = new Application_Model_Log();
    $userStorage = Zend_Auth::getInstance()->getStorage()->read();
    $ip = $this->getRequest()->getServer('REMOTE_ADDR');
    $info = 'ASM STANDBY - Update('.$id.') - Area('.implode(',', $area).') - Province('.implode(',', $region).')';

    $QLog->insert( array (
        'info' => $info,
        'user_id' => $userStorage->id,
        'ip_address' => $ip,
        'time' => date('Y-m-d H:i:s'),
    ) );

    $this->_redirect(HOST.'manage/asm-standby');
    
}
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

    $area = $this->getRequest()->getParam('area');
    $area = is_array($area) ? array_unique( array_filter( $area ) ) : array();

    $region = $this->getRequest()->getParam('region');
    $region = is_array($region) ? array_unique( array_filter( $region ) ) : array();

    $partner = $this->getRequest()->getParam('txt_partner');

    $where = $QAsm->getAdapter()->quoteInto('staff_id = ?', $id);
    $QAsm->delete($where);

    if (isset($area) && $area) {
        for ($i=0;$i<count($area);$i++) {
            $data = array(
                'staff_id' => $id,
                'area_id'  => $area[$i],
                'type'     => My_Region::Area,
                'partner'  => is_null($partner[$i]) ? 0 : $partner[$i],
            );
            $QAsm->insert($data);
        }
    }

    if (isset($region) && $region) {
        foreach ($region as $value) {
            $data = array(
                'staff_id' => $id,
                'area_id'  => $value,
                'type'     => My_Region::Province,
                );

            $QAsm->insert($data);
        }
    }

    $cache = Zend_Registry::get('cache');
    $cache->remove('asm_cache');

    $flashMessenger = $this->_helper->flashMessenger;
    $this->view->messages = $flashMessenger->setNamespace('success')->addMessage('Success');

    // log
    $QLog = new Application_Model_Log();
    $userStorage = Zend_Auth::getInstance()->getStorage()->read();
    $ip = $this->getRequest()->getServer('REMOTE_ADDR');
    $info = 'ASM - Update('.$id.') - Area('.implode(',', $area).') - Province (' . implode(',', $region) . ')' ;

    $QLog->insert( array (
        'info'       => $info,
        'user_id'    => $userStorage->id,
        'ip_address' => $ip,
        'time'       => date('Y-m-d H:i:s'),
    ) );

    $this->_redirect(HOST.'manage/asm');

}
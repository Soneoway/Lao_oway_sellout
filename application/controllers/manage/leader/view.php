<?php
$id = $this->getRequest()->getParam('id');

$flashMessenger = $this->_helper->flashMessenger;

try {

    if (!$id) {
        throw new Exception('Invalid ID');
    }

    // check Asm/ leader role
    $QStaff = new Application_Model_Staff();
    $staff = $QStaff->find($id);
    $staff = $staff->current();

    if (!$staff) {
        throw new Exception('Invalid ID');
    }

    $userStorage = Zend_Auth::getInstance()->getStorage()->read();

    if (!$userStorage) {
        $this->_redirect(HOST);
    }
    if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view)) {
        $QAsm = new Application_Model_Asm();
        $list_regions = $QAsm->get_cache($userStorage->id);
        $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();

        if (!count($list_regions) || !in_array($staff->regional_market, $list_regions))
            throw new Exception('You cannot access other Area\'s Stores');
        
    } elseif ($userStorage->group_id == LEADER_ID && $userStorage->id != $id) {
        throw new Exception('You cannot access other Leader\'s Stores');
    }

    // get leader's stores
    $QStoreLeader = new Application_Model_StoreLeader();
    $this->view->store_leaders = $QStoreLeader->fetchAllLeader($id);

    $QStore = new Application_Model_Store();
    $this->view->stores = $QStore->get_cache();

    $QRegion = new Application_Model_RegionalMarket();
    $this->view->regions = $QRegion->get_cache_all();
    $QArea = new Application_Model_Area();
    $this->view->areas = $QArea->get_cache();

    $this->view->staff = $staff;

} catch(Exception $ex) {
    $flashMessenger->setNamespace('error')->addMessage($ex->getMessage());
    $this->_redirect(HOST.'manage/leader');
}

$this->_helper->viewRenderer->setRender('leader/view');
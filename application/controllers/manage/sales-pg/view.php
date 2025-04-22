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

    /**
     * Phân quyền view theo group
     */
    if ( $userStorage->id == SUPERADMIN_ID 
            || in_array(
                $userStorage->group_id, 
                array(ADMINISTRATOR_ID, HR_ID, HR_EXT_ID, BOARD_ID, SALES_EXT_ID, 29, BM_ID, TRADE_MARKETING_ID, 39, 13) 
            ) 
    ) { }

    elseif ( in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id) )
        $params['asm'] = $userStorage->id;

    elseif ( in_array($userStorage->group_id, array(PGPB_ID, SALES_ID)) ) {
        if ($userStorage->id != $id)
            throw new Exception("You can view only your stores.");
    }
    
    // elseif ($userStorage->group_id == LEADER_ID)
        // $params['leader'] = $userStorage->id;

    else
        $this->_redirect(HOST);

    // get leader's stores
    $QStoreStaffLog = new Application_Model_StoreStaffLog();
    $page = $this->getRequest()->getParam('page', 1);
    $limit = null;
    $params = array(
        'staff_id' => $id,
        'id' => $id,
    );
    $total = 0;
    $this->view->store_staffs = $QStoreStaffLog->fetchPagination($page, $limit, $total, $params);

    $QStore = new Application_Model_Store();
    $this->view->stores = $QStore->get_cache();

    $QRegion = new Application_Model_RegionalMarket();
    $this->view->regions = $QRegion->get_cache_all();
    $QArea = new Application_Model_Area();
    $this->view->areas = $QArea->get_cache();

    $this->view->staff   = $staff;
    // $this->view->params  = $params;
    // $this->view->limit   = $limit;
    // $this->view->total   = $total;

    // $this->view->offset  = $limit*($page-1);
    // $this->view->url     = HOST.'manage/sales-pg-view'.( $params ? '?'.http_build_query($params).'&' : '?' );

} catch(Exception $ex) {
    $flashMessenger->setNamespace('error')->addMessage($ex->getMessage());
    $this->_redirect(HOST.'manage/sales-pg');
}

$this->_helper->viewRenderer->setRender('sales-pg/view');
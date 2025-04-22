<?php
$id = $this->getRequest()->getParam('id');

$QOrg = new Application_Model_Org();
$this->view->org = $QOrg->fetchAll(null, 'org_name');

$QMarketType = new Application_Model_MarketType();
$this->view->market_type = $QMarketType->fetchAll(null, 'name');

$QArea = new Application_Model_Area();
$this->view->areas = $QArea->get_cache();

$QTP = new Application_Model_ThProvince();
$this->view->th_province = $QTP->get_cache();

$QDistributor = new Application_Model_Distributor();
$this->view->distributor = $QDistributor->get_cache();

$QSubarea = new Application_Model_SubArea();
$this->view->subarea = $QSubarea->get_cache();

$store_level = array(
    '0'  => array('id' => 'S', 'name' => 'S'),
    '1'  => array('id' => 'A', 'name' => 'A'),
    '2'  => array('id' => 'B', 'name' => 'B'),
    '3'  => array('id' => 'C', 'name' => 'C'),
);

$this->view->store_level_list = $store_level;

if ($id) {
    $QModel = new Application_Model_Store();
    $rowset = $QModel->find($id);
    $store = $rowset->current();
    
    $this->view->store = $store;

    $rowset = $QRegionalMarket->find($store->regional_market);
    $regional_market = $rowset->current();

    $rowset = $QArea->find($regional_market->area_id);
    $area = $rowset->current();

    $this->view->area = $area;

    //get staff assign
    $QStoreStaff = new Application_Model_StoreStaff();
    $QStaff = new Application_Model_Staff();

    $where = array();
    $where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);
    $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 0);

    $data = $QStoreStaff->fetchAll($where);

    if ($data->count()){
        $tem = array();

        foreach ($data as $item)
            $tem[] = $item->staff_id;

        $where = $QStaff->getAdapter()->quoteInto('id IN (?)', $tem);
        $this->view->staffs = $QStaff->fetchAll($where);
    }

    // leader
    $where = array();
    $where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);
    $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 1);

    $data = $QStoreStaff->fetchAll($where);

    if ($data->count()){
        $tem = array();

        foreach ($data as $item)
            $tem[] = $item->staff_id;

        $where = $QStaff->getAdapter()->quoteInto('id IN (?)', $tem);
        $this->view->leaders = $QStaff->fetchAll($where);
    }

    // PC Manager
    $where = array();
    $where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);
    $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 2);
    $data = $QStoreStaff->fetchAll($where);
    
    if ($data->count()){
        $tem = array();
        
        foreach ($data as $item)
            $tem[] = $item->staff_id;
        
        $where = $QStaff->getAdapter()->quoteInto('id IN (?)', $tem);
        $this->view->pcm = $QStaff->fetchAll($where);
    }

    // Sales Maketting MKT
    $where = array();
    $where[] = $QStoreStaff->getAdapter()->quoteInto('store_id =?', $id);
    $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader =?', 5);
    $data = $QStoreStaff->fetchAll($where);

    if($data->count()){
        $tem = array();

        foreach ($data as $item)
            $tem[] = $item->staff_id;

        $where = $QStaff->getAdapter()->quoteInto('id IN (?)',$tem);
        $this->view->mkt = $QStaff->fetchAll($where);
    }

    // Brand Shop Manager
    $where = array();
    $where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);
    $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 3);
    $data = $QStoreStaff->fetchAll($where);
    
    if ($data->count()){
        $tem = array();
        
        foreach ($data as $item)
            $tem[] = $item->staff_id;
        
        $where = $QStaff->getAdapter()->quoteInto('id IN (?)', $tem);
        $this->view->bm = $QStaff->fetchAll($where);
    }

    //asm Case
    $where = array();
    $where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);
    $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 4);
    $data = $QStoreStaff->fetchAll($where);
    
    if ($data->count()){
        $tem = array();
        
        foreach ($data as $item)
            $tem[] = $item->staff_id;
        
        $where = $QStaff->getAdapter()->quoteInto('id IN (?)', $tem);
        $this->view->asm = $QStaff->fetchAll($where);
    }
}

// $QDistributor = new Application_Model_Distributor();
// $where = $QDistributor->getAdapter()->quoteInto('del IS NULL OR del = ?', 0);
// $this->view->distributors = $QDistributor->fetchAll($where, 'title');
// $this->view->distributors = $QDistributor->get_cache();

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages = $messages;

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

$this->_helper->viewRenderer->setRender('store/create');
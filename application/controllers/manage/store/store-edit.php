<?php
$id = $this->getRequest()->getParam('id');

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$group_id = $userStorage->group_id;

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

$QTA = new Application_Model_ThAmphure();
$QTD = new Application_Model_ThDistrict();

$QRegionalMarket = new Application_Model_RegionalMarket();
$QSubDistrict = new Application_Model_SubDistrict();
$QMarketName = new Application_Model_MarketName();

$store_level = array(
    '0'  => array('id' => 'S', 'name' => 'S'),
    '1'  => array('id' => 'A', 'name' => 'A'),
    '2'  => array('id' => 'B', 'name' => 'B'),
    '3'  => array('id' => 'C', 'name' => 'C'),
);

$this->view->store_level_list = $store_level;

// Permission for Market Name
if (in_array($group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id)) {

    $QAsm = new Application_Model_Asm();
    $result_area = $QAsm->get_cache($userStorage->id);
    $area_permission = $result_area['area'];

} elseif ( in_array($group_id, array(SALES_ID,LEADER_ID,PGPB_ID,PCDB_ID) ) ) {

    $QStaff = new Application_Model_Staff();
    $result_area = $QStaff->getStaffArea($userStorage->id);
    $area_permission = $result_area['staff_area_id'];

} 

if ($id) {
    $QModel = new Application_Model_Store();
    $rowset = $QModel->find($id);
    $store = $rowset->current();

    $this->view->store = $store;

    if(isset($store['area_id'])) {
        $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $store['area_id']);
        $this->view->regional_markets = $QRegionalMarket->fetchAll($where,'name');
    }

    if (isset($store['district'])) { // Old

        $district_cache = $QRegionalMarket->get_district_cache();
        $province_cache = $QRegionalMarket->get_cache_all();

        $province_cache_id = isset( $province_cache[ $district_cache[ $store['district'] ]['parent'] ] ) ? $district_cache[ $store['district'] ]['parent'] : 0;

        if ( $province_cache_id ) {
            $where = $QRegionalMarket->getAdapter()->quoteInto('parent = ?', $province_cache_id);
            $this->view->store_province = $province_cache_id;
            $this->view->districts = $QRegionalMarket->fetchAll($where, 'name');

            $area_cache_id = isset( $province_cache[ $province_cache_id ]['area_id'] ) ? $province_cache[ $province_cache_id ]['area_id'] : 0;

            if ( $area_cache_id ) {
                $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_cache_id);
                $this->view->regional_markets = $QRegionalMarket->fetchAll($where, 'name');
                $this->view->store_area = $area_cache_id;
            } // END if area_cache_id
        } // END if province_cache_id

        $where = $QSubDistrict->getAdapter()->quoteInto('district = ?', $store['district']);
        $this->view->sub_districts = $QSubDistrict->fetchAll($where, 'name');
        
    } // END if $store['district']


    if (isset($store['th_district'])) {

        $where = $QTD->getAdapter()->quoteInto('id = ?', $store['th_district']);
        $result_tmp = $QTD->fetchRow($where, 'name_th');

        $where = $QTA->getAdapter()->quoteInto('id = ?', $result_tmp['amphure_id']);
        $result_tmp2 = $QTA->fetchRow($where, 'name_th');

        // Get TH Amphure List 
        $where = $QTA->getAdapter()->quoteInto('province_id = ?', $result_tmp2['province_id']);
        $result_tha = $QTA->fetchAll($where, 'name_th')->ToArray();

        // Get TH District List 
        $where = array();
        $where[] = $QTD->getAdapter()->quoteInto('zipcode <> ?', 0);
        $where[] = $QTD->getAdapter()->quoteInto('amphure_id = ?', $result_tmp['amphure_id']);
        $result_thd = $QTD->fetchAll($where, 'name_th')->ToArray();

        $this->view->store_th_province = $result_tmp2['province_id'];
        $this->view->store_th_amphure = $result_tmp['amphure_id'];

        $this->view->th_amphure = $result_tha;
        $this->view->th_district = $result_thd;
    }


    // get staff assign
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

    // Salses marketting MTK
     $where = array();
    $where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);
    $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 5);
    $data = $QStoreStaff->fetchAll($where);
    
    if ($data->count()){
        $tem = array();
        
        foreach ($data as $item)
            $tem[] = $item->staff_id;
        
        $where = $QStaff->getAdapter()->quoteInto('id IN (?)', $tem);
        $this->view->mkt = $QStaff->fetchAll($where);
    }
    // rd

    $QStoreStaff = new Application_Model_StoreStaff();
    $QStaff = new Application_Model_Staff();

    $where = array();
    $where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);
    $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 6);
    $data = $QStoreStaff->fetchAll($where);

    if ($data->count()){
        $tem = array();
        foreach ($data as $item)
            $tem[] = $item->staff_id;
        
        $where = $QStaff->getAdapter()->quoteInto('id IN (?)', $tem);
        $this->view->rd = $QStaff->fetchAll($where);
    }

    //get pcdb
    $QStoreStaff = new Application_Model_StoreStaff();
    $QStaff = new Application_Model_Staff();

    $where = array();
    $where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);
    $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 7);
    $data = $QStoreStaff->fetchAll($where);

    if ($data->count()){
        $tem = array();
        foreach ($data as $item)
            $tem[] = $item->staff_id;
        
        $where = $QStaff->getAdapter()->quoteInto('id IN (?)', $tem);
        $this->view->pcdb = $QStaff->fetchAll($where);
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


    // Brand Shop Manager
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

    $QStoreMarket = new Application_Model_StoreMarket();

    $market_info = $QStoreMarket->getMarketInfo($id);
    $this->view->market_info = $market_info;

    if ($market_info) {

        $where_mn = array();
        $where_mn[] = $QMarketName->getAdapter()->quoteInto('market_type_id = ?', $market_info['market_type_id']);

        if ( isset($area_permission) && !empty($area_permission) ) {
            $where_mn[] = $QMarketName->getAdapter()->quoteInto('area_id IN (?)', $area_permission);
        }

        $this->view->market_name = $QMarketName->fetchAll($where_mn, 'name');
    }
}

$this->view->area_permission = $area_permission;

$QDistributor = new Application_Model_Distributor();
// $where = $QDistributor->getAdapter()->quoteInto('del IS NULL OR del = ?', 0);
// $this->view->distributors = $QDistributor->fetchAll($where, 'title');
// $this->view->distributors = $QDistributor->get_cache();

// if ($userStorage->group_id == ADMINISTRATOR_ID) { 
    //$this->view->distributors = $QDistributor->get_cache(); 
// } else {
    $d_temp = $QDistributor->find($store['d_id']);
    $this->view->d_chain1 = $d_temp->current();
// }

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages = $messages;

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

$this->_helper->viewRenderer->setRender('store/create');
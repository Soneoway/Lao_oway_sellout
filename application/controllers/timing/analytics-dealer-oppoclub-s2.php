<?php

$id              = $this->getRequest()->getParam('id');
$name            = $this->getRequest()->getParam('name');
$from            = $this->getRequest()->getParam('from', date('01/m/Y'));
$to              = $this->getRequest()->getParam('to', date('d/m/Y'));
$area_id         = $this->getRequest()->getParam('area_id');
$regional_market = $this->getRequest()->getParam('regional_market');
$district        = $this->getRequest()->getParam('district');
$export          = $this->getRequest()->getParam('export');
$dealer_id       = $this->getRequest()->getParam('dealer_id');
$level_id       = $this->getRequest()->getParam('level_id');

$page = $this->getRequest()->getParam('page', 1);
$sort = $this->getRequest()->getParam('sort', 'total');
$desc = $this->getRequest()->getParam('desc', 1);

$limit = LIMITATION;
$total = 0;

$params = array(
    'season'          => 2,
    'id'              => $id,
    'name'            => $name,
    'from'            => $from,
    'to'              => $to,
    'area_id'         => $area_id,
    'regional_market' => $regional_market,
    'district'        => $district,
    'page'            => $page,
    'sort'            => $sort,
    'desc'            => $desc,
    'export'          => $export,
    'level_id'        => $level_id
    );
//print_r($params);

$QArea = new Application_Model_Area();
$this->view->areas = $QArea->fetchAll(null, 'name');

$QRegionalMarket = new Application_Model_RegionalMarket();

if ($area_id) {
    if (is_array($area_id) && count($area_id))
        $where = $QRegionalMarket->getAdapter()->quoteInto('area_id IN (?)', $area_id);
    else
        $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id);

    $this->view->regional_markets = $QRegionalMarket->fetchAll($where, 'name');
}

if ($regional_market) {
    if (is_array($regional_market) && count($regional_market))
        $where = $QRegionalMarket->getAdapter()->quoteInto('parent IN (?)', $regional_market);
    else
        $where = $QRegionalMarket->getAdapter()->quoteInto('parent = ?', $regional_market);

    $this->view->districts = $QRegionalMarket->fetchAll($where, 'name');
}

if ($district) {
    $QStore = new Application_Model_Store();

    if (is_array($regional_market) && count($regional_market))
        $where = $QStore->getAdapter()->quoteInto('district IN (?)', $district);
    else
        $where = $QStore->getAdapter()->quoteInto('district = ?', $district);

    $this->view->stores = $QStore->fetchAll($where, 'name');
}

$QOP_Reward = new Application_Model_OppoclubLevel();
$this->view->op_level = $QOP_Reward->fetchAll();

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id)) {
    $params['asm'] = $userStorage->id;
} 

if ( isset($export) && $export ) {
    if ($export == 1) { 
        $params['dealer_id'] = $dealer_id;
        $this->_exportExcelOPStoreAll($params);
    }
    if ($export == 2) { 
        $params['dealer_id'] = $dealer_id;
        $this->_exportExcelOPStoreActive($params);
    }
    if ($export == 3) {
        $this->_exportExcelOPPOClub($params);
    }
    if ($export == 4) {
        // Export StoreActive for HUB
        $params['dealer_id'] = $dealer_id;
        $this->_exportExcelOPStoreHUBActive($params);
    }
    if ($export == 5) {
        $this->_exportExcelOPPOClubByStore($params);
    }
} 

$QTiming = new Application_Model_Timing();
if (!empty($_GET)) { 
    $sales = $QTiming->report_by_dealer_oppoclub($page, $limit, $total, $params);
    $get_total = $QTiming->get_total_oppoclub($params);
}

//print_r($sales);
//print_r($get_total);

$this->view->sales = $sales;
$this->view->total = $total;

$this->view->total_sales = $get_total['sellout'];
$this->view->total_count = $get_total['count'];
$this->view->total_activated = $get_total['active'];

$this->view->params      = $params;
$this->view->limit       = $limit;
$this->view->desc        = $desc;
$this->view->offset      = $limit*($page-1);
$this->view->url         = HOST.'timing/analytics-dealer-oppoclub-s2'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->current_col = $sort;
